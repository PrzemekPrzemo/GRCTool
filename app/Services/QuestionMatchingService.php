<?php

namespace App\Services;

use App\Models\AnswerLibrary;
use Illuminate\Support\Facades\DB;
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
     * @return array<int,array{answer:AnswerLibrary,relevance:float}>
     */
    private function fulltextSearch(string $query, int $limit): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
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
