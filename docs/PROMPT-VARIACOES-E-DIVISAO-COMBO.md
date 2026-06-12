# 🧬 PROMPT — Sistema de Variações (Produto-Pai) + Divisão de Combo/Kit

> Documento operacional para implementar **variações reais (Opção A: produto-pai + variantes)** no FlowManager **sem quebrar nenhum produto já existente**, e uma nova funcionalidade de **dividir um combo/kit em produtos separados** (criados manualmente ou vinculados como variação de um produto existente).
> Stack: Laravel 12 · Livewire 3 · MySQL. Tabela central: `products`. Kits usam `produto_componentes`.

---

## 🎯 Objetivos

1. **Variações reais**: um produto "pai" agrupa N variantes (ex: Aroma → Lavanda, Rosas, Baunilha), cada uma com seu próprio SKU, estoque e preço.
2. **Migração sem quebra**: todo produto existente continua funcionando exatamente como hoje (vira um produto "standalone" sem pai). A adoção de variações é **opt-in**.
3. **Dividir combo/kit**: pegar um produto lançado como combo (ex: vem 3 itens) e **separá-lo em 3 produtos distintos**, cada um podendo ser:
   - cadastrado como **produto novo**, OU
   - vinculado como **variação de um produto já existente**.

---

## 🧱 FASE 1 — Modelo de dados (migração NÃO destrutiva)

### 1.1 Princípio de não-quebra
- **Nenhuma coluna existente é alterada ou removida.**
- **Nenhum dado é movido automaticamente.**
- Adicionamos apenas **colunas novas nuláveis** com defaults seguros.
- Produto existente → `parent_id = NULL`, `is_variation_parent = false` → comporta-se como hoje (standalone). Zero efeito colateral.

### 1.2 Migration — adicionar colunas a `products`
```php
// database/migrations/XXXX_add_variation_fields_to_products.php
public function up(): void
{
    Schema::table('products', function (Blueprint $t) {
        // Pai da variação (NULL = produto standalone, como todos os atuais)
        $t->unsignedBigInteger('parent_id')->nullable()->after('id');
        // Marca o produto que É um pai/guarda-chuva de variações
        $t->boolean('is_variation_parent')->default(false)->after('parent_id');
        // Atributo e valor da variação (ex: "Aroma" / "Lavanda", "Tamanho" / "100ml")
        $t->string('variation_attribute', 60)->nullable()->after('is_variation_parent');
        $t->string('variation_value', 120)->nullable()->after('variation_attribute');
        // Ordem de exibição das variantes dentro do pai
        $t->unsignedInteger('variation_sort')->default(0)->after('variation_value');

        $t->foreign('parent_id')->references('id')->on('products')->nullOnDelete();
        $t->index(['parent_id']);
        $t->index(['user_id', 'is_variation_parent']);
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $t) {
        $t->dropForeign(['parent_id']);
        $t->dropColumn(['parent_id','is_variation_parent','variation_attribute','variation_value','variation_sort']);
    });
}
```

> **Importante**: rodar `php artisan migrate` aqui é seguro — só adiciona colunas. Nenhum produto muda de comportamento.

### 1.3 Conceito do "pai"
Há **duas estratégias** para o pai — escolha **B** (mais simples, recomendada):

- **Estratégia A (pai virtual)**: o pai é um registro `products` com `is_variation_parent = true`, **sem estoque/preço próprios** (é só um agrupador). As variantes apontam `parent_id` para ele. Mais limpo conceitualmente, mas cria 1 linha "fantasma".
- **Estratégia B (pai = a 1ª variante) ✅ RECOMENDADA**: um produto comum vira "pai" ao receber variantes. Ele continua sendo um produto vendável **e** agrupa os outros. `is_variation_parent = true` nele; as variantes têm `parent_id = id_dele`. Não cria linha fantasma e não quebra nada (produtos sem variantes ficam `is_variation_parent = false`).

**Usar Estratégia B.** Um produto standalone existente pode "ganhar" variantes depois, virando pai, sem migração de dados.

---

## 🧩 FASE 2 — Model e relações

### 2.1 `Product` — relações de variação
```php
// app/Models/Product.php
protected $fillable = [ /* ...existentes... */, 'parent_id', 'is_variation_parent', 'variation_attribute', 'variation_value', 'variation_sort' ];

protected $casts = [ /* ...existentes... */, 'is_variation_parent' => 'boolean' ];

// Variantes deste produto (quando é pai)
public function variants()
{
    return $this->hasMany(Product::class, 'parent_id')->orderBy('variation_sort');
}

// Pai deste produto (quando é variante)
public function parent()
{
    return $this->belongsTo(Product::class, 'parent_id');
}

// Todas as variantes da "família" (inclui o próprio pai), independente de quem chamou
public function family()
{
    $rootId = $this->parent_id ?? $this->id;
    return Product::where('user_id', $this->user_id)
        ->where(fn($q) => $q->where('id', $rootId)->orWhere('parent_id', $rootId))
        ->orderBy('variation_sort');
}

public function isParent(): bool   { return (bool) $this->is_variation_parent; }
public function isVariant(): bool  { return $this->parent_id !== null; }
public function hasVariations(): bool { return $this->is_variation_parent || $this->parent_id !== null; }
```

