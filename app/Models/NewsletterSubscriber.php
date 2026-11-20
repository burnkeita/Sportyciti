<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'status', 'token', 'source', 'ip_hash'];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscriber): void {
            $subscriber->token ??= Str::random(48);
            $subscriber->email = Str::lower(trim($subscriber->email));
        });
    }

    public function confirm(): void
    {
        $this->forceFill(['status' => 'confirmed', 'confirmed_at' => now(), 'unsubscribed_at' => null])->save();
    }

    public function unsubscribe(): void
    {
        $this->forceFill(['status' => 'unsubscribed', 'unsubscribed_at' => now()])->save();
    }
}
