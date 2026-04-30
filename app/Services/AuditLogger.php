<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Centralny serwis do logowania wszystkich akcji w audit_logs.
 * Każdy zapis tworzy łańcuch hash (integrity_hash = sha256(prev || row))
 * pozwalający wykryć tampering ex-post.
 */
class AuditLogger
{
    public static function log(
        string $action,
        ?Model $subject = null,
        ?array $changes = null,
        ?array $context = null,
    ): AuditLog {
        return DB::transaction(function () use ($action, $subject, $changes, $context): AuditLog {
            $user = auth()->user();
            $request = request();

            $previousHash = (string) (AuditLog::query()->orderByDesc('id')->value('integrity_hash') ?? '');

            $occurredAt = now();
            $payload = [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'subject_code' => $subject->code ?? null,
                'changes' => $changes,
                'context' => array_merge([
                    'route' => $request?->path(),
                    'method' => $request?->method(),
                ], $context ?? []),
                'ip_address' => $request?->ip(),
                'user_agent' => substr((string) $request?->userAgent(), 0, 500),
                'occurred_at' => $occurredAt->format('Y-m-d H:i:s'),
            ];

            $integrityHash = hash('sha256', $previousHash.json_encode($payload));

            return AuditLog::create([
                ...$payload,
                'occurred_at' => $occurredAt,
                'integrity_hash' => $integrityHash,
            ]);
        });
    }

    /**
     * Weryfikacja łańcucha integralności. Liczy oczekiwany hash od stanu wiersza
     * z kanonicznymi polami (te same które używane są w log()).
     */
    public static function verifyChain(int $limit = 100): array
    {
        $logs = AuditLog::query()->orderBy('id')->limit($limit)->get();
        $errors = [];
        $previousHash = '';

        foreach ($logs as $log) {
            $payload = [
                'user_id' => $log->user_id,
                'user_email' => $log->user_email,
                'action' => $log->action,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'subject_code' => $log->subject_code,
                'changes' => $log->changes,
                'context' => $log->context,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'occurred_at' => $log->occurred_at?->format('Y-m-d H:i:s'),
            ];

            $expected = hash('sha256', $previousHash.json_encode($payload));
            if ($expected !== $log->integrity_hash) {
                $errors[] = ['id' => $log->id, 'expected' => $expected, 'actual' => $log->integrity_hash];
            }
            $previousHash = (string) $log->integrity_hash;
        }

        return $errors;
    }
}
