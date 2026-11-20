<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InsightType: string implements HasLabel
{
    case TacticalBreakdown = 'tactical_breakdown';
    case MatchPreview = 'match_preview';
    case PostMatchAnalysis = 'post_match_analysis';
    case PlayerPerformance = 'player_performance';
    case TeamForm = 'team_form';
    case StatisticalInsight = 'statistical_insight';
    case ExpertOpinion = 'expert_opinion';
    case TransferAnalysis = 'transfer_analysis';
    case BusinessAnalysis = 'business_analysis';

    public function getLabel(): string
    {
        return match ($this) {
            self::TacticalBreakdown => 'Tactical breakdown',
            self::MatchPreview => 'Match preview',
            self::PostMatchAnalysis => 'Post-match analysis',
            self::PlayerPerformance => 'Player performance review',
            self::TeamForm => 'Team form analysis',
            self::StatisticalInsight => 'Statistical insight',
            self::ExpertOpinion => 'Expert opinion',
            self::TransferAnalysis => 'Transfer analysis',
            self::BusinessAnalysis => 'Football business analysis',
        };
    }
}
