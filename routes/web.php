<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Football;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MultimediaController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// ---- News ---------------------------------------------------------------------
Route::get('/news/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/preview/articles/{article}', [ArticleController::class, 'preview'])->middleware(['auth', 'signed'])->name('articles.preview');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/insights', [CategoryController::class, 'insights'])->name('insights.index');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/search', SearchController::class)->name('search');
Route::get('/latest', [ArticleController::class, 'latest'])->name('articles.latest');

// ---- Authors ----------------------------------------------------------------------
Route::get('/team', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/author/{slug}', [AuthorController::class, 'show'])->name('authors.show');

// ---- Multimedia -------------------------------------------------------------------
Route::get('/videos', [MultimediaController::class, 'videos'])->name('videos.index');
Route::get('/videos/{slug}', [MultimediaController::class, 'video'])->name('videos.show');
Route::get('/galleries', [MultimediaController::class, 'galleries'])->name('galleries.index');
Route::get('/galleries/{slug}', [MultimediaController::class, 'gallery'])->name('galleries.show');

// ---- Football ---------------------------------------------------------------------
Route::prefix('football')->name('football.')->group(function () {
    Route::get('/', [Football\HubController::class, 'index'])->name('index');
    Route::get('/live-scores', [Football\HubController::class, 'scores'])->name('scores');
    Route::get('/fixtures', [Football\HubController::class, 'fixtures'])->name('fixtures');
    Route::get('/results', [Football\HubController::class, 'results'])->name('results');
    Route::get('/news', [Football\HubController::class, 'news'])->name('news');
    Route::get('/insights', [Football\HubController::class, 'insights'])->name('insights');
    Route::get('/match/{fixture}/{slug?}', [Football\MatchController::class, 'show'])->whereNumber('fixture')->name('matches.show');
    Route::get('/competitions', [Football\CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/competitions/{slug}', [Football\CompetitionController::class, 'show'])->name('competitions.show');
    Route::get('/competitions/{slug}/table', [Football\CompetitionController::class, 'table'])->name('competitions.table');
    Route::get('/competitions/{slug}/fixtures', [Football\CompetitionController::class, 'fixtures'])->name('competitions.fixtures');
    Route::get('/teams/{slug}', [Football\TeamController::class, 'show'])->name('teams.show');
    Route::get('/transfers', [Football\TransferController::class, 'index'])->name('transfers.index');
    Route::get('/players/{slug}', [Football\TransferController::class, 'player'])->name('players.show');
});

// ---- Engagement -------------------------------------------------------------------
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])->middleware('throttle:newsletter')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/saved', [AccountController::class, 'bookmarks'])->name('bookmarks');
    Route::get('/interests', [AccountController::class, 'interests'])->name('interests');
    Route::put('/interests', [AccountController::class, 'updateInterests'])->name('interests.update');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
});

// ---- Advertising ------------------------------------------------------------------
Route::get('/ad/{advertisement}/click', [AdController::class, 'click'])->middleware('throttle:60,1')->name('ads.click');

// ---- SEO ------------------------------------------------------------------------
Route::get('/sitemap.xml', [SeoController::class, 'sitemapIndex'])->name('sitemap');
Route::get('/sitemaps/articles-{year}-{month}.xml', [SeoController::class, 'articleSitemap'])->whereNumber(['year', 'month'])->name('sitemap.articles');
Route::get('/sitemaps/pages.xml', [SeoController::class, 'pagesSitemap'])->name('sitemap.pages');
Route::get('/news-sitemap.xml', [SeoController::class, 'newsSitemap'])->name('sitemap.news');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/feed', [SeoController::class, 'rss'])->name('feed');

// ---- Static pages ---------------------------------------------------------------
Route::get('/{page}', [PageController::class, 'show'])
    ->whereIn('page', ['about', 'contact', 'advertise', 'privacy', 'terms', 'cookies', 'editorial-policy', 'corrections'])
    ->name('pages.show');
