<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Throwable;

/**
 * Phone photos arrive as multi-megabyte JPEGs that catalogue grids then serve
 * at card size. Every upload is re-encoded to WebP — capped at a size no
 * storefront slot needs — and a small thumbnail is generated next to it so
 * listings never ship full-resolution files.
 */
class ImageOptimizer
{
    private const MAX_DIMENSION = 1600;

    private const THUMB_DIMENSION = 640;

    private const QUALITY = 82;

    /**
     * @return array{path: string, thumb: ?string}
     */
    public function store(UploadedFile $file, string $directory, int $maxDimension = self::MAX_DIMENSION, ?int $thumbDimension = self::THUMB_DIMENSION): array
    {
        try {
            return $this->storeEncoded($file->getRealPath(), $directory, $maxDimension, $thumbDimension);
        } catch (Throwable $exception) {
            // A file that passes `image` validation can still fail to decode;
            // keep the original upload rather than losing the admin's work.
            Log::warning('Image optimization failed, storing the original file', [
                'file' => $file->getClientOriginalName(),
                'error' => $exception->getMessage(),
            ]);

            return ['path' => $file->store($directory, 'public'), 'thumb' => null];
        }
    }

    /**
     * Re-encodes a file that is already on the public disk, for the backfill
     * command. Returns null when the stored file is gone or cannot be decoded.
     *
     * @return array{path: string, thumb: ?string}|null
     */
    public function optimizeStored(string $storedPath, string $directory): ?array
    {
        $realPath = Storage::disk('public')->path($storedPath);

        if (! is_file($realPath)) {
            return null;
        }

        try {
            return $this->storeEncoded($realPath, $directory, self::MAX_DIMENSION, self::THUMB_DIMENSION);
        } catch (Throwable $exception) {
            Log::warning('Stored image could not be optimized', [
                'path' => $storedPath,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{path: string, thumb: ?string}
     */
    private function storeEncoded(string $realPath, string $directory, int $maxDimension, ?int $thumbDimension): array
    {
        $image = ImageManager::gd()->read($realPath)->orient();
        $name = Str::uuid()->toString();

        // Intervention images are mutable: the full file must be encoded
        // before the thumbnail resize mutates the same instance.
        $path = $this->put($directory.'/'.$name.'.webp', $image->scaleDown($maxDimension, $maxDimension));
        $thumb = $thumbDimension === null
            ? null
            : $this->put($directory.'/'.$name.'-thumb.webp', $image->scaleDown($thumbDimension, $thumbDimension));

        return ['path' => $path, 'thumb' => $thumb];
    }

    private function put(string $path, ImageInterface $image): string
    {
        Storage::disk('public')->put($path, (string) $image->toWebp(quality: self::QUALITY));

        return $path;
    }
}
