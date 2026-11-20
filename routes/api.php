<?php

use App\Http\Controllers\Api\ArticleApiController;
use App\Http\Controllers\Api\FootballApiController;
use Illuminate\Support\Facades\Route;

/*
| Read-only public API (v1) for future mobile apps and partners.
| Rate limited to 60 requests/minute per IP.
*/
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('/articles', [ArticleApiController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleApiController::class, 'show']);
    Route::get('/categories', [ArticleApiController::class, 'categories']);
    Route::get('/football/live', [FootballApiController::class, 'live']);
    Route::get('/football/fixtures', [FootballApiController::class, 'fixtures']);
    Route::get('/football/competitions', [FootballApiController::class, 'competitions']);
    Route::get('/football/competitions/{slug}/standings', [FootballApiController::class, 'standings']);
});
