<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class DetailedCosmeticCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorias específicas e detalhadas para produtos de beleza Eudora e Boticário
        $categories = [
            // Perfumaria detalhada
            [
                'name' => 'Perfume Feminino',
                'desc_category' => 'Perfumes e fragrâncias femininas',
                'hexcolor_category' => '#E91E63',
                'icone' => 'fas fa-venus',
                'descricao_detalhada' => 'Perfumes femininos das marcas Eudora e Boticário - fragrâncias florais, frutadas, amadeiradas',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Perfume Masculino',
                'desc_category' => 'Perfumes e fragrâncias masculinas',
                'hexcolor_category' => '#2196F3',
                'icone' => 'fas fa-mars',
                'descricao_detalhada' => 'Perfumes masculinos das marcas Eudora e Boticário - fragrâncias amadeiradas, cítricas, aromáticas',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Colônia',
                'desc_category' => 'Colônias e águas de cheiro',
                'hexcolor_category' => '#9C27B0',
                'icone' => 'fas fa-tint',
                'descricao_detalhada' => 'Colônias, águas de cheiro e fragrâncias leves para uso diário',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Body Spray',
                'desc_category' => 'Desodorantes colônias e body sprays',
                'hexcolor_category' => '#00BCD4',
                'icone' => 'fas fa-spray-can',
                'descricao_detalhada' => 'Body sprays, desodorantes colônias e fragrâncias corporais refrescantes',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],

            // Cuidados corporais específicos
            [
                'name' => 'Creme Hidratante',
                'desc_category' => 'Cremes hidratantes corporais',
                'hexcolor_category' => '#4CAF50',
                'icone' => 'fas fa-hand-holding-water',
                'descricao_detalhada' => 'Cremes hidratantes para corpo, pernas, mãos e áreas específicas',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Loção Corporal',
                'desc_category' => 'Loções e hidratantes líquidos',
                'hexcolor_category' => '#8BC34A',
                'icone' => 'fas fa-pump-soap',
                'descricao_detalhada' => 'Loções corporais hidratantes, óleos corporais e produtos líquidos para hidratação',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Protetor Solar',
                'desc_category' => 'Protetores solares faciais e corporais',
                'hexcolor_category' => '#FF9800',
                'icone' => 'fas fa-sun',
                'descricao_detalhada' => 'Protetores solares com diferentes FPS para rosto e corpo',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],

            // Maquiagem específica
            [
                'name' => 'Base e Corretivo',
                'desc_category' => 'Bases líquidas, em pó e corretivos',
                'hexcolor_category' => '#F44336',
                'icone' => 'fas fa-palette',
                'descricao_detalhada' => 'Bases faciais, corretivos, primers e produtos para uniformizar a pele',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Batom e Gloss',
                'desc_category' => 'Batons líquidos, cremosos e gloss',
                'hexcolor_category' => '#E91E63',
                'icone' => 'fas fa-kiss',
                'descricao_detalhada' => 'Batons líquidos, cremosos, matte, gloss labial e produtos para os lábios',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Rímel e Olhos',
                'desc_category' => 'Rímeis, sombras e produtos para olhos',
                'hexcolor_category' => '#673AB7',
                'icone' => 'fas fa-eye',
                'descricao_detalhada' => 'Rímeis, sombras, delineadores, lápis e produtos para maquiagem dos olhos',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Blush e Iluminador',
                'desc_category' => 'Blush, bronzer e iluminadores',
                'hexcolor_category' => '#FF5722',
                'icone' => 'fas fa-gem',
                'descricao_detalhada' => 'Blush, bronzer, iluminadores e produtos para dar cor e brilho ao rosto',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],

            // Cabelos específicos
            [
                'name' => 'Shampoo',
                'desc_category' => 'Shampoos para todos os tipos de cabelo',
                'hexcolor_category' => '#2196F3',
                'icone' => 'fas fa-shower',
                'descricao_detalhada' => 'Shampoos para cabelos oleosos, secos, mistos, com caspa e tratamentos específicos',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Condicionador',
                'desc_category' => 'Condicionadores e cremes para pentear',
                'hexcolor_category' => '#009688',
                'icone' => 'fas fa-hand-paper',
                'descricao_detalhada' => 'Condicionadores, cremes para pentear e produtos leave-in para hidratação capilar',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Máscara Capilar',
                'desc_category' => 'Máscaras de tratamento capilar',
                'hexcolor_category' => '#795548',
                'icone' => 'fas fa-spa',
                'descricao_detalhada' => 'Máscaras capilares intensivas para hidratação, nutrição e reconstrução dos fios',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Óleo Capilar',
                'desc_category' => 'Óleos e séruns para cabelo',
                'hexcolor_category' => '#FFC107',
                'icone' => 'fas fa-oil-can',
                'descricao_detalhada' => 'Óleos capilares, séruns e produtos para dar brilho e nutrição aos cabelos',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],

            // Higiene específica
            [
                'name' => 'Sabonete Líquido',
                'desc_category' => 'Sabonetes líquidos para corpo e mãos',
                'hexcolor_category' => '#607D8B',
                'icone' => 'fas fa-pump-soap',
                'descricao_detalhada' => 'Sabonetes líquidos hidratantes, antibacterianos e perfumados para corpo e mãos',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Sabonete em Barra',
                'desc_category' => 'Sabonetes sólidos e em barra',
                'hexcolor_category' => '#9E9E9E',
                'icone' => 'fas fa-soap',
                'descricao_detalhada' => 'Sabonetes em barra hidratantes, esfoliantes e com diferentes fragrâncias',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Desodorante Roll-on',
                'desc_category' => 'Desodorantes roll-on antitranspirantes',
                'hexcolor_category' => '#3F51B5',
                'icone' => 'fas fa-circle',
                'descricao_detalhada' => 'Desodorantes roll-on com proteção antitranspirante para uso diário',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Desodorante Aerosol',
                'desc_category' => 'Desodorantes em spray aerosol',
                'hexcolor_category' => '#00BCD4',
                'icone' => 'fas fa-spray-can',
                'descricao_detalhada' => 'Desodorantes aerosol com secagem rápida e proteção prolongada',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],

            // Produtos especiais
            [
                'name' => 'Esmalte',
                'desc_category' => 'Esmaltes e produtos para unhas',
                'hexcolor_category' => '#E91E63',
                'icone' => 'fas fa-hand',
                'descricao_detalhada' => 'Esmaltes coloridos, base, top coat e produtos para cuidados com as unhas',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Creme Facial',
                'desc_category' => 'Cremes faciais e tratamentos',
                'hexcolor_category' => '#8BC34A',
                'icone' => 'fas fa-smile',
                'descricao_detalhada' => 'Cremes faciais hidratantes, anti-idade, para acne e tratamentos específicos',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
