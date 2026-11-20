<?php

namespace App\Services\Content;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Counts one view per article per session, ignoring bots and staff previews.
 * Writes happen after the response is sent so they never slow down page delivery.
 */
class ViewRecorder
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget|headless/i';

    public function record(Article $article, Request $request): void
    {
        if (preg_match(self::BOT_PATTERN, (string) $request->userAgent())) {
            return;
        }

        $sessionKey = 'viewed_articles';
        $viewed = $request->session()->get($sessionKey, []);

        if (in_array($article->id, $viewed, true)) {
            return;
        }

        $request->session()->put($sessionKey, array_slice([...$viewed, $article->id], -200));

        $id = $article->id;
        dispatch(function () use ($id): void {
            DB::table('articles')->where('id', $id)->increment('view_count');

            $date = now()->toDateString();
            $updated = DB::table('article_views')->where('article_id', $id)->where('date', $date)->increment('views');

            if ($updated === 0) {
                DB::table('article_views')->insertOrIgnore(['article_id' => $id, 'date' => $date, 'views' => 1]);
            }
        })->afterResponse();
    }
}
