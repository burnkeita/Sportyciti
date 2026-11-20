<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug', 'code', 'flag_url'];

    protected function slugSource(): string
    {
        return 'name';
    }

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
