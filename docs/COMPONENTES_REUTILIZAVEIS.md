# Componentes Reutilizáveis

Este documento descreve os componentes criados para reutilização em diferentes páginas do sistema FlowManager.

## Componentes Disponíveis

### 1. PageHeader (page-header)

Componente para o cabeçalho da página com botão voltar, título, descrição e ações adicionais.

**Uso:**
```blade
<x-page-header 
    title="Título da Página"
    description="Descrição opcional da página"
    icon="nome-do-icone-bootstrap"
    back-route="nome.da.rota"
    back-url="/caminho/personalizado">
    
    <!-- Slot para ações adicionais (opcional) -->
    <div class="flex space-x-2">
        <button class="btn">Ação Extra</button>
    </div>
</x-page-header>
```

**Propriedades:**
- `title`: Título da página (obrigatório)
- `description`: Descrição da página (opcional)
- `icon`: Nome do ícone Bootstrap Icons (opcional)
- `back-route`: Nome da rota para o botão voltar (opcional)
- `back-url`: URL personalizada para o botão voltar (opcional)

### 2. SectionHeader (section-header)

Componente para cabeçalhos de seção com ícone e título.

**Uso:**
```blade
<x-section-header 
    title="Título da Seção" 
    icon="nome-do-icone" 
    icon-color="blue">
    
    <!-- Conteúdo da seção -->
    <div class="bg-white p-6 rounded-lg">
        Conteúdo aqui
    </div>
</x-section-header>
```

**Propriedades:**
- `title`: Título da seção (obrigatório)
- `icon`: Nome do ícone Bootstrap Icons (opcional)
- `icon-color`: Cor do ícone (blue, purple, green, etc.) - padrão: blue

### 3. FormInput (form-input)

Componente reutilizável para campos de formulário com validação.

**Uso:**
```blade
<!-- Input simples -->
<x-form-input 
    label="Nome"
    icon="person"
    wire-model="name"
    id="name"
    placeholder="Digite seu nome"
    :required="true"
    :error="$errors->first('name')" />

<!-- Input com prefixo (moeda) -->
<x-form-input 
    label="Preço"
    icon="currency-dollar"
    wire-model="price"
    prefix="R$"
    placeholder="0,00"
    :required="true" />

<!-- Input numérico -->
<x-form-input 
    label="Quantidade"
    type="number"
    wire-model="quantity"
    :min="1"
    :max="100"
    :step="1" />
```

**Propriedades:**
- `label`: Rótulo do campo (opcional)
- `icon`: Ícone Bootstrap Icons (opcional)
- `type`: Tipo do input (text, number, email, etc.) - padrão: text
- `name`: Nome do campo (opcional)
- `id`: ID do campo (opcional)
- `wire-model`: Propriedade Livewire (opcional)
- `placeholder`: Placeholder do campo (opcional)
- `required`: Se o campo é obrigatório (boolean) - padrão: false
- `prefix`: Prefixo (ex: R$, @) (opcional)
- `suffix`: Sufixo (opcional)
- `min`, `max`, `step`: Para inputs numéricos (opcional)
- `error`: Mensagem de erro (opcional)

### 4. ActionButtons (action-buttons)

Componente para botões de ação (cancelar, salvar) com estados de loading.

**Uso:**
```blade
<!-- Básico -->
<x-action-buttons 
    cancel-route="products.index"
    submit-text="Salvar"
    loading-text="Salvando..." />

<!-- Personalizado -->
<x-action-buttons 
    cancel-url="/voltar"
    cancel-text="Voltar"
    submit-text="Criar Item"
    submit-icon="plus-circle"
    loading-text="Criando..."
    loading-icon="arrow-clockwise"
    variant="solid"
    position="static" />
```

**Propriedades:**
- `cancel-route`: Rota para o botão cancelar (opcional)
- `cancel-url`: URL personalizada para cancelar (opcional)
- `cancel-text`: Texto do botão cancelar - padrão: "Cancelar"
- `submit-text`: Texto do botão submit - padrão: "Salvar"
- `submit-icon`: Ícone do botão submit - padrão: "check-circle"
- `loading-text`: Texto durante loading - padrão: "Salvando..."
- `loading-icon`: Ícone durante loading - padrão: "arrow-clockwise"
- `type`: Tipo do botão - padrão: "submit"
- `position`: Posição (sticky, static) - padrão: "sticky"
- `variant`: Estilo (gradient, solid) - padrão: "gradient"

