<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class GenerateThirdLevelCategories extends Command
{
    protected $signature = 'categories:generate-third-level';
    protected $description = 'Create 3rd-level categories under Cookware matching hamkobazar.shop structure';

    // Live site hierarchy: subcategory => [3rd-level names => keywords to match in product names]
    private array $hierarchy = [
        'ELECTRIC KITCHEN APPLIANCE' => [
            'GAS STOVE'              => ['GAS STOVE'],
            'RICE COOKER'            => ['RICE COOKER', 'Rice Cooker'],
            'KETTLE'                 => ['KETTLE', 'kettle', 'Kettle'],
            'BLENDER AND GRINDER'    => ['BLENDER', 'GRINDER', 'Mixer Grinder'],
            'INDUCTION COOKER'       => ['INDUCTION COOKER', 'Induction Cooker'],
            'INFRARED COOKER'        => ['INFRARED COOKER', 'Infrared Cooker'],
        ],
        'PRESSURE COOKER' => [
            'SS PRESSURE COOKER 3 LAYER' => ['Three Layer Pressure Cooker', '3 Layer Pressure Cooker'],
            'OVAL SHAPE PRESSURE COOKER' => ['Pressure Cooker Oval', 'Oval'],
            'STRAIGHT SHAPE PRESSURE COOKER' => ['Pressure Cooker Straight', 'Straight'],
        ],
        'STAINLESS STEEL' => [
            'SAUCEPAN'                     => ['Saucepan', 'SAUCEPAN'],
            'SS FRY PAN THREE LAYER'       => ['3 Layer SS Fry Pan'],
            'SS WOK PAN THREE LAYER'        => ['Three Layer Wokpan', 'Wokpan'],
            'SS FRY PAN'                   => ['Long Handle SS Fry Pan', 'Fry Pan Without', 'Fry Pan With'],
            'SS TWO SIDE HANDLE POT'       => ['Two Handle SS Pot'],
            'SS BOWL'                      => ['Classic Bowl', 'SS Super Bowl', 'SS Classic Bowl'],
            'SS PLATE'                     => ['Classic Plate', 'SS DEEP PLATE', 'SS Deep Baby', 'SS Classic Half', 'SS Flat Plate', 'St. Round Plate'],
            'SS MILK PAN'                  => ['Milk Pan'],
            'SS 3LAYER SAUCEPAN'           => ['3Layer Saucepan'],
        ],
        'NON-STICK PRODUCT' => [
            'WOKPAN'  => ['TH Wok Pan', 'Wok Pan with Glass Lid', 'Wokpan with Glass Lid', 'TH Wokpan'],
            'FRYPAN'  => ['Fry Pan Without Glass Lid', 'Fry Pan With Glass Lid', 'Deep Frypan', 'Grill Pan'],
        ],
        'ALUMINUM PRODUCT' => [
            'ALU SAUCEPAN'         => ['ALU. Saucepan', 'Alu. Saucepan'],
            'ALU DEEP CURRY PAN'   => ['Alu. Deep Curry'],
        ],
        'CLASSIC SUPER KORAI' => [
            'CLASSIC SUPER KORAI PRODUCT' => ['Classic Super Korai', 'Super Korai'],
        ],
        'HOTPOT' => [
            'HOTPOT PRODUCT' => ['Smart Hot Pot', 'Deep Hot Pot', 'Magnum Hotpot'],
        ],
    ];

    private array $flatSubcategories = [
        'CASSEROLE PAN'     => ['Casserole Pan'],
        'PIZZA PAN'         => ['Pizza Pan'],
        'RUTI TOWA'         => ['Ruti Towa'],
        'NON-STICK GIFT SET' => ['Non-stick Gift Set'],
        'LUNCH CARRIER'     => ['Lunch Carrier', 'Tiffin Carrier'],
        'CLASSIC BOX'       => ['Classic Box'],
    ];

    private array $additionalMappings = [
        'STAINLESS STEEL' => [
            'SS BOWL'  => ['Classic Bowl', 'SS Super Bowl', 'SS Classic Bowl'],
            'SS PLATE' => ['Classic Plate', 'SS DEEP PLATE', 'SS Deep Baby', 'SS Classic Half', 'SS Flat Plate', 'St. Round Plate'],
        ],
    ];

    public function handle()
    {
        $cookware = Category::where('slug', 'cookware')->first();
        if (!$cookware) {
            $this->error('Cookware category not found!');
            return 1;
        }

        $created = 0;
        $assigned = 0;

        foreach ($this->hierarchy as $subName => $thirdLevels) {
            $sub = Category::where('name', $subName)->where('parent_id', $cookware->id)->first();
            if (!$sub) {
                $this->warn("Subcategory '{$subName}' not found under Cookware, skipping.");
                continue;
            }

            foreach ($thirdLevels as $thirdName => $keywords) {
            // Create 3rd-level category
            $slug = \Illuminate\Support\Str::slug($thirdName);
            $baseSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $third = Category::firstOrCreate(
                ['name' => $thirdName, 'parent_id' => $sub->id],
                [
                    'slug' => $slug,
                    'status' => 'active',
                    'show_in_menu' => true,
                    'sort_order' => 0,
                ]
            );
                if ($third->wasRecentlyCreated) {
                    $created++;
                    $this->info("Created: {$subName} > {$thirdName}");
                }

                // Find & assign products
                $query = Product::where('is_active', true)
                    ->where(function ($q) use ($sub, $third, $keywords, $cookware) {
                        // Products directly under this subcategory or under Cookware
                        $q->whereIn('category_id', [$sub->id, $cookware->id]);
                    });

                foreach ($keywords as $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                }

                $products = $query->get();
                foreach ($products as $product) {
                    if ($product->category_id != $third->id) {
                        $product->category_id = $third->id;
                        $product->save();
                        $assigned++;
                        $this->line("  Assigned: {$product->name} → {$thirdName}");
                    }
                }
            }
        }

        // Handle flat subcategories (2nd level items without 3rd level)
        foreach ($this->flatSubcategories as $flatName => $keywords) {
            // Create as 2nd level under Cookware if not exists
            $flat = Category::firstOrCreate(
                ['name' => $flatName, 'parent_id' => $cookware->id],
                [
                    'slug' => \Illuminate\Support\Str::slug($flatName),
                    'status' => 'active',
                    'show_in_menu' => true,
                    'sort_order' => 0,
                ]
            );
            if ($flat->wasRecentlyCreated) {
                $created++;
                $this->info("Created flat subcategory: {$flatName}");
            }

            // Move matching products to this flat subcategory
            $query = Product::where('is_active', true)
                ->where('category_id', $cookware->id);

            foreach ($keywords as $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            }

            $products = $query->get();
            foreach ($products as $product) {
                if ($product->category_id != $flat->id) {
                    $product->category_id = $flat->id;
                    $product->save();
                    $assigned++;
                    $this->line("  Assigned: {$product->name} → {$flatName}");
                }
            }
        }

        $this->newLine();

        // Process additional mappings for already-created 3rd-level categories
        foreach ($this->additionalMappings as $subName => $thirdLevels) {
            $sub = Category::where('name', $subName)->where('parent_id', $cookware->id)->first();
            if (!$sub) continue;

            foreach ($thirdLevels as $thirdName => $keywords) {
                $third = Category::where('name', $thirdName)->where('parent_id', $sub->id)->first();
                if (!$third) {
                    $this->warn("3rd-level '{$thirdName}' not found under '{$subName}', skipping.");
                    continue;
                }

                $query = Product::where('is_active', true)
                    ->whereIn('category_id', [$sub->id, $cookware->id]);

                foreach ($keywords as $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                }

                $products = $query->get();
                foreach ($products as $product) {
                    if ($product->category_id != $third->id) {
                        $product->category_id = $third->id;
                        $product->save();
                        $assigned++;
                        $this->line("  Assigned: {$product->name} → {$thirdName}");
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Done! Created {$created} new categories, assigned {$assigned} products.");
        $this->warn("Products remaining under Cookware (unmatched): " . Product::where('is_active', true)->where('category_id', $cookware->id)->count());

        return 0;
    }
}
