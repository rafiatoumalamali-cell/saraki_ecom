<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\GalleryItem;
use App\Models\ProductImage;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Uploads made before the WebP pipeline store the original file with no
 * thumbnail. This re-encodes them so old catalogue photos get the same
 * weight savings as new ones.
 */
class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--dry-run : Report what would change without writing}';

    protected $description = 'Re-encode stored images to WebP and generate missing thumbnails';

    public function handle(ImageOptimizer $optimizer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $done = 0;
        $skipped = 0;

        $jobs = [
            ['model' => ProductImage::class, 'image' => 'path', 'thumb' => 'thumb_path', 'directory' => 'products'],
            ['model' => GalleryItem::class, 'image' => 'image_path', 'thumb' => 'thumb_path', 'directory' => 'content/gallery'],
            ['model' => BlogPost::class, 'image' => 'cover_image', 'thumb' => 'cover_thumb', 'directory' => 'content/posts'],
        ];

        foreach ($jobs as $job) {
            $job['model']::query()
                ->whereNotNull($job['image'])
                ->whereNull($job['thumb'])
                ->chunkById(50, function ($rows) use ($job, $optimizer, $dryRun, &$done, &$skipped) {
                    // Encoding writes files, so a dry run only counts rows.
                    if ($dryRun) {
                        $done += $rows->count();

                        return;
                    }

                    foreach ($rows as $row) {
                        $stored = $optimizer->optimizeStored($row->{$job['image']}, $job['directory']);

                        if ($stored === null) {
                            $skipped++;

                            continue;
                        }

                        $previous = [$row->{$job['image']}, $row->{$job['thumb']}];

                        $row->forceFill([
                            $job['image'] => $stored['path'],
                            $job['thumb'] => $stored['thumb'],
                        ])->save();

                        Storage::disk('public')->delete(array_filter($previous));

                        $done++;
                    }
                });
        }

        $this->info($dryRun
            ? "Would optimize {$done} image(s), skipped {$skipped}."
            : "Optimized {$done} image(s), skipped {$skipped}.");

        return self::SUCCESS;
    }
}
