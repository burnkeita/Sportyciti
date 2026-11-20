<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug', 'is_active'];

    protected function slugSource(): string
    {
        return 'name';
    }

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }
}
