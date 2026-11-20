<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('avatar_path')->nullable()->after('email');
            $table->string('job_title')->nullable();
            $table->string('specialisation')->nullable();
            $table->text('bio')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_public_author')->default(false)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['is_public_author']);
            $table->dropColumn(['slug', 'avatar_path', 'job_title', 'specialisation', 'bio', 'social_links', 'is_public_author', 'is_active', 'last_login_at', 'last_login_ip']);
        });
    }
};
