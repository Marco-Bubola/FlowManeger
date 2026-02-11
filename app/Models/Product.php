<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_sale',
        'stock_quantity',
        'category_id',
        'user_id',
        'product_code',
        'image',
        'status', // ativo, inativo, descontinuado
        'tipo', // simples, kit
        'custos_adicionais', // decimal(10,2)
        'descricao_custos_adicionais', // texto opcional
        'barcode', // EAN/GTIN para integração Mercado Livre
        'brand', // Marca do produto
        'model', // Modelo do produto
        'warranty_months', // Meses de garantia
        'condition', // new, used
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'price_sale' => 'decimal:2',
        'custos_adicionais' => 'decimal:2',
        'stock_quantity' => 'integer',
        'warranty_months' => 'integer',
        'category_id' => 'integer',
        'user_id' => 'integer',
    ];

    protected $appends = ['image_url'];

    /**
     * status: ativo, inativo, descontinuado
     * tipo: simples, kit
     * custos_adicionais: decimal(10,2)
     */
    // Relacionamento com os itens de venda
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    } // Relação com a categoria
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id_category');  // A chave estrangeira é 'category_id' e a chave primária da categoria é 'id_category'
    }
    // Definir o relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Se for kit, retorna os componentes
    public function componentes()
    {
        return $this->hasMany(\App\Models\ProdutoComponente::class, 'kit_produto_id');
    }

    /**
     * Relacionamento com anúncio do Mercado Livre (sistema legado 1:1)
     */
    public function mercadoLivreProduct()
    {
        return $this->hasOne(\App\Models\MercadoLivreProduct::class, 'product_id');
    }

    /**
     * Relacionamento com publicações do Mercado Livre (novo sistema N:N com suporte a kits)
     */
    public function mlPublications()
    {
        return $this->belongsToMany(
            \App\Models\MlPublication::class,
            'ml_publication_products',
            'product_id',
            'ml_publication_id'
        )->withPivot('quantity', 'unit_cost', 'sort_order')
          ->withTimestamps();
    }

    /**
     * Verifica se o produto está em alguma publicação ativa no ML
     */
    public function hasActivePublications(): bool
    {
        return $this->mlPublications()
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Retorna todas as publicações ativas que contêm este produto
     */
    public function getActivePublications()
    {
        return $this->mlPublications()
            ->where('status', 'active')
            ->with('products')
            ->get();
    }

    /**
     * Retorna a URL completa da imagem do produto
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image || $this->image === 'product-placeholder.png') {
            return asset('storage/products/product-placeholder.png');
        }
        
        return asset('storage/products/' . $this->image);
    }

    /**
     * Verifica se o produto está pronto para ser publicado no Mercado Livre
     * Retorna array com ['ready' => bool, 'errors' => array]
     */
    public function isReadyForMercadoLivre(): array
    {
        $errors = [];

        // Validações obrigatórias
        if (empty($this->name) || strlen($this->name) < 3) {
            $errors[] = 'Título muito curto (mínimo 3 caracteres)';
        }

        if ($this->price <= 0) {
            $errors[] = 'Preço deve ser maior que R$ 0,00';
        }

        if ($this->stock_quantity <= 0) {
            $errors[] = 'Produto sem estoque';
        }

        if (empty($this->image) || $this->image === 'product-placeholder.png') {
            $errors[] = 'Imagem não cadastrada';
        }

        if (empty($this->condition)) {
            $errors[] = 'Condição (novo/usado) não informada';
        }

        // ⚠️ OBRIGATÓRIO: Código de barras para ML
        if (empty($this->barcode)) {
            $errors[] = 'Código de barras (EAN) obrigatório';
        }

        if (empty($this->category_id)) {
            $errors[] = 'Categoria não selecionada';
        }

        return [
            'ready' => empty($errors),
            'errors' => $errors
        ];
    }
}
