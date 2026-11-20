<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Generates a unique, URL-safe slug from a source attribute when none is given.
 * Models may override slugSource() to change the source attribute.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model): void {
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug((string) $model->{$model->slugSource()});
            } else {
                $model->slug = Str::slug($model->slug);
            }
        });
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function generateUniqueSlug(string $value): string
    {
        $base = Str::limit(Str::slug($value), 180, '') ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        $query = fn (string $candidate) => static::query()
            ->when(in_array(SoftDeletes::class, class_uses_recursive(static::class), true), fn ($q) => $q->withTrashed())
            ->where('slug', $candidate)
            ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
            ->exists();

        while ($query($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
