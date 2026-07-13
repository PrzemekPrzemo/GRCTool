<?php

namespace App\Services;

use App\Models\AnswerLibrary;
use App\Models\Policy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Dopasowywanie pytań ankiety do AnswerLibrary.
 *
 * Strategia (faza A — bez LLM):
 *  1. MySQL FULLTEXT MATCH (gdy mysql) lub LIKE-trigram fallback (sqlite)
 *  2. Boost po dopasowaniu aliasów (bonus +0.2 jeśli alias substring match)
 *  3. Boost po wspólnych tagach (jeśli pytanie ma tagi)
 *  4. Score normalizowany do 0..1
 *
 * Faza B (V2): dodanie embeddings + cosine similarity przez LLM API.
 */
class QuestionMatchingService
{
    public const AUTO_FILL_THRESHOLD = 0.65;

    /**
     * @param  string[]  $tags
     * @return array<int,array{answer:AnswerLibrary,score:float}>
     */
    public function findMatches(string $questionText, array $tags = [], int $limit = 5): array
    {
        $candidates = $this->fulltextSearch($questionText, $limit * 3);

        $scored = [];
        $normalized = $this->normalize($questionText);

        foreach ($candidates as $row) {
            $answer = $row['answer'];
            $relevance = (float) $row['relevance'];

            // Bonus za alias match
            $aliasBonus = 0.0;
            foreach ($answer->aliases ?? [] as $alias) {
                if (Str::contains($normalized, $this->normalize($alias))) {
                    $aliasBonus = 0.2;
                    break;
                }
            }

            // Bonus za tag overlap
            $tagBonus = 0.0;
            if (! empty($tags) && ! empty($answer->tags)) {
                $intersect = array_intersect(
                    array_map('mb_strtolower', $tags),
                    array_map('mb_strtolower', $answer->tags),
                );
                $tagBonus = min(0.15, count($intersect) * 0.05);
            }

            $score = min(1.0, $relevance + $aliasBonus + $tagBonus);
            $scored[] = ['answer' => $answer, 'score' => $score];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    /**
     * Sugestie z treści polityk — używane, gdy AnswerLibrary nie ma jeszcze
     * gotowej, wyselekcjonowanej odpowiedzi na dane pytanie. W przeciwieństwie
     * do findMatches() nie jest to auto-fill: surowy fragment polityki nie
     * jest gotową odpowiedzią dla klienta, tylko podpowiedzią do ręcznego
     * wykorzystania przez osobę odpowiadającą.
     *
     * @return array<int,array{policy:Policy,relevance:float,snippet:string}>
     */
    public function findPolicySuggestions(string $questionText, int $limit = 3): array
    {
        $driver = DB::getDriverName();
        $tokens = $this->tokenize($questionText);

        if ($driver === 'mysql') {
            try {
                $rows = DB::select(
                    'SELECT id, MATCH(description) AGAINST (? IN NATURAL LANGUAGE MODE) AS rel
                     FROM policies
                     WHERE deleted_at IS NULL AND description IS NOT NULL
                       AND MATCH(description) AGAINST (? IN NATURAL LANGUAGE MODE) > 0
                     ORDER BY rel DESC LIMIT '.(int) $limit,
                    [$questionText, $questionText],
                );

                $maxRel = max(array_map(fn ($r) => (float) $r->rel, $rows ?: [(object) ['rel' => 1]]));

                return array_values(array_filter(array_map(function ($row) use ($maxRel, $tokens) {
                    $policy = Policy::find($row->id);
                    if (! $policy) {
                        return null;
                    }

                    return [
                        'policy' => $policy,
                        'relevance' => $maxRel > 0 ? min(1.0, ((float) $row->rel) / $maxRel) : 0.0,
                        'snippet' => $this->snippet((string) $policy->description, $tokens),
                    ];
                }, $rows)));
            } catch (\Throwable $e) {
                // Np. brak FULLTEXT indeksu na policies.description (migracja nie została
                // jeszcze uruchomiona) — nie wywalaj strony ankiety, tylko poinformuj w logu
                // i spadnij na fallback token-overlap poniżej.
                Log::warning('QuestionMatchingService::findPolicySuggestions MySQL FULLTEXT query failed, falling back to token overlap.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // SQLite / fallback — token overlap heuristic na tytule + treści
        if (empty($tokens)) {
            return [];
        }

        return Policy::whereNotNull('description')
            ->get(['id', 'code', 'title', 'description'])
            ->map(function (Policy $p) use ($tokens) {
                $candidateTokens = $this->tokenize($p->title.' '.$p->description);
                $intersect = array_intersect($tokens, $candidateTokens);
                $relevance = count($intersect) / max(count($tokens), 1);

                return ['policy' => $p, 'relevance' => $relevance];
            })
            ->filter(fn ($x) => $x['relevance'] > 0.2)
            ->sortByDesc('relevance')
            ->take($limit)
            ->map(fn ($x) => [
                'policy' => $x['policy'],
                'relevance' => min(0.8, $x['relevance']),
                'snippet' => $this->snippet((string) $x['policy']->description, $tokens),
            ])
            ->values()
            ->all();
    }

    /** @param  string[]  $tokens */
    private function snippet(string $text, array $tokens, int $context = 160): string
    {
        $normalized = mb_strtolower($text);
        $position = null;
        foreach ($tokens as $token) {
            $pos = mb_strpos($normalized, mb_strtolower($token));
            if ($pos !== false && ($position === null || $pos < $position)) {
                $position = $pos;
            }
        }

        if ($position === null) {
            return Str::limit(trim($text), $context);
        }

        $start = max(0, $position - 40);
        $excerpt = mb_substr($text, $start, $context);

        return ($start > 0 ? '…' : '').trim($excerpt).'…';
    }

    /**
     * @return array<int,array{answer:AnswerLibrary,relevance:float}>
     */
    private function fulltextSearch(string $query, int $limit): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            try {
                $rows = DB::select(
                    'SELECT id, MATCH(canonical_question) AGAINST (? IN NATURAL LANGUAGE MODE) AS rel
                     FROM answer_library
                     WHERE deleted_at IS NULL AND is_active = 1
                       AND MATCH(canonical_question) AGAINST (? IN NATURAL LANGUAGE MODE) > 0
                     ORDER BY rel DESC LIMIT '.(int) $limit,
                    [$query, $query],
                );

                $maxRel = max(array_map(fn ($r) => (float) $r->rel, $rows ?: [(object) ['rel' => 1]]));

                return array_map(function ($row) use ($maxRel) {
                    $answer = AnswerLibrary::find($row->id);

                    return ['answer' => $answer, 'relevance' => $maxRel > 0 ? min(1.0, ((float) $row->rel) / $maxRel * 0.8) : 0];
                }, $rows);
            } catch (\Throwable $e) {
                Log::warning('QuestionMatchingService::fulltextSearch MySQL FULLTEXT query failed, falling back to token overlap.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // SQLite / fallback — token overlap heuristic
        $tokens = $this->tokenize($query);

        return AnswerLibrary::where('is_active', true)->get()
            ->map(function (AnswerLibrary $a) use ($tokens) {
                $candidateTokens = $this->tokenize($a->canonical_question.' '.implode(' ', $a->aliases ?? []));
                if (empty($candidateTokens)) {
                    return ['answer' => $a, 'relevance' => 0.0];
                }
                $intersect = array_intersect($tokens, $candidateTokens);
                $relevance = count($intersect) / max(count($tokens), 1);

                return ['answer' => $a, 'relevance' => min(0.8, $relevance)];
            })
            ->filter(fn ($x) => $x['relevance'] > 0.1)
            ->sortByDesc('relevance')
            ->take($limit)
            ->values()
            ->all();
    }

    private function normalize(string $text): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($text)));
    }

    /** @return string[] */
    private function tokenize(string $text): array
    {
        $stopwords = ['i', 'oraz', 'lub', 'a', 'jest', 'czy', 'na', 'do', 'w', 'z', 'po', 'dla', 'the', 'is', 'are', 'a', 'an', 'and', 'or', 'in', 'on', 'of', 'to'];
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($tokens, fn ($t) => mb_strlen($t) > 2 && ! in_array($t, $stopwords, true)));
    }
}
