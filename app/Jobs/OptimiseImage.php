<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Generates responsive WebP variants (sm/md/lg) with GD and records image dimensions.
 * Originals are kept untouched; variants are served via srcset.
 */
class OptimiseImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public int $mediaId) {}

    public function handle(): void
    {
        $media = Media::find($this->mediaId);

        if (! $media || ! $media->isImage() || ! function_exists('imagewebp')) {
            return;
        }

        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            return;
        }

        $source = @imagecreatefromstring($disk->get($media->path));

        if ($source === false) {
            Log::warning('OptimiseImage: unreadable image', ['media' => $media->id]);

            return;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $variants = [];
        $base = pathinfo($media->path, PATHINFO_DIRNAME).'/variants/'.pathinfo($media->path, PATHINFO_FILENAME);

        foreach (Media::VARIANT_WIDTHS as $key => $targetWidth) {
            $w = min($targetWidth, $width);
            $h = (int) round($height * ($w / $width));

            $canvas = imagecreatetruecolor($w, $h);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $w, $h, $width, $height);

            ob_start();
            imagewebp($canvas, null, 80);
            $binary = ob_get_clean();
            imagedestroy($canvas);

            $path = "{$base}-{$key}.webp";
            $disk->put($path, $binary, ['visibility' => 'public']);
            $variants[$key] = $path;

            if ($w === $width) {
                break; // no point upscaling beyond the original
            }
        }

        imagedestroy($source);

        $media->forceFill(['width' => $width, 'height' => $height, 'variants' => $variants])->saveQuietly();
    }
}
