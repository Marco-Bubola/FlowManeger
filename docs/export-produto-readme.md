# 📸 Export de Cards de Produtos

## Funcionalidade de Exportação de Imagem de Produtos

Este recurso permite exportar cards de produtos como imagens PNG de alta qualidade em dois formatos distintos.

---

## 🎯 Formatos Disponíveis

### 1. **Completo** (Com Custo)
- Exibe preço de custo
- Exibe preço de venda
- Ideal para uso interno, controle de estoque, planilhas gerenciais

### 2. **Público** (Sem Custo)
- Exibe apenas preço de venda
- Oculta o preço de custo
- Ideal para compartilhar em redes sociais, WhatsApp, catálogos de clientes

---

## 🚀 Como Usar

### Exportar Produto Individual

1. Acesse a listagem de produtos em **Produtos > Catálogo**
2. Localize o produto que deseja exportar
3. Clique no botão verde **📷 Exportar** (ícone de imagem) no card do produto
4. Selecione o formato desejado:
   - **Completo**: Inclui preço de custo
   - **Público**: Apenas preço de venda
5. Clique em **Baixar Imagem**
6. A imagem será baixada automaticamente como PNG

### Nome do Arquivo

O arquivo será salvo automaticamente com o padrão:
```
{codigo_produto}-{tipo}.png

Exemplos:
- PROD001-completo.png
- PROD002-publico.png
```

---

## 🎨 Características da Imagem Exportada

- **Formato**: PNG com fundo transparente
- **Resolução**: Alta qualidade (3x scale)
- **Elementos incluídos**:
  - Imagem do produto
  - Nome do produto
  - Código do produto (badge)
  - Categoria com ícone
  - Quantidade em estoque
  - Preços (conforme formato escolhido)
  - Logo/marca da aplicação no rodapé

---

## 💡 Casos de Uso

### Uso Interno (Formato Completo)
- Relatórios de margem de lucro
- Planilhas de controle financeiro
- Análise de precificação
- Documentação interna

### Uso Externo (Formato Público)
- Catálogos para clientes
- Posts em redes sociais (Instagram, Facebook)
- Envio via WhatsApp
- Folders e materiais promocionais
- E-commerce (imagens de produto)

---

## 🛠️ Tecnologias Utilizadas

- **Livewire 3**: Comunicação entre componentes
- **Alpine.js**: Interatividade do modal
- **html2canvas**: Conversão de HTML para imagem
- **Tailwind CSS**: Estilização responsiva
- **Laravel**: Backend e gerenciamento de dados

---

## 📋 Estrutura de Arquivos

```
app/
├── Livewire/
│   └── Products/
│       └── ExportProductCard.php    # Componente Livewire

resources/
└── views/
    └── livewire/
        └── products/
            ├── products-index.blade.php         # Botão de exportação
            └── export-product-card.blade.php    # Modal de exportação

public/
└── assets/
    └── css/
        └── produtos.css                         # Estilos do botão
```

---

## 🔧 Configurações

### Ajustar Qualidade da Imagem

No arquivo `export-product-card.blade.php`, linha ~20:
```javascript
const canvas = await html2canvas(cardElement, {
    backgroundColor: null,
    scale: 3,  // Altere este valor (1-5) para ajustar qualidade
    logging: false,
    useCORS: true,
    allowTaint: true
});
```

### Personalizar Nome da Marca

No rodapé do card (linha ~230 do export-product-card.blade.php):
```blade
<p class="text-xs text-slate-500 dark:text-slate-400">
    {{ config('app.name', 'FlowManager') }}
</p>
```

---

## ⚠️ Requisitos

- PHP 8.1+
- Laravel 10+
- Livewire 3+
- Navegador moderno com suporte a ES6+

---

## 📝 Observações

- As imagens dos produtos devem estar acessíveis via HTTP/HTTPS
- Para produtos sem imagem, será usado o placeholder padrão
- O atributo `crossorigin="anonymous"` garante compatibilidade CORS
- Delay de 500ms entre downloads múltiplos evita bloqueio do navegador

---

## 🎉 Próximas Melhorias (Futuro)

- [ ] Exportação em lote de múltiplos produtos (ZIP)
- [ ] Formatos adicionais (JPEG, WebP)
- [ ] Tamanhos personalizados (pequeno, médio, grande)
- [ ] Templates de card personalizáveis
- [ ] Marca d'água customizável
- [ ] Integração com WhatsApp Business API
- [ ] Preview antes de baixar
- [ ] Histórico de exportações

---

## 🐛 Solução de Problemas

### Imagem não está sendo baixada
- Verifique se o JavaScript está habilitado
- Confirme se as imagens dos produtos estão acessíveis
- Limpe o cache do navegador

### Imagem cortada ou com problemas
- Verifique a resolução da imagem do produto
- Ajuste o valor de `scale` no html2canvas
- Certifique-se de que não há elementos com `position: fixed`

### Erro CORS
- Adicione `crossorigin="anonymous"` nas tags `<img>`
- Configure headers CORS no servidor

---

## 📞 Suporte

Para dúvidas ou problemas, entre em contato com a equipe de desenvolvimento.

**Desenvolvido com ❤️ para FlowManager**
