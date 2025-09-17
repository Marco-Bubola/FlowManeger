# Componentes Reutilizáveis - Formulários e UI

## Componentes Criados

### 1. Stepper (`<x-stepper>`)
Componente para exibir indicadores de progresso em várias etapas.

**Localização:** `resources/views/components/stepper.blade.php`

**Props:**
- `steps` (array, obrigatório): Array de etapas com título, descrição e ícone
- `currentStep` (int, padrão: 1): Etapa atual
- `showStepNumbers` (bool, padrão: true): Mostrar números das etapas

**Exemplo de uso:**
```blade
<x-stepper 
    :steps="[
        ['title' => 'Informações', 'description' => 'Dados básicos', 'icon' => 'bi-info-circle'],
        ['title' => 'Imagem', 'description' => 'Upload de foto', 'icon' => 'bi-image']
    ]"
    :current-step="$currentStep"
    :show-step-numbers="false"
/>
```

### 2. Botões de Ação (`<x-action-buttons-new>`)
Componente para botões de formulário (Voltar, Próximo, Salvar, Cancelar).

**Localização:** `resources/views/components/action-buttons-new.blade.php`

**Props:**
- `showBack` (bool, padrão: false): Mostrar botão voltar
- `showNext` (bool, padrão: false): Mostrar botão próximo
- `showSave` (bool, padrão: false): Mostrar botão salvar
- `showCancel` (bool, padrão: false): Mostrar botão cancelar
- `backAction` (string): Ação Livewire para voltar
- `nextAction` (string): Ação Livewire para próximo
- `saveAction` (string): Ação Livewire para salvar
- `cancelRoute` (string): Rota para cancelar
- `backText` (string, padrão: 'Voltar'): Texto do botão voltar
- `nextText` (string, padrão: 'Próxima Etapa'): Texto do botão próximo
- `saveText` (string, padrão: 'Salvar'): Texto do botão salvar
- `cancelText` (string, padrão: 'Cancelar'): Texto do botão cancelar
- `loading` (bool, padrão: false): Estado de carregamento
- `loadingText` (string, padrão: 'Processando...'): Texto durante carregamento

**Exemplo de uso:**
```blade
<x-action-buttons-new
    :show-back="$currentStep > 1"
    :show-next="$currentStep < 2"
    :show-save="$currentStep == 2"
    :show-cancel="true"
    back-action="previousStep"
    :cancel-route="route('products.index')"
    save-text="Criar Produto"
/>
```

### 3. Upload de Imagem (`<x-image-upload>`)
Componente para upload de imagens com drag & drop.

**Localização:** `resources/views/components/image-upload.blade.php`

**Props:**
- `name` (string, padrão: 'image'): Nome do campo
- `id` (string, padrão: 'image'): ID do campo
- `wireModel` (string, padrão: 'image'): Modelo Livewire
- `acceptedTypes` (string, padrão: 'image/*'): Tipos aceitos
- `maxSize` (string, padrão: '2MB'): Tamanho máximo
- `supportedFormats` (string, padrão: 'PNG, JPG, JPEG'): Formatos suportados
- `title` (string, padrão: 'Adicionar Imagem'): Título
- `description` (string): Descrição
- `existingImage`: Imagem existente
- `width` (string, padrão: 'w-full'): Largura
- `height` (string, padrão: 'h-140'): Altura
- `showPreview` (bool, padrão: true): Mostrar preview

**Exemplo de uso:**
```blade
<x-image-upload 
    name="product_image"
    id="product_image"
    wire-model="productImage"
    title="Foto do Produto"
    :existing-image="$productImage"
/>
```

### 4. Campo de Quantidade (`<x-quantity-input>`)
Componente para entrada de quantidade com botões +/-.

**Localização:** `resources/views/components/quantity-input.blade.php`

**Props:**
- `name` (string, padrão: 'quantity'): Nome do campo
- `id` (string, padrão: 'quantity'): ID do campo
- `wireModel` (string, padrão: 'quantity'): Modelo Livewire
- `min` (int, padrão: 0): Valor mínimo
- `max` (int): Valor máximo
- `value` (int, padrão: 0): Valor inicial
- `placeholder` (string, padrão: '0'): Placeholder
- `label` (string, padrão: 'Quantidade'): Label
- `icon` (string, padrão: 'bi-stack'): Ícone
- `iconColor` (string, padrão: 'cyan'): Cor do ícone
- `required` (bool, padrão: false): Campo obrigatório
- `disabled` (bool, padrão: false): Campo desabilitado
- `width` (string, padrão: 'max-w-[10rem]'): Largura

**Exemplo de uso:**
```blade
<x-quantity-input 
    name="stock"
    id="stock"
    wire-model="stockQuantity"
    label="Estoque"
    icon="bi-boxes"
    icon-color="blue"
    :required="true"
    :min="0"
    :max="1000"
/>
```

### 5. Campo de Moeda (`<x-currency-input>`)
Componente para entrada de valores monetários com máscara.

**Localização:** `resources/views/components/currency-input.blade.php`

**Props:**
- `name` (string, padrão: 'price'): Nome do campo
- `id` (string, padrão: 'price'): ID do campo
- `wireModel` (string, padrão: 'price'): Modelo Livewire
- `label` (string, padrão: 'Valor'): Label
- `placeholder` (string, padrão: '0,00'): Placeholder
- `icon` (string, padrão: 'bi-currency-dollar'): Ícone
- `iconColor` (string, padrão: 'green'): Cor do ícone
- `currency` (string, padrão: 'R$'): Símbolo da moeda
- `required` (bool, padrão: false): Campo obrigatório
- `disabled` (bool, padrão: false): Campo desabilitado
- `maxlength` (int, padrão: 12): Comprimento máximo

**Exemplo de uso:**
```blade
<x-currency-input 
    name="sale_price"
    id="sale_price"
    wire-model="salePrice"
    label="Preço de Venda"
    icon="bi-tag-fill"
    icon-color="orange"
    :required="true"
/>
```

## Cores Disponíveis para Ícones

- `cyan`: Ciano
- `blue`: Azul
- `green`: Verde
- `purple`: Roxo
- `orange`: Laranja
- `red`: Vermelho

## Funcionalidades Automáticas

### Campo de Moeda
- Formatação automática (0,00)
- Máscara de entrada apenas números
- Conversão para formato US no backend
- Validação de entrada

### Campo de Quantidade
- Botões de incremento/decremento
- Validação de min/max
- Sincronização com Livewire
- Scripts únicos por campo (evita conflitos)

### Upload de Imagem
- Preview automático
- Drag & drop
- Validação de tipo e tamanho
- Estados visuais de feedback

### Stepper
- Indicação visual de progresso
- Estados: ativo, completo, pendente
- Suporte a ícones ou números
- Conectores animados

## Vantagens dos Componentes

1. **Reutilização**: Pode ser usado em qualquer formulário
2. **Consistência**: Design padronizado
3. **Manutenibilidade**: Alterações centralizadas
4. **Flexibilidade**: Muitas opções de customização
5. **Acessibilidade**: Labels e estrutura semântica adequada
6. **Performance**: Scripts otimizados por componente
