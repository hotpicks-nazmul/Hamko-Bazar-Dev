<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateShortDescriptions extends Command
{
    protected $signature = 'products:generate-short-descriptions
                            {--force : Overwrite existing short descriptions}
                            {--id= : Only process a specific product ID}';

    protected $description = 'Generate concise short descriptions from product data';

    public function handle()
    {
        $query = Product::query();

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        } elseif (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('short_description')
                  ->orWhere('short_description', '');
            });
        }

        $count = $query->count();
        $this->info("Processing {$count} products...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunk(50, function ($products) use ($bar) {
            foreach ($products as $product) {
                $short = $this->generateShortDescription($product);
                if ($short) {
                    $product->short_description = $short;
                    $product->save();
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Done!');
    }

    private function generateShortDescription(Product $product): string
    {
        $parts = [];

        // Product name as starting point
        $name = trim($product->name ?? '');
        if ($name) {
            $parts[] = $name;
        }

        // Category (convert to proper case)
        $cat = $product->category->name ?? '';
        $cat = $cat ? ucwords(strtolower($cat)) : '';
        if ($cat) {
            $parts[] = "is a high-quality {$cat}";
        } else {
            $parts[] = 'is a high-quality product';
        }

        // Brand
        $brand = $product->brand ?? '';
        if (!$brand && $product->long_description) {
            if (preg_match('/Brand\s*:?\s*([^\s,;.]+)/i', $product->long_description, $m)) {
                $brand = trim($m[1]);
            }
        }
        if ($brand) {
            $parts[count($parts) - 1] .= " from {$brand}";
        }

        // Material
        $material = '';
        if ($product->long_description && preg_match('/Material\s*:?\s*([A-Za-z][^,\n;.]+)/i', $product->long_description, $m)) {
            $material = trim($m[1]);
        }
        if ($material) {
            $parts[] = "made of {$material}";
        }

        // Feature descriptors from product name or description
        $features = [];
        if ($product->long_description) {
            // Capacity
            if (preg_match('/Capacity\s*:?\s*([\d.]+(?:\s*[a-zA-Z]+)?)/i', $product->long_description, $m)) {
                $features[] = trim($m[1]) . ' capacity';
            }
            // Weight
            if (!$features && preg_match('/Weight\s*:?\s*([\d.]+(?:\s*[a-zA-Z]+)?)/i', $product->long_description, $m)) {
                $features[] = trim($m[1]);
            }
            // Dimensions
            if (!$features && preg_match('/(\d+(?:\.\d+)?\s*(?:cm|inch|mm))/i', $product->long_description, $m)) {
                $features[] = trim($m[1]);
            }
        }

        if ($features) {
            $parts[] = 'with ' . $features[0];
        }

        // Combine
        $sentence = implode(', ', $parts) . '.';
        $sentence = preg_replace('/\s+/', ' ', $sentence);
        $sentence = trim($sentence);

        // Keep it short (max 200 chars)
        if (mb_strlen($sentence) > 200) {
            $sentence = mb_substr($sentence, 0, 197) . '...';
        }

        return $sentence;
    }
}
