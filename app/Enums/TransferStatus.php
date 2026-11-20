<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransferStatus: string implements HasColor, HasLabel
{
    case Rumour = 'rumour';
    case Confirmed = 'confirmed';
    case Loan = 'loan';
    case Renewal = 'renewal';
    case Departure = 'departure';
    case FreeTransfer = 'free';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rumour => 'Rumour',
            self::Confirmed => 'Confirmed transfer',
            self::Loan => 'Confirmed loan',
            self::Renewal => 'Contract renewal',
            self::Departure => 'Departure',
            self::FreeTransfer => 'Confirmed free transfer',
        };
    }

    public function getColor(): string
    {
        return $this === self::Rumour ? 'warning' : 'success';
    }

    public function isConfirmed(): bool
    {
        return $this !== self::Rumour;
    }
}
