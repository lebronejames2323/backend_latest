<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Address;
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
            ['name' => 'ASUS ROG RTX 4080', 'description' => 'ASUS ROG Strix Gaming 16GB of Vram', 'price' => 62000.00, 'stock' => 43, 'extension' => 'jpg', 'category_name' => 'Graphics Card'],
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
            ['name' => 'Samsung NVMe 980 Pro', 'description' => 'Samsung NVMe M.2 SSD 980 PRO lightning-fast read', 'price' => 4700.00, 'stock' => 30, 'extension' => 'jpg', 'category_name' => 'Storage Drive'],
            ['name' => 'Lenovo G27 Monitor', 'description' => 'Lenovo G27 27 Inches Gaming Monitor', 'price' => 7500.00, 'stock' => 27, 'extension' => 'webp', 'category_name' => 'Monitor'],
            ['name' => 'Western Digital SSD', 'description' => 'Western Digital Green 240GB SSD', 'price' => 2100.00, 'stock' => 23, 'extension' => 'webp', 'category_name' => 'Storage Drive'],
            ['name' => 'Team Elite T Force RGB', 'description' => 'Team Elite T Force Delta 8GB Memory 3600MHz DDR4', 'price' => 3800.00, 'stock' => 30, 'extension' => 'jpg', 'category_name' => 'Memory'],
            ['name' => 'WD Blue NVMe SSD', 'description' => 'WD Blue NVMe M.2 Gen 4.0 500GB', 'price' => 3200.00, 'stock' => 30, 'extension' => 'webp', 'category_name' => 'Storage Drive'],
            ['name' => 'Teamgroup NVMe SSD', 'description' => 'Teamgroup MP3 M.2 PCIe Gen 4 512GB', 'price' => 3900.00, 'stock' => 30, 'extension' => 'jpg', 'category_name' => 'Storage Drive'],
            ['name' => 'Phanteks XT Pro Ultra', 'description' => 'Phanteks XT Pro Ultra Gaming PC Case', 'price' => 2700.00, 'stock' => 25, 'extension' => 'jpg', 'category_name' => 'PC Case'],
            ['name' => 'MSI B550-A PRO', 'description' => 'MSI B550-A PRO Micro-ATX Motherboard', 'price' => 6600.00, 'stock' => 30, 'extension' => 'webp', 'category_name' => 'Motherboard'],
            ['name' => 'ASUS ROG Strix B550F', 'description' => 'ASUS ROG Strix B550-F Gaming Motherboard', 'price' => 6700.00, 'stock' => 35, 'extension' => 'jpg', 'category_name' => 'Motherboard'],
            ['name' => 'MSI MPG Case', 'description' => 'MSI MPG Gungnir 300R Airflow PC Case ATX', 'price' => 4100.00, 'stock' => 32, 'extension' => 'webp', 'category_name' => 'PC Case'],
            ['name' => 'Ryzen 5 5600', 'description' => 'AMD CPU Ryzen 5 5600 Gaming', 'price' => 10500.00, 'stock' => 30, 'extension' => 'jpg', 'category_name' => 'Processor'],
            ['name' => 'Crucial T700 Nvme SSD', 'description' => 'Crucial T700 1TB M.2 Gen 5 SSD', 'price' => 5100.00, 'stock' => 29, 'extension' => 'webp', 'category_name' => 'Storage Drive'],
            ['name' => 'Corsair CX650 PSU', 'description' => 'Corsair MAG 650W Power Supply', 'price' => 3200.00, 'stock' => 30, 'extension' => 'jpg', 'category_name' => 'Power Supply'],
            ['name' => 'ASUS TUF 850G PSU', 'description' => 'ASUS TUF 850G Gaming PSU 850W 80 Plus Gold', 'price' => 4200.00, 'stock' => 35, 'extension' => 'webp', 'category_name' => 'Power Supply'],
            ['name' => 'SKYNDINNTL PSU', 'description' => 'SKYNDINNTL 1000W Modular PSU', 'price' => 4900.00, 'stock' => 21, 'extension' => 'jpg', 'category_name' => 'Power Supply'],
            ['name' => 'Segotep 650W PSU', 'description' => 'Segotep 650W 80 Plus Gold Certified Non-Modular', 'price' => 3400.00, 'stock' => 28, 'extension' => 'webp', 'category_name' => 'Power Supply']
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

        Address::create([
            'user_id'      => $user->id,
            'full_name'    => 'Admin Istrator',
            'phone_number' => '09865464564',
            'region'       => 'NCR',
            'province'     => 'Metro Manila',
            'city'         => 'Pasig City',
            'barangay'     => 'Santolan',
            'postal_code'  => '1600',
            'street_address' => '135 Santolan Street, Pasig City',
        ]);
    }
}