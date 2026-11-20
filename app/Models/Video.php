<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use Auditable, HasFactory, HasSlug;

    public const TYPES = [
        'interview' => 'Interview',
        'press_conference' => 'Press conference',
        'match_analysis' => 'Match analysis',
        'feature' => 'Feature',
        'news' => 'Video news report',
        'programme' => 'SportyCiti programme',
    ];

    protected $fillable = [
        'title', 'slug', 'description', 'video_type', 'source', 'youtube_id', 'file_path', 'thumbnail_media_id',
        'category_id', 'duration_seconds', 'rights_confirmed', 'rights_holder', 'is_featured', 'status', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rights_confirmed' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Only rights-cleared, published videos are ever shown publicly. */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')->where('rights_confirmed', true)->where('published_at', '<=', now());
    }

    public function url(): string
    {
        return route('videos.show', $this->slug);
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail) {
            return $this->thumbnail->url('md');
        }

        return $this->youtube_id ? "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg" : null;
    }

    public function embedUrl(): ?string
    {
        return $this->youtube_id ? "https://www.youtube-nocookie.com/embed/{$this->youtube_id}" : null;
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->video_type] ?? ucfirst((string) $this->video_type);
    }

    /** Accepts a full YouTube URL or a bare ID and returns the 11-char ID. */
    public static function parseYoutubeId(?string $input): ?string
    {
        if (blank($input)) {
            return null;
        }

        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $input)) {
            return $input;
        }

        if (preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $input, $m)) {
            return $m[1];
        }

        return null;
    }
}
