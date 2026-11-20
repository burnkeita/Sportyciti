<?php

namespace App\Observers;

use App\Jobs\OptimiseImage;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    public function saving(Media $media): void
    {
        if ($media->isDirty('path') && $media->path) {
            $disk = Storage::disk($media->disk);

            if ($disk->exists($media->path)) {
                $media->mime_type = $disk->mimeType($media->path) ?: $media->mime_type;
                $media->size = $disk->size($media->path);
            }

            $media->uploaded_by ??= auth()->id();
            $media->variants = null;
        }

        if (blank($media->title) && $media->path) {
            $media->title = pathinfo($media->path, PATHINFO_FILENAME);
        }
    }

    public function saved(Media $media): void
    {
        if ($media->wasChanged('path') || $media->wasRecentlyCreated) {
            OptimiseImage::dispatch($media->id);
        }
    }

    public function deleted(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        $disk->delete(array_filter([$media->path, ...array_values($media->variants ?? [])]));
    }
}
