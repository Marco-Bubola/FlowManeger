<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar as categorias criadas para o usuário 2
        $categories = Category::where('user_id', 2)->where('type', 'product')->get()->keyBy('name');

        // Produtos Eudora e Boticário por categoria
        $products = [
            // Perfumaria
            [
                'name' => 'Perfume Eudora Siàge Nutrição Intensa 75ml',
                'description' => 'Perfume feminino com fragrância floral frutada, ideal para o dia a dia',
                'price' => 89.90,
                'cost_price' => 45.00,
                'stock_quantity' => 25,
                'barcode' => '7898623830012',
                'category_id' => $categories['Perfumaria']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Colônia Boticário Malbec 100ml',
                'description' => 'Perfume masculino amadeirado e sofisticado',
                'price' => 149.90,
                'cost_price' => 75.00,
                'stock_quantity' => 15,
                'barcode' => '7898623830013',
                'category_id' => $categories['Perfumaria']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Perfume Eudora Intense 75ml',
                'description' => 'Fragrância feminina intensa e marcante',
                'price' => 95.00,
                'cost_price' => 48.00,
                'stock_quantity' => 20,
                'barcode' => '7898623830014',
                'category_id' => $categories['Perfumaria']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Maquiagem
            [
                'name' => 'Base Eudora Glam Skin Perfection',
                'description' => 'Base líquida com cobertura natural e longa duração',
                'price' => 45.90,
                'cost_price' => 23.00,
                'stock_quantity' => 30,
                'barcode' => '7898623830015',
                'category_id' => $categories['Maquiagem']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Batom Boticário Make B. Matte',
                'description' => 'Batom líquido matte de longa duração',
                'price' => 29.90,
                'cost_price' => 15.00,
                'stock_quantity' => 40,
                'barcode' => '7898623830016',
                'category_id' => $categories['Maquiagem']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Rímel Eudora Glam Spectacular',
                'description' => 'Rímel para volume e alongamento dos cílios',
                'price' => 35.90,
                'cost_price' => 18.00,
                'stock_quantity' => 25,
                'barcode' => '7898623830017',
                'category_id' => $categories['Maquiagem']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Cuidados com a Pele
            [
                'name' => 'Hidratante Boticário Nativa SPA Quinoa',
                'description' => 'Loção hidratante corporal com quinoa',
                'price' => 39.90,
                'cost_price' => 20.00,
                'stock_quantity' => 35,
                'barcode' => '7898623830018',
                'category_id' => $categories['Cuidados com a Pele']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Protetor Solar Eudora FPS 60',
                'description' => 'Protetor solar facial com proteção UVA/UVB',
                'price' => 55.90,
                'cost_price' => 28.00,
                'stock_quantity' => 20,
                'barcode' => '7898623830019',
                'category_id' => $categories['Cuidados com a Pele']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Cuidados com o Cabelo
            [
                'name' => 'Shampoo Eudora Siàge Nutrição Intensa',
                'description' => 'Shampoo para cabelos ressecados e danificados',
                'price' => 25.90,
                'cost_price' => 13.00,
                'stock_quantity' => 45,
                'barcode' => '7898623830020',
                'category_id' => $categories['Cuidados com o Cabelo']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Condicionador Boticário Acquaflora',
                'description' => 'Condicionador hidratante para todos os tipos de cabelo',
                'price' => 28.90,
                'cost_price' => 14.50,
                'stock_quantity' => 40,
                'barcode' => '7898623830021',
                'category_id' => $categories['Cuidados com o Cabelo']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Higiene Pessoal
            [
                'name' => 'Desodorante Boticário Malbec Roll-on',
                'description' => 'Desodorante antitranspirante masculino',
                'price' => 18.90,
                'cost_price' => 9.50,
                'stock_quantity' => 50,
                'barcode' => '7898623830022',
                'category_id' => $categories['Higiene Pessoal']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Sabonete Eudora Siàge',
                'description' => 'Sabonete hidratante com fragrância suave',
                'price' => 12.90,
                'cost_price' => 6.50,
                'stock_quantity' => 60,
                'barcode' => '7898623830023',
                'category_id' => $categories['Higiene Pessoal']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Presentes e Kits
            [
                'name' => 'Kit Presente Boticário Malbec (Perfume + Desodorante)',
                'description' => 'Kit presente masculino com perfume e desodorante Malbec',
                'price' => 179.90,
                'cost_price' => 90.00,
                'stock_quantity' => 10,
                'barcode' => '7898623830024',
                'category_id' => $categories['Presentes e Kits']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Kit Eudora Glam Beauty (Base + Batom + Rímel)',
                'description' => 'Kit de maquiagem completo para o dia a dia',
                'price' => 99.90,
                'cost_price' => 50.00,
                'stock_quantity' => 8,
                'barcode' => '7898623830025',
                'category_id' => $categories['Presentes e Kits']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Infantil
            [
                'name' => 'Shampoo Infantil Boticário Naturé Baby',
                'description' => 'Shampoo suave para bebês e crianças',
                'price' => 19.90,
                'cost_price' => 10.00,
                'stock_quantity' => 30,
                'barcode' => '7898623830026',
                'category_id' => $categories['Infantil']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Masculino
            [
                'name' => 'Gel de Barbear Boticário Men',
                'description' => 'Gel transparente para barbear com proteção',
                'price' => 24.90,
                'cost_price' => 12.50,
                'stock_quantity' => 25,
                'barcode' => '7898623830027',
                'category_id' => $categories['Masculino']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],

            // Casa e Ambiente
            [
                'name' => 'Vela Aromática Boticário Vanilla',
                'description' => 'Vela perfumada com fragrância de baunilha',
                'price' => 49.90,
                'cost_price' => 25.00,
                'stock_quantity' => 15,
                'barcode' => '7898623830028',
                'category_id' => $categories['Casa e Ambiente']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Difusor de Ambiente Eudora',
                'description' => 'Difusor com varetas para perfumar ambientes',
                'price' => 69.90,
                'cost_price' => 35.00,
                'stock_quantity' => 12,
                'barcode' => '7898623830029',
                'category_id' => $categories['Casa e Ambiente']->id_category ?? null,
                'user_id' => 2,
                'is_active' => true
            ]
        ];

        foreach ($products as $product) {
            if ($product['category_id']) { // Só cria se a categoria existe
                Product::create($product);
            }
        }
    }
}
