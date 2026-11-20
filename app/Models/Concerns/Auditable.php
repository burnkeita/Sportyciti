<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Arr;

/**
 * Records create/update/delete events to the audit_logs table.
 * Large or sensitive attributes are excluded via $auditExclude.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->writeAudit('created', [], $model->auditableAttributes($model->getAttributes())));

        static::updated(function ($model): void {
            $changes = $model->auditableAttributes($model->getChanges());
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $old = Arr::only($model->auditableAttributes($model->getOriginal()), array_keys($changes));
            $model->writeAudit('updated', $old, $changes);
        });

        static::deleted(fn ($model) => $model->writeAudit('deleted', $model->auditableAttributes($model->getOriginal()), []));
    }

    protected function auditableAttributes(array $attributes): array
    {
        $exclude = array_merge(
            ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'body', 'html_code', 'insight_blocks', 'view_count'],
            property_exists($this, 'auditExclude') ? $this->auditExclude : [],
        );

        return collect(Arr::except($attributes, $exclude))
            ->map(fn ($value) => $value instanceof \BackedEnum ? $value->value : ($value instanceof \DateTimeInterface ? $value->format(DATE_ATOM) : $value))
            ->all();
    }

    protected function writeAudit(string $event, array $old, array $new): void
    {
        $request = app()->runningInConsole() ? null : request();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
        ]);
    }
}