### 5. ProductCard (product-card)

Componente para exibir um produto selecionável com checkbox e quantidade.

**Uso:**
```blade
<x-product-card 
    :product="$product"
    wire-model="selectedProducts.{{ $product->id }}"
    :selected="$isSelected"
    :quantity="$currentQuantity"
    :max-quantity="$product->stock_quantity"
    :show-quantity="true" />
```

**Propriedades:**
- `product`: Objeto do produto (obrigatório)
- `wire-model`: Model Livewire para controle (opcional)
- `selected`: Se está selecionado (boolean) - padrão: false
- `quantity`: Quantidade atual - padrão: 1
- `max-quantity`: Quantidade máxima (opcional)
- `show-quantity`: Mostrar campo quantidade - padrão: true
- `compact`: Modo compacto (menos informações) - padrão: false

### 6. ProductSelector (product-selector)

Componente completo para seleção de produtos com grid e resumo.

**Uso:**
```blade
<x-product-selector 
    :available-products="$availableProducts"
    :selected-products="$selectedProducts"
    wire-model="produtos"
    empty-state-title="Nenhum produto encontrado"
    empty-state-description="Adicione produtos primeiro"
    empty-state-button-text="Adicionar Produto"
    empty-state-button-route="products.create"
    :show-summary="true"
    columns="1 sm:grid-cols-2 lg:grid-cols-3" />
```

**Propriedades:**
- `available-products`: Coleção de produtos disponíveis (obrigatório)
- `selected-products`: Array de produtos selecionados - padrão: []
- `wire-model`: Model Livewire - padrão: "produtos"
- `empty-state-title`: Título quando vazio
- `empty-state-description`: Descrição quando vazio
- `empty-state-button-text`: Texto do botão quando vazio
- `empty-state-button-route`: Rota do botão quando vazio
- `show-summary`: Mostrar resumo - padrão: true
- `columns`: Classes do grid CSS - padrão: responsivo

## Exemplo de Uso Completo

```blade
<!-- Nova página usando todos os componentes -->
<div class="min-h-screen bg-neutral-50 dark:bg-neutral-900">
    <x-page-header 
        title="Nova Venda"
        description="Registre uma nova venda no sistema"
        icon="cart-plus"
        back-route="sales.index" />

    <form wire:submit="store" class="w-full">
        <div class="px-6 py-8 space-y-8">
            <!-- Informações da Venda -->
            <x-section-header title="Dados da Venda" icon="info-circle" icon-color="green">
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-input 
                            label="Cliente"
                            icon="person"
                            wire-model="client_name"
                            :required="true"
                            :error="$errors->first('client_name')" />

                        <x-form-input 
                            label="Data da Venda"
                            type="date"
                            icon="calendar"
                            wire-model="sale_date"
                            :required="true" />
                    </div>
                </div>
            </x-section-header>

            <!-- Seleção de Produtos -->
            <x-section-header title="Produtos" icon="box" icon-color="purple">
                <x-product-selector 
                    :available-products="$products"
                    :selected-products="$selectedProducts"
                    wire-model="selectedProducts" />
            </x-section-header>
        </div>

        <!-- Botões -->
        <x-action-buttons 
            cancel-route="sales.index"
            submit-text="Finalizar Venda"
            loading-text="Processando..." />
    </form>
</div>
```

## Vantagens dos Componentes

1. **Reutilização**: Mesma funcionalidade em várias páginas
2. **Consistência**: Interface uniforme em todo o sistema
3. **Manutenibilidade**: Alterações centralizadas
4. **Produtividade**: Desenvolvimento mais rápido
5. **Responsividade**: Todos os componentes são responsivos
6. **Dark Mode**: Suporte completo ao modo escuro
7. **Acessibilidade**: Labels e estrutura semântica adequadas
8. **Livewire**: Integração nativa com Livewire para reatividade
