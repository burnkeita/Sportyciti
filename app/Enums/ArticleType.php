<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ArticleType: string implements HasLabel
{
    case News = 'news';
    case MatchReport = 'match_report';
    case Interview = 'interview';
    case Opinion = 'opinion';
    case Feature = 'feature';
    case Transfer = 'transfer';
    case Insight = 'insight';

    public function getLabel(): string
    {
        return match ($this) {
            self::News => 'News',
            self::MatchReport => 'Match report',
            self::Interview => 'Interview',
            self::Opinion => 'Opinion',
            self::Feature => 'Feature',
            self::Transfer => 'Transfer story',
            self::Insight => 'SportyCiti Insight',
        };
    }
}
