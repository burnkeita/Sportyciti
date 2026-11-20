<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['provider', 'resource', 'status', 'records', 'duration_ms', 'message', 'context'];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
