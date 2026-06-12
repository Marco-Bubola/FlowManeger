<?php

namespace App\Models;

use App\Traits\HasTeamScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use HasTeamScope;

    protected string $teamScopeModule = 'products';

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
        // Campos logísticos (obrigatórios para Shopee, úteis para ML)
        'weight_grams',  // Peso em gramas
        'length_cm',     // Comprimento do pacote
        'width_cm',      // Largura do pacote
        'height_cm',     // Altura do pacote
        // Variações (produto-pai + variantes)
        'parent_id',
        'is_variation_parent',
        'variation_attribute',
        'variation_value',
        'variation_sort',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'price'            => 'decimal:2',
        'price_sale'       => 'decimal:2',
        'custos_adicionais'=> 'decimal:2',
        'stock_quantity'   => 'integer',
        'warranty_months'  => 'integer',
        'category_id'      => 'integer',
        'user_id'          => 'integer',
        'weight_grams'     => 'integer',
        'length_cm'        => 'decimal:2',
        'width_cm'         => 'decimal:2',
        'height_cm'        => 'decimal:2',
        'parent_id'           => 'integer',
        'is_variation_parent' => 'boolean',
        'variation_sort'      => 'integer',
    ];

    protected $appends = ['image_url', 'all_images'];

    /**
     * status: ativo, inativo, descontinuado
     * tipo: simples, kit
     * custos_adicionais: decimal(10,2)
     */
    // Relacionamento com os itens de venda
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Galeria de imagens adicionais do produto.
     * Produto legado (sem registros aqui) continua funcionando — usa $product->image.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Retorna todas as URLs de imagem do produto (principal + galeria), sem duplicatas.
     * Compatível com produtos legados (só possuem $product->image).
     */
    public function getAllImagesAttribute(): array
    {
        $urls = [];

        // Galeria extra (nova tabela)
        foreach ($this->images as $img) {
            $urls[] = $img->url;
        }

        // Se não há galeria, usa a imagem principal como fallback
        if (empty($urls)) {
            $main = $this->getImageUrlAttribute();
            if ($main && !str_ends_with($main, 'product-placeholder.png')) {
                $urls[] = $main;
            }
        }

        return array_values(array_unique($urls));
    }

    // Relação com a categoria
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

    // ─────────────────────────────────────────────────────────────
    // VARIAÇÕES (produto-pai + variantes) — Estratégia B
    // ─────────────────────────────────────────────────────────────

    /** Variantes deste produto (quando ele é pai). */
    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_id')->orderBy('variation_sort');
    }

    /** Pai deste produto (quando ele é variante). */
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /**
     * Toda a "família" da variação (pai + variantes), independente de quem chamou.
     * Inclui o próprio produto raiz.
     */
    public function family()
    {
        $rootId = $this->parent_id ?? $this->id;
        return Product::where('user_id', $this->user_id)
            ->where(function ($q) use ($rootId) {
                $q->where('id', $rootId)->orWhere('parent_id', $rootId);
            })
            ->orderBy('is_variation_parent', 'desc') // pai primeiro
            ->orderBy('variation_sort');
    }

    public function isParent(): bool      { return (bool) $this->is_variation_parent; }
    public function isVariant(): bool     { return $this->parent_id !== null; }
    public function hasVariations(): bool { return $this->is_variation_parent || $this->parent_id !== null; }

    /** Estoque somado da família (para exibir no card do pai). */
    public function getFamilyStockAttribute(): int
    {
        if (!$this->is_variation_parent) {
            return (int) $this->stock_quantity;
        }
        return (int) $this->stock_quantity + (int) $this->variants()->sum('stock_quantity');
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
     * Relacionamento com publicações da Shopee (N:N com mapeamento de model_id)
     */
    public function shopeePublications()
    {
        return $this->belongsToMany(
            \App\Models\ShopeePublication::class,
            'shopee_publication_products',
            'product_id',
            'shopee_publication_id'
        )->withPivot(
            'shopee_model_id',
            'shopee_model_sku',
            'variation_attributes',
            'quantity',
            'unit_cost',
            'sort_order'
        )->withTimestamps();
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
