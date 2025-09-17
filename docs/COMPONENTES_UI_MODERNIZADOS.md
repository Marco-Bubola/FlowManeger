# Componentes UI Modernizados - Flow Manager

Este documento descreve os componentes UI modernizados para o sistema Flow Manager, focando em design moderno, micro-interações e experiência visual excepcional.

## 🎨 Design System

Todos os componentes seguem uma estética moderna com:
- **Glassmorphism** - Efeitos de vidro fosco com backdrop-blur
- **Gradientes Animados** - Transições suaves de cores
- **Micro-interações** - Feedback visual instantâneo
- **Animações Fluidas** - Transições duration-300/700 para suavidade
- **Efeitos de Profundidade** - Shadows dinâmicos com cores temáticas
- **Responsividade** - Adaptação dark/light mode automática

## 📋 Componentes Disponíveis

### 1. Stepper Component (`components/stepper.blade.php`)

**Propósito**: Indicador de progresso em múltiplas etapas com design glassmorphism.

**Recursos Visuais**:
- Conectores animados com gradientes
- Ícones com efeitos de brilho
- Estados visuais distintos (completo, ativo, pendente)
- Animações de hover com scale e glow

**Props**:
```php
'steps' => [
    ['number' => 1, 'title' => 'Informações', 'icon' => 'bi-info-circle'],
    ['number' => 2, 'title' => 'Preços', 'icon' => 'bi-currency-dollar'],
    // ...
],
'currentStep' => 1,
'completedSteps' => []
```

**Exemplo de uso**:
```blade
<x-stepper 
    :steps="$steps" 
    :current-step="$currentStep" 
    :completed-steps="$completedSteps" 
/>
```

### 2. Action Buttons (`components/action-buttons-new.blade.php`)

**Propósito**: Conjunto de botões de ação padronizados com animações e micro-interações.

**Recursos Visuais**:
- Gradientes dinâmicos por tipo de botão
- Efeitos de ripple e pulse
- Ícones com animações de rotation e scale
- Feedback visual com cores temáticas

**Props**:
```php
'showBack' => true,
'showNext' => false,
'showSave' => true,
'showCancel' => true,
'backAction' => null,    // Callback Livewire
'nextAction' => null,
'saveAction' => null,
'cancelAction' => null,
'backText' => 'Voltar',
'nextText' => 'Próximo',
'saveText' => 'Salvar',
'cancelText' => 'Cancelar',
'disabled' => false
```

**Exemplo de uso**:
```blade
<x-action-buttons-new 
    :show-back="$currentStep > 1"
    :show-next="$currentStep < 3"
    :show-save="$currentStep === 3"
    back-action="previousStep"
    next-action="nextStep"
    save-action="save"
    cancel-action="cancel"
/>
```

### 3. Image Upload (`components/image-upload.blade.php`)

**Propósito**: Upload de imagem com drag & drop, preview e partículas flutuantes animadas.

**Recursos Visuais**:
- Área de drop com glassmorphism
- Partículas flutuantes css animadas
- Preview com overlay de ações
- Feedback visual para estados (hover, upload, error)
- Gradientes de borda dinâmicos

**Props**:
```php
'wireModel' => 'product_image',
'name' => 'product_image',
'id' => 'product_image',
'maxSize' => '2MB',
'acceptedFormats' => 'JPG, PNG, GIF',
'label' => 'Imagem do Produto',
'icon' => 'bi-image',
'iconColor' => 'purple',
'required' => false,
'disabled' => false
```

**Exemplo de uso**:
```blade
<x-image-upload 
    wire:model="image"
    name="product_image"
    label="Foto do Produto"
    max-size="5MB"
    icon-color="purple"
    :required="true"
/>
```

### 4. Currency Input (`components/currency-input.blade.php`)

**Propósito**: Input de moeda com máscara, validação e feedback visual animado.

**Recursos Visuais**:
- Ícone com gradiente e efeitos de brilho
- Máscara de moeda em tempo real
- Animações de feedback (verde para válido, vermelho para erro)
- Transições suaves com shake para erros

**Props**:
```php
'name' => 'price',
'id' => 'price',
'wireModel' => 'price',
'currency' => 'R$',
'placeholder' => '0,00',
'label' => 'Preço',
'icon' => 'bi-currency-dollar',
'iconColor' => 'green',
'required' => false,
'disabled' => false,
'width' => 'max-w-[15rem]'
```

