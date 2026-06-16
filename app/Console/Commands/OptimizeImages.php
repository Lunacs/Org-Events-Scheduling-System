<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Intervention\Image\Laravel\Facades\Image;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--force : Overwrite existing optimized images}';

    protected $description = 'Generate optimized WebP versions of public images at multiple responsive sizes';

    /** @var array<int> */
    private array $heroWidths = [480, 768, 1024, 1280];

    public function handle(): int
    {
        $outputDir = public_path('images/optimized');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
            $this->info("Created output directory: {$outputDir}");
        }

        $this->optimizeHeroImage($outputDir);
        $this->optimizeLogo($outputDir, 'osa-logo.jpg', 200);
        $this->optimizeLogo($outputDir, 'plv-logo.png', 80);

        $this->newLine();
        $this->info('✅ Image optimization complete!');

        return self::SUCCESS;
    }

    private function optimizeHeroImage(string $outputDir): void
    {
        $source = public_path('images/suhay husay.png');

        if (! file_exists($source)) {
            $this->error("Hero image not found: {$source}");

            return;
        }

        $this->info('Optimizing hero image: suhay husay.png ('.$this->formatBytes(filesize($source)).')');

        foreach ($this->heroWidths as $width) {
            $outputPath = "{$outputDir}/hero-{$width}w.webp";

            if (file_exists($outputPath) && ! $this->option('force')) {
                $this->line("  ⏭  hero-{$width}w.webp already exists (use --force to overwrite)");

                continue;
            }

            $image = Image::read($source);
            $image->scaleDown(width: $width);
            $image->toWebp(quality: 75)->save($outputPath);

            $this->line("  ✅ hero-{$width}w.webp — ".$this->formatBytes(filesize($outputPath)));
        }
    }

    private function optimizeLogo(string $outputDir, string $filename, int $size): void
    {
        $source = public_path("images/{$filename}");

        if (! file_exists($source)) {
            $this->error("Logo not found: {$source}");

            return;
        }

        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $outputPath = "{$outputDir}/{$baseName}.webp";

        if (file_exists($outputPath) && ! $this->option('force')) {
            $this->line("  ⏭  {$baseName}.webp already exists (use --force to overwrite)");

            return;
        }

        $this->info("Optimizing logo: {$filename} (".$this->formatBytes(filesize($source)).')');

        $image = Image::read($source);
        $image->cover($size, $size);
        $image->toWebp(quality: 80)->save($outputPath);

        $this->line("  ✅ {$baseName}.webp ({$size}x{$size}) — ".$this->formatBytes(filesize($outputPath)));
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1024, 1).' KB';
    }
}
