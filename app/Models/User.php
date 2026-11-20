<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'slug', 'email', 'password', 'avatar_path', 'job_title', 'specialisation',
        'bio', 'social_links', 'is_public_author', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'app_authentication_secret', 'app_authentication_recovery_codes'];

    protected array $auditExclude = ['last_login_at', 'last_login_ip', 'app_authentication_secret', 'app_authentication_recovery_codes'];

    /** Mirror database defaults so freshly-created models behave consistently. */
    protected $attributes = [
        'is_active' => true,
        'is_public_author' => false,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'social_links' => 'array',
            'is_public_author' => 'boolean',
            'is_active' => 'boolean',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }

    // ---- Staff two-factor authentication (Filament app authentication) ----

    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(#[\SensitiveParameter] ?string $secret): void
    {
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    public function saveAppAuthenticationRecoveryCodes(#[\SensitiveParameter] ?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (blank($user->slug) && $user->is_public_author) {
                $base = Str::slug($user->name) ?: 'author';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->whereKeyNot($user->getKey())->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $user->slug = $slug;
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->can('access admin panel');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatarUrl();
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->take(2)->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))->implode('');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->where('status', ArticleStatus::Published)->where('published_at', '<=', now());
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'bookmarks')->withTimestamps();
    }

    public function favouriteCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function isStaff(): bool
    {
        return $this->can('access admin panel');
    }

    public function url(): ?string
    {
        return $this->is_public_author && $this->slug ? route('authors.show', $this->slug) : null;
    }
}
