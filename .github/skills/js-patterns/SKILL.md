---
name: js-patterns
description: "Use when: criando novos arquivos JavaScript, adicionando interatividade a páginas, integrando com Livewire via Alpine/JS, criando módulos JS para páginas ou componentes, ou usando localStorage no FlowManager."
---

# Padrões JavaScript — FlowManager

## Estrutura de Arquivos JS

```
public/assets/js/
└── settings.js          ← JS das páginas de configurações (window.FlowSettings)

resources/js/
├── app.js               ← Entry point Vite (importa flowbite)
└── upload-interactions.js ← Interações de upload (compilado via Vite)
```

### Quando Criar um Novo Arquivo JS

- **Feature de página específica** → `public/assets/js/<page-slug>.js`
- **Módulo reutilizável** → `public/assets/js/<modulo>.js`
- **Integração build** (usa import/export, npm packages) → `resources/js/<nome>.js` + registrar no `vite.config.js`

## Estrutura de Módulo JS (IIFE Pattern)

Usar IIFE para encapsular e evitar poluição do escopo global:

```js
/* ═══════════════════════════════════════════════════════════
   FLOWMANAGER — <PAGE/MODULE NAME>
   Descrição do propósito deste arquivo
   ═══════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    /* ══════════════════════════════════════════════════════
       CONSTANTES / CONFIGURAÇÃO
       ══════════════════════════════════════════════════════ */
    var CONFIG = {
        storageKey: 'flowmanager:<modulo>',
        debounceMs: 300,
    };

    /* ══════════════════════════════════════════════════════
       FUNÇÕES PRIVADAS
       ══════════════════════════════════════════════════════ */
    function init() {
        // Aguarda DOM estar pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
    }

    function setup() {
        bindEvents();
    }

    function bindEvents() {
        // event listeners aqui
    }

    /* ══════════════════════════════════════════════════════
       API PÚBLICA
       ══════════════════════════════════════════════════════ */
    window.Flow<ModuleName> = {
        init: init,
        // métodos públicos
    };

    // Auto-inicializar
    init();

})();
```

## Namespace Global

Todos os módulos JS públicos seguem o padrão `window.Flow<ModuleName>`:

```js
window.FlowSettings   // settings.js
window.FlowDashboard  // dashboard.js (se existir)
window.FlowProducts   // products.js (se existir)
```

## LocalStorage — Padrões

**Chave de armazenamento:** sempre prefixar com `flowmanager:`:
```js
localStorage.getItem('flowmanager:theme')
localStorage.getItem('flowmanager:color-theme')
localStorage.getItem('flowmanager:<modulo>')
```

**Helper de leitura segura (copiar de settings.js):**
```js
function getStoredJson(key, fallback) {
    try {
        var raw = localStorage.getItem(key);
        if (!raw) return fallback || {};
        var parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : fallback || {};
    } catch (e) {
        return fallback || {};
    }
}
```

## Integração com Livewire

**Despachar evento para o Livewire:**
```js
// Do JS para o componente Livewire
Livewire.dispatch('meu-evento', { dado: 'valor' });

// Ou via elemento
document.getElementById('meu-component').dispatchEvent(
    new CustomEvent('meu-evento', { detail: { dado: 'valor' } })
);
```

**Ouvir eventos do Livewire no JS:**
```js
document.addEventListener('livewire:initialized', function () {
    Livewire.on('produto-selecionado', function (data) {
        console.log(data);
    });
});
```

**Chamar método Livewire do JS (Alpine.js):**
```blade
<div x-data x-on:click="$wire.meuMetodo()">
```

## Alpine.js — Padrões

Alpine.js está disponível via Livewire. Usar para interatividade local sem precisar de componente Livewire:

```blade
{{-- Toggle simples --}}
<div x-data="{ aberto: false }">
    <button @click="aberto = !aberto">Toggle</button>
    <div x-show="aberto" x-transition>Conteúdo</div>
</div>

{{-- Dropdown --}}
<div x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open">Menu</button>
    <div x-show="open" x-transition class="absolute ...">
        ...
    </div>
</div>
```

## CSRF em Requisições Fetch

Sempre incluir token CSRF em requisições POST/PUT/DELETE:

```js
var csrf = document.querySelector('meta[name="csrf-token"]');
if (!csrf) return;

fetch('/rota/api', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf.content,
    },
    body: JSON.stringify({ dado: valor })
})
.then(function (r) { return r.json(); })
.then(function (data) { /* ... */ })
.catch(function (err) { console.error(err); });
```

## Debounce em Inputs

```js
function debounce(fn, delay) {
    var timer;
    return function () {
        var args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            fn.apply(this, args);
        }, delay);
    };
}

// Uso
var handleSearch = debounce(function (e) {
    // buscar...
}, 300);

document.getElementById('search').addEventListener('input', handleSearch);
```

## Carregando JS de Página no Blade

### JS compilado via Vite (tem imports/npm)
1. Adicionar ao `vite.config.js`:
```js
input: [
    // ... existentes
    'resources/js/meu-modulo.js',
],
```
2. Carregar no template:
```blade
@vite(['resources/js/meu-modulo.js'])
```

### JS simples de página (sem build)
```blade
@push('scripts')
<script src="{{ asset('assets/js/<page-slug>.js') }}"></script>
@endpush
```
(O `@stack('scripts')` deve estar no layout antes de `</body>`)

## Flatpickr — Datepicker Padrão

```js
flatpickr('#meu-input-data', {
    locale: 'pt',
    dateFormat: 'd/m/Y',
    allowInput: true,
});

flatpickr('#meu-range-data', {
    locale: 'pt',
    mode: 'range',
    dateFormat: 'd/m/Y',
});
```

## Boas Práticas

- Sempre usar `'use strict'` nos IIFEs
- Usar `var` (não `let`/`const`) dentro de IIFEs para compatibilidade máxima; fora de IIFEs (ES modules), usar `const`/`let`
- Nunca acessar `document.*` antes do DOM carregar — usar `DOMContentLoaded` ou `document.readyState`
- Não usar `document.write()` — vulnerabilidade XSS
- Sanitizar qualquer dado antes de inserir como HTML (`textContent` em vez de `innerHTML` quando possível)
- Quando usar `innerHTML`, garantir que o dado vem do servidor confiável ou está sanitizado
- Event listeners: remover quando o componente for destruído (Livewire: usar `document.addEventListener('livewire:navigated', ...)`)
