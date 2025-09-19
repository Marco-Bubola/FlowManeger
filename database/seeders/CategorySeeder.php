<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorias para produtos Eudora e Boticário
        $categories = [
            // Categorias principais de cosméticos
            [
                'name' => 'Perfumaria',
                'desc_category' => 'Perfumes, colônias e fragrâncias',
                'hexcolor_category' => '#9C27B0',
                'icone' => 'fas fa-spray-can',
                'descricao_detalhada' => 'Categoria para todos os produtos de perfumaria incluindo perfumes, colônias, águas de cheiro e produtos aromáticos das marcas Eudora e Boticário',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Maquiagem',
                'desc_category' => 'Produtos de maquiagem e beleza',
                'hexcolor_category' => '#E91E63',
                'icone' => 'fas fa-palette',
                'descricao_detalhada' => 'Base, batom, rímel, sombra, blush, corretivo e demais produtos de maquiagem das marcas Eudora e Boticário',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Cuidados com a Pele',
                'desc_category' => 'Produtos para cuidados faciais e corporais',
                'hexcolor_category' => '#4CAF50',
                'icone' => 'fas fa-leaf',
                'descricao_detalhada' => 'Cremes hidratantes, protetores solares, sabonetes, loções e produtos para cuidados com a pele',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Cuidados com o Cabelo',
                'desc_category' => 'Produtos capilares',
                'hexcolor_category' => '#FF9800',
                'icone' => 'fas fa-cut',
                'descricao_detalhada' => 'Shampoos, condicionadores, máscaras, óleos e tratamentos capilares das marcas Eudora e Boticário',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Higiene Pessoal',
                'desc_category' => 'Produtos de higiene e limpeza pessoal',
                'hexcolor_category' => '#2196F3',
                'icone' => 'fas fa-soap',
                'descricao_detalhada' => 'Desodorantes, sabonetes, géis de banho e produtos de higiene pessoal',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Presentes e Kits',
                'desc_category' => 'Kits e produtos para presentear',
                'hexcolor_category' => '#F44336',
                'icone' => 'fas fa-gift',
                'descricao_detalhada' => 'Kits especiais, produtos para presente e embalagens promocionais das marcas Eudora e Boticário',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Infantil',
                'desc_category' => 'Produtos infantis',
                'hexcolor_category' => '#FFC107',
                'icone' => 'fas fa-baby',
                'descricao_detalhada' => 'Produtos específicos para crianças e bebês das marcas Eudora e Boticário',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Masculino',
                'desc_category' => 'Produtos masculinos',
                'hexcolor_category' => '#607D8B',
                'icone' => 'fas fa-male',
                'descricao_detalhada' => 'Linha completa de produtos para o público masculino incluindo perfumes, desodorantes e cuidados pessoais',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Casa e Ambiente',
                'desc_category' => 'Produtos para casa e ambientes',
                'hexcolor_category' => '#795548',
                'icone' => 'fas fa-home',
                'descricao_detalhada' => 'Velas, aromatizadores de ambiente, difusores e produtos para perfumar e decorar a casa',
                'tipo' => 'receita',
                'is_active' => 1,
                'user_id' => 2,
                'type' => 'product'
            ],
            [
                'name' => 'Nutrição e Bem-estar',
                'desc_category' => 'Suplementos e produtos para bem-estar',
                'hexcolor_category' => '#8BC34A',
                'icone' => 'fas fa-heart',
                'descricao_detalhada' => 'Suplementos, vitaminas e produtos voltados para nutrição e bem-estar corporal',
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
