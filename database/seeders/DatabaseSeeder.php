<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $categories = [
            ['name' => 'Graphics Card', 'extension' => 'jpg'],
            ['name' => 'Processor', 'extension' => 'webp'],
            ['name' => 'Monitor', 'extension' => 'jpg'],
            ['name' => 'Memory', 'extension' => 'jpg'],
            ['name' => 'Motherboard', 'extension' => 'jpg'],
            ['name' => 'Storage Drive', 'extension' => 'webp'],
            ['name' => 'PC Case', 'extension' => 'webp'],
            ['name' => 'Power Supply', 'extension' => 'webp'],
        ];

        $categoryMap = [];
        foreach ($categories as $key => $categoryData) {
            $category = Category::create([
                'name' => $categoryData['name'],
                'extension' => $categoryData['extension'],
            ]);

            $categoryMap[$categoryData['name']] = $category->id;

            $placeholderPath = public_path("placeholders/categories/category" . ($key + 1) . ".jpg");
            $targetPath = Storage::disk('public')->path("uploads/categories/{$category->id}.{$categoryData['extension']}");
            if (file_exists($placeholderPath)) {
                copy($placeholderPath, $targetPath);
            }
        }

        $products = [
            ['name' => 'GTX 1660 Super', 'description' => 'NVIDIA GPU 6GB Vram', 'price' => 11999.00, 'stock' => 42, 'extension' => 'jpg', 'category_name' => 'Graphics Card'],
            ['name' => 'Intel Core i5-10400K', 'description' => 'Intel Core CPU 10th gen', 'price' => 7800.00, 'stock' => 41, 'extension' => 'jpg', 'category_name' => 'Processor'],
            ['name' => 'Dell 24 inch Full HD', 'description' => 'Dell Monitor IPS Display', 'price' => 7800.00, 'stock' => 1, 'extension' => 'jpg', 'category_name' => 'Monitor'],
            ['name' => 'RTX 4080 Super', 'description' => 'Gigabyte 16GB of Vram', 'price' => 68000.00, 'stock' => 48, 'extension' => 'webp', 'category_name' => 'Graphics Card'],
            ['name' => 'RTX 4080', 'description' => 'MSI 16GB of Vram', 'price' => 62000.00, 'stock' => 43, 'extension' => 'jpg', 'category_name' => 'Graphics Card'],
            ['name' => 'RTX 4090 Colorful', 'description' => 'MSI 16GB of Vram', 'price' => 94000.00, 'stock' => 42, 'extension' => 'jpg', 'category_name' => 'Graphics Card'],
            ['name' => 'Corsair LPX 16GB ram', 'description' => 'Corsair Vengeance DDR4 3200MHz', 'price' => 4200.00, 'stock' => 45, 'extension' => 'webp', 'category_name' => 'Memory'],
            ['name' => 'T-Force Vulcan Ram', 'description' => 'TeamGroup DDR4 3600MHz', 'price' => 1900.00, 'stock' => 48, 'extension' => 'webp', 'category_name' => 'Memory'],
            ['name' => 'Ryzen 5 5600G', 'description' => 'AMD Ryzen CPU', 'price' => 5700.00, 'stock' => 43, 'extension' => 'jpg', 'category_name' => 'Processor'],
            ['name' => 'LG OLED24hz Curved', 'description' => 'UltraGear 34inch OLED 240hz curved', 'price' => 62000.00, 'stock' => 44, 'extension' => 'jpeg', 'category_name' => 'Monitor'],
            ['name' => 'Fractal North MATX', 'description' => 'Charcoal Black Chassis PC Case', 'price' => 9000.00, 'stock' => 42, 'extension' => 'webp', 'category_name' => 'PC Case'],
            ['name' => 'B650 AORUS ELITE', 'description' => 'Gigabyte ATX Motherboard', 'price' => 9500.00, 'stock' => 48, 'extension' => 'webp', 'category_name' => 'Motherboard'],
            ['name' => 'Ripjaws V 16GB Ram', 'description' => 'G.Skill DDR4-3200mhz', 'price' => 3800.00, 'stock' => 49, 'extension' => 'jpg', 'category_name' => 'Memory'],
            ['name' => 'TUF GAMING B450M', 'description' => 'ASUS Motherboard PLUS', 'price' => 2800.00, 'stock' => 40, 'extension' => 'jpg', 'category_name' => 'Motherboard'],
            ['name' => 'SanDisk SSD', 'description' => 'SanDisk 128Gb SATA', 'price' => 2000.00, 'stock' => 41, 'extension' => 'jpg', 'category_name' => 'Storage Drive'],
            ['name' => 'Ryzen 7 5700X', 'description' => 'AMD Ryzen CPU', 'price' => 12000.00, 'stock' => 42, 'extension' => 'jpg', 'category_name' => 'Processor'],
            ['name' => 'AOC G2 Monitor 165Hz', 'description' => 'AdaptiveSync 23.8 inch Gaming', 'price' => 7600.00, 'stock' => 43, 'extension' => 'jpg', 'category_name' => 'Monitor'],
            ['name' => 'MSI Tomahawk B650', 'description' => 'MSI Motherboard mATX', 'price' => 9000.00, 'stock' => 45, 'extension' => 'jpg', 'category_name' => 'Motherboard'],
            ['name' => 'Trident Z 16GB Ram', 'description' => 'G.Skill RGB 2x8 DDR4 3200Mhz', 'price' => 6000.00, 'stock' => 42, 'extension' => 'jpg', 'category_name' => 'Memory'],
            ['name' => 'Power Train Case', 'description' => 'Tempered Glass Mid Tower PC Gaming', 'price' => 4800.00, 'stock' => 43, 'extension' => 'jpg', 'category_name' => 'PC Case'],
            ['name' => 'Asus ROG B650A', 'description' => 'Asus ROG B650A Gaming Motherboard', 'price' => 9000.00, 'stock' => 48, 'extension' => 'jpg', 'category_name' => 'Motherboard'],
            ['name' => 'Thermaltake Monitor', 'description' => 'Thermaltake Curve Gaming Monitor', 'price' => 11000.00, 'stock' => 49, 'extension' => 'jpg', 'category_name' => 'Monitor'],
        ];

        foreach ($products as $key => $productData) {
            $categoryId = $categoryMap[$productData['category_name']] ?? null;

            if ($categoryId) {
                $product = Product::create([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'category_id' => $categoryId,
                    'stock' => $productData['stock'],
                    'extension' => $productData['extension'],
                ]);

                $placeholderPath = public_path("placeholders/products/product" . ($key + 1) . ".jpg");
                $targetPath = Storage::disk('public')->path("uploads/products/{$product->id}.{$productData['extension']}");
                if (file_exists($placeholderPath)) {
                    copy($placeholderPath, $targetPath);
                }
            }
        }

        $user = User::create([
            'username' => 'admin',
            'email' => 'administrator@gmail.com',
            'password' => Hash::make('123123123'),
        ]);

        $user->profile()->create([
            'first_name' => 'Admin',
            'last_name' => 'Istrator',
            'phone_number' => '09865464564',
            'address' => '135 Santolan Street Pasig City',
        ]);
    }
}