**Exemplo de uso**:
```blade
<x-currency-input 
    wire:model="price"
    label="Preço de Venda"
    currency="R$"
    icon="bi-currency-dollar"
    icon-color="green"
    :required="true"
/>
```

### 5. Quantity Input (`components/quantity-input.blade.php`)

**Propósito**: Input numérico com botões de incremento/decremento e validação.

**Recursos Visuais**:
- Botões com gradientes e efeitos de ripple
- Feedback colorido (verde para incremento, laranja para decremento)
- Validação com animação de shake
- Ícones com micro-animações

**Props**:
```php
'name' => 'quantity',
'id' => 'quantity',
'wireModel' => 'quantity',
'min' => 0,
'max' => null,
'value' => 0,
'placeholder' => '0',
'label' => 'Quantidade',
'icon' => 'bi-stack',
'iconColor' => 'cyan',
'required' => false,
'disabled' => false,
'width' => 'max-w-[15rem]'
```

**Exemplo de uso**:
```blade
<x-quantity-input 
    wire:model="quantity"
    label="Quantidade em Estoque"
    :min="0"
    :max="9999"
    icon="bi-boxes"
    icon-color="cyan"
    :required="true"
/>
```

## 🎨 Sistema de Cores

### Cores Disponíveis para ícones:
- `cyan` - Ciano/Azul claro
- `blue` - Azul
- `green` - Verde
- `purple` - Roxo  
- `red` - Vermelho
- `orange` - Laranja

### Classes CSS Customizadas

```css
/* Animações */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-3px); }
    75% { transform: translateX(3px); }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    33% { transform: translateY(-10px) rotate(120deg); }
    66% { transform: translateY(5px) rotate(240deg); }
}

/* Utilitários */
.animate-shake { animation: shake 0.3s ease-in-out; }
.animate-slideIn { animation: slideIn 0.3s ease-out; }
.animate-float { animation: float 6s ease-in-out infinite; }
```

## 🚀 Implementação

### 1. Registrar componentes (se necessário)
Os componentes são automaticamente registrados pelo Laravel.

### 2. Usar em páginas Livewire
```blade
<!-- Em qualquer view Blade -->
<x-stepper :steps="$steps" :current-step="1" />
<x-action-buttons-new :show-save="true" save-action="save" />
<x-image-upload wire:model="image" />
<x-currency-input wire:model="price" />
<x-quantity-input wire:model="quantity" />
```

### 3. Propriedades Livewire necessárias
```php
// No seu componente Livewire
public $steps = [
    ['number' => 1, 'title' => 'Info', 'icon' => 'bi-info'],
    // ...
];
public $currentStep = 1;
public $image;
public $price;
public $quantity;
```

## 🛠️ Customização

### Personalizar cores
Adicione novas cores no array `$iconColorClasses` de cada componente:

```php
$iconColorClasses = [
    'cyan' => 'from-cyan-400 to-cyan-600 text-white',
    'custom' => 'from-pink-400 to-pink-600 text-white',
    // ...
];
```

### Personalizar animações
Modifique os valores de `duration-*` e adicione novas classes no CSS:

```css
.custom-animation {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
```

## 📱 Responsividade

Todos os componentes são totalmente responsivos com:
- Breakpoints automáticos do Tailwind
- Dark mode support
- Touch-friendly (mobile)
- Acessibilidade (ARIA labels, keyboard navigation)

## 🔧 Manutenção

### Checklist de atualizações:
- [ ] Manter consistência visual entre componentes
- [ ] Testar em modo escuro
- [ ] Validar acessibilidade
- [ ] Verificar performance de animações
- [ ] Atualizar documentação quando adicionar props

---

## 🎉 Resultado Final

A página `create-product.blade.php` agora possui:

1. **Header modernizado** com gradientes e glassmorphism
2. **Stepper animado** com conectores e efeitos visuais
3. **Campos de formulário** com design consistente e moderno
4. **Upload de imagem** com partículas e feedback visual
5. **Botões de ação** com micro-interações e animações
6. **Inputs especializados** (moeda e quantidade) com validação visual

Todos os componentes são **reutilizáveis**, **responsivos** e seguem o mesmo **design system** para garantir consistência visual em todo o projeto.

---

**Versão**: 2.0  
**Última atualização**: Dezembro 2024  
**Autor**: GitHub Copilot

---

*Estes componentes foram desenvolvidos com foco na experiência do usuário, performance e manutenibilidade. Cada componente é independente e pode ser usado em qualquer página do sistema.*
