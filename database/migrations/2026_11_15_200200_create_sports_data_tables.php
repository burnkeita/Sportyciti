<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sports data persisted from licensed providers (e.g. API-Football).
 * Every provider-sourced row carries (provider, external_id) so data is
 * upserted rather than duplicated, and its origin is always traceable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 10)->nullable()->index();
            $table->string('flag_url')->nullable();
            $table->timestamps();
        });

        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->restrictOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name', 40)->nullable();
            $table->string('type', 20)->default('league'); // league | cup
            $table->string('logo_url')->nullable();
            $table->string('provider', 32)->nullable();
            $table->string('external_id', 64)->nullable();
            $table->unsignedSmallInteger('current_season_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('sync_enabled')->default(true);
            $table->boolean('has_standings')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->text('description')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index(['is_active', 'position']);
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('name', 20);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->unique(['competition_id', 'year']);
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_name', 40)->nullable();
            $table->string('code', 10)->nullable();
            $table->string('logo_url')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_city')->nullable();
            $table->unsignedSmallInteger('founded')->nullable();
            $table->boolean('is_national')->default(false);
            $table->string('provider', 32)->nullable();
            $table->string('external_id', 64)->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index('name');
        });

        Schema::create('competition_team', function (Blueprint $table) {
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->primary(['competition_id', 'team_id']);
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('nationality_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('position', 30)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo_url')->nullable();
            $table->text('bio')->nullable();
            $table->string('provider', 32)->nullable();
            $table->string('external_id', 64)->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index('name');
        });

        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->restrictOnDelete();
            $table->timestamp('kickoff_at')->index();
            $table->string('status', 10)->default('NS');
            $table->string('status_long', 40)->nullable();
            $table->unsignedSmallInteger('elapsed')->nullable();
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->unsignedTinyInteger('home_ht_score')->nullable();
            $table->unsignedTinyInteger('away_ht_score')->nullable();
            $table->unsignedTinyInteger('home_pen_score')->nullable();
            $table->unsignedTinyInteger('away_pen_score')->nullable();
            $table->string('round')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('referee')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('provider', 32)->nullable();
            $table->string('external_id', 64)->nullable();
            $table->timestamp('details_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'external_id']);
            $table->index(['competition_id', 'kickoff_at']);
            $table->index(['status', 'kickoff_at']);
            $table->index(['home_team_id', 'kickoff_at']);
            $table->index(['away_team_id', 'kickoff_at']);
            $table->index(['is_featured', 'kickoff_at']);
        });

        Schema::create('fixture_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('minute')->default(0);
            $table->unsignedSmallInteger('extra_minute')->nullable();
            $table->string('type', 20); // goal | card | subst | var
            $table->string('detail')->nullable();
            $table->string('player_name')->nullable();
            $table->string('assist_name')->nullable();
            $table->string('comments')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['fixture_id', 'sort_order']);
        });

        Schema::create('fixture_lineups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('formation', 20)->nullable();
            $table->string('coach_name')->nullable();
            $table->json('starting_xi')->nullable();
            $table->json('substitutes')->nullable();
            $table->timestamps();
            $table->unique(['fixture_id', 'team_id']);
        });

        Schema::create('fixture_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('value', 30)->nullable();
            $table->timestamps();
            $table->unique(['fixture_id', 'team_id', 'type']);
        });

        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('group_name', 60)->default('');
            $table->unsignedSmallInteger('rank');
            $table->unsignedSmallInteger('played')->default(0);
            $table->unsignedSmallInteger('won')->default(0);
            $table->unsignedSmallInteger('drawn')->default(0);
            $table->unsignedSmallInteger('lost')->default(0);
            $table->unsignedSmallInteger('goals_for')->default(0);
            $table->unsignedSmallInteger('goals_against')->default(0);
            $table->smallInteger('goal_difference')->default(0);
            $table->smallInteger('points')->default(0);
            $table->string('form', 10)->nullable();
            $table->string('description')->nullable();
            $table->json('home')->nullable();
            $table->json('away')->nullable();
            $table->timestamps();
            $table->unique(['season_id', 'group_name', 'team_id']);
            $table->index(['season_id', 'group_name', 'rank']);
        });

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('resource', 60);
            $table->string('status', 20); // success | failed | skipped
            $table->unsignedInteger('records')->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['provider', 'created_at']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->string('player_name');
            $table->foreignId('from_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('from_team_name')->nullable();
            $table->foreignId('to_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('to_team_name')->nullable();
            $table->string('status', 20); // rumour | confirmed | loan | renewal | departure | free
            $table->string('reliability', 20)->nullable(); // for rumours: low | medium | high
            $table->decimal('fee_amount', 14, 2)->nullable();
            $table->string('fee_currency', 3)->nullable();
            $table->boolean('fee_verified')->default(false);
            $table->string('fee_note')->nullable();
            $table->string('contract_until')->nullable();
            $table->string('transfer_window', 40)->nullable();
            $table->string('source_name');
            $table->string('source_url')->nullable();
            $table->text('summary')->nullable();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->timestamp('reported_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'reported_at']);
            $table->index(['from_team_id', 'reported_at']);
            $table->index(['to_team_id', 'reported_at']);
            $table->index(['player_id', 'reported_at']);
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('fixture_id')->references('id')->on('fixtures')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['fixture_id']);
        });

        foreach (['transfers', 'sync_logs', 'standings', 'fixture_statistics', 'fixture_lineups', 'fixture_events', 'fixtures', 'players', 'competition_team', 'teams', 'seasons', 'competitions', 'countries', 'sports'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