### 2.2 Helper para promover/anexar variação (idempotente, seguro)
Criar `app/Services/Products/VariationService.php`:
```php
class VariationService
{
    /**
     * Anexa $child como variação de $parent.
     * - Promove $parent a pai (is_variation_parent = true) se ainda não for.
     * - Define o atributo/valor da variação no filho.
     * - NÃO mexe em estoque/preço (cada variante é independente).
     */
    public function attach(Product $parent, Product $child, string $attribute, string $value, ?int $sort = null): void
    {
        // proteções
        if ($parent->id === $child->id) throw new \InvalidArgumentException('Pai e filho iguais.');
        if ($parent->parent_id) throw new \InvalidArgumentException('O pai não pode ser ele mesmo uma variante.'); // 1 nível só
        if ($child->is_variation_parent && $child->variants()->exists()) throw new \InvalidArgumentException('O filho já é pai de outras variações.');

        DB::transaction(function () use ($parent, $child, $attribute, $value, $sort) {
            if (!$parent->is_variation_parent) {
                $parent->update(['is_variation_parent' => true,
                                 'variation_attribute' => $attribute,
                                 'variation_value' => $parent->variation_value ?? 'Padrão']);
            }
            $child->update([
                'parent_id' => $parent->id,
                'is_variation_parent' => false,
                'variation_attribute' => $attribute,
                'variation_value' => $value,
                'variation_sort' => $sort ?? ($parent->variants()->max('variation_sort') + 1),
            ]);
        });
    }

    /** Desanexa uma variante (volta a ser standalone). */
    public function detach(Product $variant): void
    {
        DB::transaction(function () use ($variant) {
            $parent = $variant->parent;
            $variant->update(['parent_id' => null, 'variation_value' => null, 'variation_sort' => 0]);
            // Se o pai ficou sem variantes, deixa de ser pai
            if ($parent && !$parent->variants()->exists()) {
                $parent->update(['is_variation_parent' => false]);
            }
        });
    }
}
```

> **Regra de 1 nível**: variantes não têm sub-variantes (mantém simples e evita loops). Validar sempre.

---

## 📋 FASE 3 — Atualizar a lógica atual (ShowProduct + Index)

