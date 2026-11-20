<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model
{
    protected $fillable = ['key', 'name', 'description', 'size_hint', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class);
    }
}
