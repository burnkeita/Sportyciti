<?php

namespace App\Models;

use App\Enums\TransferStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

class Transfer extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'player_id', 'player_name', 'from_team_id', 'from_team_name', 'to_team_id', 'to_team_name', 'status', 'reliability',
        'fee_amount', 'fee_currency', 'fee_verified', 'fee_note', 'contract_until', 'transfer_window',
        'source_name', 'source_url', 'summary', 'article_id', 'created_by', 'is_published', 'reported_at', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferStatus::class,
            'fee_amount' => 'decimal:2',
            'fee_verified' => 'boolean',
            'is_published' => 'boolean',
            'reported_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $transfer): void {
            $transfer->created_by ??= auth()->id();
        });

        static::saving(function (self $transfer): void {
            // A rumour can never carry a confirmation date.
            if ($transfer->status === TransferStatus::Rumour) {
                $transfer->confirmed_at = null;
            } elseif (! $transfer->confirmed_at) {
                $transfer->confirmed_at = now();
            }
        });
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function fromTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'from_team_id');
    }

    public function toTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'to_team_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublic(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeForTeam(Builder $query, int $teamId): void
    {
        $query->where(fn ($q) => $q->where('from_team_id', $teamId)->orWhere('to_team_id', $teamId));
    }

    public function fromName(): ?string
    {
        return $this->fromTeam?->name ?? $this->from_team_name;
    }

    public function toName(): ?string
    {
        return $this->toTeam?->name ?? $this->to_team_name;
    }

    /** Only verified fees are displayed as figures; anything else is labelled "undisclosed/unverified". */
    public function feeLabel(): string
    {
        if ($this->status === TransferStatus::FreeTransfer) {
            return 'Free transfer';
        }

        if ($this->fee_amount !== null && $this->fee_verified) {
            return Number::currency((float) $this->fee_amount, $this->fee_currency ?: 'EUR', precision: 0);
        }

        if ($this->fee_amount !== null) {
            return 'Reported fee unverified';
        }

        return $this->fee_note ?: 'Undisclosed';
    }
}
