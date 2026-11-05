<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogueSeeder extends Seeder
{
    private const SIZES = ['S', 'M', 'L', 'XL'];

    public function run(): void
    {
        foreach ($this->categories() as $sort => $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name_fr' => $category['name_fr'],
                    'name_en' => $category['name_en'],
                    'sort_order' => $sort + 1,
                    'is_active' => true,
                ]
            );
        }

        foreach ($this->products() as $index => $data) {
            $baseSku = sprintf('SRK-%s-%03d', $data['code'], $index + 1);

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name_fr'])],
                [
                    'category_id' => Category::firstWhere('slug', $data['category'])->id,
                    'name_fr' => $data['name_fr'],
                    'name_en' => $data['name_en'],
                    'description_fr' => $data['name_fr'].' — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.',
                    'description_en' => $data['name_en'].' — hand-made in our atelier, premium fabric and careful finishing.',
                    'base_price' => $data['price'],
                    'base_sku' => $baseSku,
                    'is_active' => true,
                    'is_featured' => $index < 4,
                ]
            );

            $this->syncVariants($product, $baseSku, $data['sizes'] ?? null, $data['colors'] ?? null);
        }
    }

    private function syncVariants(Product $product, string $baseSku, ?array $sizes, ?array $colors): void
    {
        if ($sizes === null || $colors === null) {
            ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'size' => ProductVariant::DEFAULT, 'color' => ProductVariant::DEFAULT],
                ['sku' => $baseSku.'-DEF', 'stock_qty' => 15, 'price_delta' => 0, 'is_active' => true]
            );

            return;
        }

        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'size' => $size, 'color' => $color],
                    [
                        'sku' => $baseSku.'-'.strtoupper(Str::slug($size.'-'.$color, '-')),
                        'stock_qty' => 12,
                        'price_delta' => 0,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function categories(): array
    {
        return [
            ['slug' => 'boubous', 'name_fr' => 'Boubous', 'name_en' => 'Boubous'],
            ['slug' => 'caftans', 'name_fr' => 'Caftans', 'name_en' => 'Caftans'],
            ['slug' => 'ensembles-wax', 'name_fr' => 'Ensembles Wax', 'name_en' => 'Wax Sets'],
            ['slug' => 'robes-de-soiree', 'name_fr' => 'Robes de soirée', 'name_en' => 'Evening Dresses'],
            ['slug' => 'accessoires', 'name_fr' => 'Accessoires', 'name_en' => 'Accessories'],
            ['slug' => 'pret-a-porter', 'name_fr' => 'Prêt-à-porter', 'name_en' => 'Ready to Wear'],
        ];
    }

    private function products(): array
    {
        return [
            $this->product('boubous', 'BOU', 'Boubou Royale', 'Royale Boubou', 1450, ['Or', 'Ivoire']),
            $this->product('boubous', 'BOU', 'Boubou Sahara', 'Sahara Boubou', 1200, ['Terracotta', 'Sable']),
            $this->product('boubous', 'BOU', 'Boubou Nuit d’Encre', 'Ink Night Boubou', 1650, ['Noir', 'Bleu nuit']),
            $this->product('boubous', 'BOU', 'Boubou Héritage', 'Heritage Boubou', 1950, ['Bordeaux', 'Or']),

            $this->product('caftans', 'CAF', 'Caftan Djoliba', 'Djoliba Caftan', 980, ['Émeraude', 'Ivoire']),
            $this->product('caftans', 'CAF', 'Caftan Sable d’Or', 'Golden Sand Caftan', 1120, ['Sable', 'Or']),
            $this->product('caftans', 'CAF', 'Caftan Médina', 'Medina Caftan', 1250, ['Blanc', 'Noir']),

            $this->product('ensembles-wax', 'WAX', 'Ensemble Sunrise', 'Sunrise Set', 620, ['Orange', 'Turquoise']),
            $this->product('ensembles-wax', 'WAX', 'Ensemble Cotonou Bloom', 'Cotonou Bloom Set', 540, ['Rose', 'Vert']),
            $this->product('ensembles-wax', 'WAX', 'Ensemble Sahel Wave', 'Sahel Wave Set', 680, ['Indigo', 'Sable']),
            $this->product('ensembles-wax', 'WAX', 'Ensemble Kente Dream', 'Kente Dream Set', 760, ['Or', 'Noir']),

            $this->product('robes-de-soiree', 'SOI', 'Robe Étoile de Dakar', 'Dakar Star Gown', 1800, ['Noir', 'Or']),
            $this->product('robes-de-soiree', 'SOI', 'Robe Velvet Nights', 'Velvet Nights Gown', 2100, ['Bordeaux', 'Vert']),
            $this->product('robes-de-soiree', 'SOI', 'Robe Perle de l’Océan', 'Ocean Pearl Gown', 1950, ['Ivoire', 'Bleu ciel']),

            $this->accessory('accessoires', 'ACC', 'Foulard Impérial', 'Imperial Scarf', 180),
            $this->accessory('accessoires', 'ACC', 'Bijoux de Mariée', 'Bridal Jewellery', 320),
            $this->accessory('accessoires', 'ACC', 'Sac Raffia', 'Raffia Bag', 260),

            $this->product('pret-a-porter', 'PRE', 'Ensemble Tam-Tam', 'Tam-Tam Set', 480, ['Rouge', 'Noir']),
            $this->product('pret-a-porter', 'PRE', 'Chemise Sahel', 'Sahel Shirt', 350, ['Blanc', 'Bleu']),
            $this->product('pret-a-porter', 'PRE', 'Jupe Wax Classique', 'Classic Wax Skirt', 290, ['Multicolore', 'Noir']),
        ];
    }

    private function product(string $category, string $code, string $nameFr, string $nameEn, float $price, array $colors): array
    {
        return [
            'category' => $category,
            'code' => $code,
            'name_fr' => $nameFr,
            'name_en' => $nameEn,
            'price' => $price,
            'sizes' => self::SIZES,
            'colors' => $colors,
        ];
    }

    private function accessory(string $category, string $code, string $nameFr, string $nameEn, float $price): array
    {
        return [
            'category' => $category,
            'code' => $code,
            'name_fr' => $nameFr,
            'name_en' => $nameEn,
            'price' => $price,
        ];
    }
}
