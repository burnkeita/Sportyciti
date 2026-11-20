<?php

namespace App\Http\Controllers\Football;

use App\Enums\TransferStatus;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use App\Models\Transfer;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index(Request $request, Seo $seo): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:confirmed,rumour'],
            'team' => ['nullable', 'string', 'max:191'],
            'window' => ['nullable', 'string', 'max:40'],
        ]);

        $team = ! empty($filters['team']) ? Team::where('slug', $filters['team'])->first() : null;

        $transfers = Transfer::query()->public()->with(['fromTeam', 'toTeam', 'player', 'article:id,slug,title'])
            ->when(($filters['status'] ?? null) === 'rumour', fn ($q) => $q->where('status', TransferStatus::Rumour))
            ->when(($filters['status'] ?? null) === 'confirmed', fn ($q) => $q->where('status', '!=', TransferStatus::Rumour))
            ->when($team, fn ($q) => $q->forTeam($team->id))
            ->when($filters['window'] ?? null, fn ($q, $w) => $q->where('transfer_window', $w))
            ->latest('reported_at')->paginate(25)->withQueryString();

        $seo->set('Football transfers and rumours', 'Confirmed football transfers, loans, contract renewals and transfer rumours with sources — Ghanaian and international.', route('football.transfers.index'));
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Football', route('football.index'))->breadcrumb('Transfers');

        return view('football.transfers', [
            'transfers' => $transfers,
            'filters' => $filters,
            'team' => $team,
            'windows' => Transfer::query()->public()->whereNotNull('transfer_window')->distinct()->orderByDesc('transfer_window')->pluck('transfer_window'),
        ]);
    }

    public function player(string $slug, Seo $seo): View
    {
        $player = Player::query()->where('slug', $slug)->with(['team', 'nationality', 'transfers' => fn ($q) => $q->public()->with(['fromTeam', 'toTeam', 'article:id,slug,title'])])->firstOrFail();

        $seo->set("{$player->name} — transfer history and news", "Transfer history, confirmed moves and reported links for {$player->name}.", $player->url(), $player->photo_url);
        $seo->breadcrumb('Home', route('home'))->breadcrumb('Transfers', route('football.transfers.index'))->breadcrumb($player->name);
        $seo->schema(array_filter(['@context' => 'https://schema.org', '@type' => 'Person', 'name' => $player->name, 'image' => $player->photo_url, 'nationality' => $player->nationality?->name]));

        return view('football.player', ['player' => $player]);
    }
}
