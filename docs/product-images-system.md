# Sistema de Imagens de Produtos - FlowManager

## ✅ Correção Aplicada

### Problema Identificado
O link simbólico entre `public/storage` e `storage/app/public` estava quebrado ou mal configurado, impedindo o acesso às imagens dos produtos pelo navegador.

### Solução Implementada

1. **Link Simbólico Recriado**
   - Removido link antigo
   - Executado: `php artisan storage:link`
   - Link criado com sucesso: `public/storage` → `storage/app/public`

2. **Accessor Adicionado ao Model Product**
   - Criado `getImageUrlAttribute()` no model `Product`
   - Retorna automaticamente a URL completa da imagem
   - Uso: `$product->image_url` (em vez de construir manualmente com `asset()`)

## 📁 Estrutura de Diretórios

```
storage/app/public/products/     ← Arquivos físicos das imagens
        ↓ (link simbólico)
public/storage/products/          ← Acesso via web
```

## 🔧 Configuração

### 1. Arquivo .env
```env
APP_URL=http://localhost:8000
```

### 2. Configuração do Filesystem (config/filesystems.php)
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

### 3. Link Simbólico
```bash
php artisan storage:link
```

## 💾 Banco de Dados

A coluna `image` na tabela `products` armazena **apenas o nome do arquivo**:
- ✅ Correto: `product_696ebfa095ec4.jpeg`
- ❌ Errado: `storage/products/product_696ebfa095ec4.jpeg`
- ❌ Errado: `/storage/products/product_696ebfa095ec4.jpeg`

## 🎨 Como Usar no Frontend

### Método 1: Usando o Accessor (Recomendado)
```blade
<img src="{{ $product->image_url }}" alt="{{ $product->name }}">
```

### Método 2: Usando o Helper asset() (Forma Antiga)
```blade
<img src="{{ $product->image ? asset('storage/products/' . $product->image) : asset('storage/products/product-placeholder.png') }}" 
     alt="{{ $product->name }}">
```

### Método 3: Com Fallback para Placeholder
```blade
<img src="{{ $product->image_url }}" 
     alt="{{ $product->name }}"
     onerror="this.src='{{ asset('storage/products/product-placeholder.png') }}'">
```

## 📤 Upload de Imagens

### No Controller/Livewire
```php
use Illuminate\Support\Facades\Storage;

// Upload da imagem
$imageName = 'product_' . uniqid() . '.' . $request->file('image')->extension();
$request->file('image')->storeAs('products', $imageName, 'public');

// Salvar no banco (apenas o nome)
$product->image = $imageName;
$product->save();
```

### Deletar Imagem Antiga
```php
if ($product->image && $product->image !== 'product-placeholder.png') {
    Storage::disk('public')->delete('products/' . $product->image);
}
```

## 🖼️ Imagem Placeholder

**Localização:** `storage/app/public/products/product-placeholder.png`

Se não existir, crie uma imagem genérica de produto (recomendado: 500x500px)

## ✔️ Checklist de Verificação

- [x] Link simbólico criado com `php artisan storage:link`
- [x] Pasta `storage/app/public/products` existe
- [x] Pasta `public/storage/products` acessível (via link)
- [x] `APP_URL` configurado corretamente no `.env`
- [x] Model `Product` tem accessor `image_url`
- [ ] Imagem placeholder criada (opcional mas recomendado)

## 🔍 Troubleshooting

### Imagem não aparece no navegador

1. **Verificar se o link existe:**
   ```bash
   Test-Path "public/storage"
   ```

2. **Verificar se a imagem física existe:**
   ```bash
   Test-Path "storage/app/public/products/nome-da-imagem.jpg"
   ```

3. **Verificar se a imagem é acessível via web:**
   ```bash
   Test-Path "public/storage/products/nome-da-imagem.jpg"
   ```

4. **Recriar o link se necessário:**
   ```bash
   Remove-Item "public/storage" -Force
   php artisan storage:link
   ```

### Erro 403 (Forbidden)

Verifique as permissões da pasta:
```bash
# No Windows, geralmente não é problema
# No Linux/Mac:
chmod -R 755 storage
chmod -R 755 public/storage
```

### URL da imagem incorreta

Verifique o `APP_URL` no arquivo `.env` e limpe o cache de configuração:
```bash
php artisan config:clear
php artisan config:cache
```

## 📊 URLs Geradas

Com `APP_URL=http://localhost:8000` e imagem `product_696ebfa095ec4.jpeg`:

- **URL Completa:** `http://localhost:8000/storage/products/product_696ebfa095ec4.jpeg`
- **Caminho Físico:** `C:\projetos\FlowManeger\storage\app\public\products\product_696ebfa095ec4.jpeg`
- **Caminho Público:** `public/storage/products/product_696ebfa095ec4.jpeg`

## 🚀 Melhorias Aplicadas

1. ✅ Link simbólico recriado corretamente
2. ✅ Accessor `image_url` adicionado ao Model Product
3. ✅ Documentação completa criada
4. ⚠️ Recomendação: Adicionar imagem placeholder
5. ⚠️ Recomendação: Atualizar views para usar `$product->image_url`

---

**Data da Correção:** 08/02/2026  
**Status:** ✅ Corrigido e Funcional
