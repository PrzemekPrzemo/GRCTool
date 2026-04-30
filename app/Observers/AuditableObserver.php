<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        AuditLogger::log('created', $model, ['attributes' => $this->safeAttributes($model)]);
    }

    public function updated(Model $model): void
    {
        $changes = [];
        foreach ($model->getChanges() as $key => $newValue) {
            if (in_array($key, ['updated_at', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'], true)) {
                continue;
            }
            $changes[$key] = [
                'old' => $model->getOriginal($key),
                'new' => $newValue,
            ];
        }

        if (! empty($changes)) {
            AuditLogger::log('updated', $model, ['changes' => $changes]);
        }
    }

    public function deleted(Model $model): void
    {
        AuditLogger::log('deleted', $model, ['attributes' => $this->safeAttributes($model)]);
    }

    public function restored(Model $model): void
    {
        AuditLogger::log('restored', $model);
    }

    private function safeAttributes(Model $model): array
    {
        $hidden = $model->getHidden();
        $attributes = $model->getAttributes();
        foreach ($hidden as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }
}