### 3.1 `ShowProduct` — trocar "mesmo código" por `parent_id`
Hoje ([ShowProduct.php:37](app/Livewire/Products/ShowProduct.php#L37)) agrupa por `product_code`. Mudar para a família via `parent_id`, **com fallback** para o comportamento antigo (compatibilidade):
```php
public function loadProductData()
{
    $product = Product::where('product_code', $this->productCode)
        ->where('user_id', Auth::id())->firstOrFail();

    // Nova lógica: família por parent_id
    if ($product->hasVariations()) {
        $this->products = $product->family()->get();
        $this->mainProduct = $product->parent_id ? $product->parent : $product;
    } else {
        // Fallback compatível: produtos antigos que compartilham product_code
        $this->products = Product::where('product_code', $this->productCode)
            ->where('user_id', Auth::id())->get();
        $this->mainProduct = $this->products->firstWhere('status','ativo') ?? $this->products->first();
    }
    $this->category = Category::find($this->mainProduct->category_id);
}
```

### 3.2 Index — agrupar variações sob o pai (opcional, recomendado)
No `ProductsIndex`, ao listar, **esconder as variantes** (mostrar só pais e standalone):
```php
$query->where(function ($q) {
    $q->whereNull('parent_id')        // standalone + pais
      ->orWhere('parent_id', '=', DB::raw('id')); // (nunca, mas explícito)
});
// = simplesmente: ->whereNull('parent_id')
```
No card, se `is_variation_parent`, mostrar badge **"N variações"** (somando estoque das variantes). Clicar abre a show com todas as variantes.

---

## ✂️ FASE 4 — Dividir Combo/Kit em produtos

### 4.1 Conceito
Um produto **combo/kit** (ex: "Kit Presente Blue" com 3 itens) pode ser **dividido**: para cada componente, o usuário decide o destino:
1. **Criar produto novo** (com nome/código/preço/estoque editáveis, herdando dados do componente).
2. **Vincular a produto existente** (apenas referencia, sem criar).
3. **Criar/vincular como variação** de um produto existente (usa o VariationService).

Após dividir, opcionalmente: **manter o kit** (continua vendável) ou **converter o kit em standalone/arquivar**.

### 4.2 Fonte dos dados
Os componentes já existem em `produto_componentes` (kit_produto_id, componente_produto_id, quantidade, preços). Cada componente **já é um Product** (o `componente_produto_id`). Então "dividir" é principalmente:
- Garantir que cada componente exista como produto autônomo (já existe), **e/ou**
- Criar novos produtos a partir das quantidades (ex: combo com "3x Sabonete" → criar 1 produto "Sabonete" com estoque ajustado), **e/ou**
- Agrupar como variações.

> ⚠️ Cuidado com **estoque**: ao dividir, decidir se o estoque do kit/combo é **distribuído** para os novos produtos ou se eles começam zerados. Tornar isso uma escolha explícita na UI.

### 4.3 Migration — registrar divisões (auditoria)
```php
Schema::create('product_splits', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->constrained();
    $t->foreignId('source_product_id')->constrained('products'); // o combo/kit dividido
    $t->enum('source_action', ['kept','archived','converted'])->default('kept');
    $t->timestamps();
});
Schema::create('product_split_items', function (Blueprint $t) {
    $t->id();
    $t->foreignId('split_id')->constrained('product_splits')->cascadeOnDelete();
    $t->foreignId('result_product_id')->constrained('products'); // produto gerado/vinculado
    $t->enum('mode', ['new','linked','variation']);
    $t->foreignId('variation_parent_id')->nullable()->constrained('products');
    $t->integer('quantity')->default(1);
    $t->integer('stock_assigned')->default(0);
    $t->timestamps();
});
```

### 4.4 Service de divisão
`app/Services/Products/ComboSplitService.php`:
```php
class ComboSplitService
{
    public function __construct(private VariationService $variations) {}

    /**
     * @param Product $source  combo/kit a dividir
     * @param array   $plan    [
     *   'source_action' => 'kept'|'archived'|'converted',
     *   'distribute_stock' => bool,   // distribui estoque do kit p/ os novos
     *   'items' => [
     *     ['component_id'=>int, 'quantity'=>int,
     *      'mode'=>'new'|'linked'|'variation',
     *      // new:
     *      'name'=>?, 'product_code'=>?, 'price'=>?, 'price_sale'=>?, 'stock'=>?, 'category_id'=>?,
     *      // linked: 'target_product_id'=>int
     *      // variation: 'parent_id'=>int, 'attribute'=>string, 'value'=>string,
     *     ], ...
     *   ]
     * ]
     */
    public function split(Product $source, array $plan): array
    {
        return DB::transaction(function () use ($source, $plan) {
            $splitLog = ProductSplit::create([
                'user_id' => $source->user_id,
                'source_product_id' => $source->id,
                'source_action' => $plan['source_action'] ?? 'kept',
            ]);

            $results = [];
            foreach ($plan['items'] as $item) {
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $mode = $item['mode'];
                $product = null;

                if ($mode === 'new') {
                    $product = Product::create([
                        'user_id' => $source->user_id,
                        'name' => $item['name'],
                        'product_code' => $item['product_code'] ?: $this->genCode(),
                        'price' => $item['price'] ?? 0,
                        'price_sale' => $item['price_sale'] ?? 0,
                        'stock_quantity' => $item['stock'] ?? 0,
                        'category_id' => $item['category_id'] ?? $source->category_id,
                        'tipo' => 'simples',
                        'status' => 'ativo',
                        'image' => $item['image'] ?? 'product-placeholder.png',
                    ]);
                } elseif ($mode === 'linked') {
                    $product = Product::findOrFail($item['target_product_id']);
                } elseif ($mode === 'variation') {
                    // pode ser produto novo virando variação, OU produto existente
                    $child = isset($item['target_product_id'])
                        ? Product::findOrFail($item['target_product_id'])
                        : Product::create([ /* mesmos campos do 'new' */ ]);
                    $parent = Product::findOrFail($item['parent_id']);
                    $this->variations->attach($parent, $child, $item['attribute'], $item['value']);
                    $product = $child;
                }

                // Distribuição de estoque (opcional)
                $stockAssigned = 0;
                if (!empty($plan['distribute_stock'])) {
                    $stockAssigned = $qty; // ou regra própria
                    $product->increment('stock_quantity', $stockAssigned);
                }

                ProductSplitItem::create([
                    'split_id' => $splitLog->id,
                    'result_product_id' => $product->id,
                    'mode' => $mode,
                    'variation_parent_id' => $item['parent_id'] ?? null,
                    'quantity' => $qty,
                    'stock_assigned' => $stockAssigned,
                ]);
                $results[] = $product;
            }

            // Ação no produto de origem
            match ($plan['source_action'] ?? 'kept') {
                'archived'  => $source->update(['status' => 'inativo']),
                'converted' => $source->update(['tipo' => 'simples']), // deixa de ser kit
                default     => null, // 'kept'
            };

            return $results;
        });
    }

    private function genCode(): string { return 'P-' . strtoupper(substr(uniqid(), -7)); }
}
```

---

## 🖥️ FASE 5 — Interface (UI)

### 5.1 Editar Produto — aba/seção "Variações"
- Em `edit-product`, adicionar seção **"Variações"**:
  - Campo **Atributo** (ex: "Aroma", "Tamanho", "Cor").
  - Lista das variantes atuais (cards no estilo da index, editáveis).
  - Botão **"+ Adicionar variação"** → modal com 2 opções:
    1. **Criar nova variante** (formulário: valor do atributo + nome + código + preços + estoque).
    2. **Vincular produto existente** (busca → seleciona → vira variante).
  - Botão **"Desvincular"** em cada variante (volta a standalone).

### 5.2 Página/Modal "Dividir Combo"
- Botão **"Dividir"** no card/ações de produtos do tipo `kit`/combo.
- Modal com a lista dos componentes; para cada um, um seletor de destino:
  - 🆕 **Novo produto** (campos editáveis pré-preenchidos com os dados do componente).
  - 🔗 **Vincular a existente** (busca).
  - 🧬 **Como variação** (escolhe o produto-pai + atributo + valor).
- Opções globais: **distribuir estoque do combo** (sim/não), **ação no combo** (manter / arquivar / converter em simples).
- Preview do resultado antes de confirmar. Confirmação chama `ComboSplitService::split`.

### 5.3 Show / Index
- Show: variantes já aparecem como cards da index (feito).
- Index: pais mostram badge "N variações" + estoque somado; variantes ficam ocultas (agrupadas).

---

## 🔁 FASE 6 — Migração opcional dos "mesmos código" atuais

> Só rodar **se o usuário quiser** agrupar os produtos antigos que repetem `product_code`. **NÃO automático.**

Comando artisan **com dry-run e confirmação**:
```bash
php artisan products:group-variations --dry-run   # mostra o que faria
php artisan products:group-variations              # aplica (pede confirmação)
```
Lógica: para cada `(user_id, product_code)` com >1 produto:
- Elege o pai (o mais antigo ou o ativo).
- `attach()` os demais como variantes (atributo padrão "Variação", valor = nome ou um sufixo).
- Loga tudo; permite `--rollback` via `product_splits`/auditoria.

---

## ⚠️ FASE 7 — Casos de borda e proteções

- **Não quebrar vendas existentes**: `sale_items.product_id` continua apontando para o produto real (variante ou standalone). Nada muda.
- **Kits que usam o produto**: se um produto vira variante, os `produto_componentes` que o referenciam continuam válidos (mesmo `id`).
- **Exclusão**: ao excluir um pai, `parent_id` das variantes vira NULL (FK `nullOnDelete`) → viram standalone (não somem).
- **1 nível só**: bloquear variante-de-variante.
- **Código único por variante**: cada variante tem seu próprio `product_code` (ou gerar automático).
- **Estoque**: sempre por variante (o pai não centraliza estoque na Estratégia B; some-se para exibição).
- **Relatórios/Dashboards**: variantes contam como produtos normais — sem efeito colateral.

---

## ✅ Checklist de implementação
- [ ] Migration de colunas em `products` (não destrutiva) + `migrate`
- [ ] Relações no Model (`variants`, `parent`, `family`, helpers)
- [ ] `VariationService` (attach/detach, 1 nível, transações)
- [ ] `ShowProduct` usando `parent_id` com fallback por `product_code`
- [ ] Index: ocultar variantes + badge "N variações" no pai
- [ ] Seção "Variações" no edit-product (criar/vincular/desvincular)
- [ ] `product_splits` + `product_split_items` (auditoria)
- [ ] `ComboSplitService` (new/linked/variation + distribuição de estoque)
- [ ] Modal "Dividir Combo" (preview + confirmação)
- [ ] Comando opcional `products:group-variations` (dry-run + rollback)
- [ ] Feature tests: attach/detach, split (3 modos), exclusão de pai, fallback do show

---

## 🗺️ Ordem sugerida
1. **FASE 1 + 2** (schema + model) — base, 100% sem quebra.
2. **FASE 3** (show/index usando parent_id com fallback) — variações já funcionam.
3. **FASE 5.1** (UI de variações no edit) — criar/vincular variações na mão.
4. **FASE 4 + 5.2** (divisão de combo) — o recurso novo.
5. **FASE 6** (migração opcional dos códigos antigos) — quando quiser consolidar.
6. **FASE 7** (testes + bordas).

**Garantia de não-quebra**: as Fases 1–2 só adicionam colunas nuláveis; tudo que existe hoje continua igual (todos os produtos ficam `parent_id = NULL`). Variações e divisão são 100% opt-in.
