# 🤖 Sistema de Auto-Categorização Inteligente no Upload

## 📋 Visão Geral

O sistema de upload de invoices (CSV/PDF) agora possui **categorização automática inteligente** que utiliza as categorias reais do banco de dados do usuário, ao invés de IDs fixos.

## 🎯 O que foi Corrigido

### Problema Anterior
- ❌ Todas as categorias eram mapeadas para ID '1' (fixo e incorreto)
- ❌ Não respeitava as categorias criadas pelo usuário
- ❌ Mostrava categorias de produtos ao invés de transações

### Solução Implementada
- ✅ Mapeamento dinâmico baseado nas categorias reais do banco de dados
- ✅ Filtra apenas categorias do tipo 'transaction'
- ✅ Utiliza palavras-chave, tags e regras de auto-categorização
- ✅ Categoria padrão inteligente quando não encontra correspondência

## 🔧 Funcionalidades

### 1. Filtro de Categorias
O upload agora mostra apenas categorias do tipo `transaction`:

```php
$this->categories = Category::where('is_active', 1)
    ->where('user_id', Auth::id())
    ->where('type', 'transaction')  // ← Apenas transações!
    ->get();
```

### 2. Mapeamento Dinâmico
O sistema cria um mapeamento automático baseado em:

#### a) Nome da Categoria
```php
// Se existe categoria "Alimentação"
$mapping['ALIMENTAÇÃO'] = $categoryId;
$mapping['ALIMENTACAO'] = $categoryId; // Sem acento
```

#### b) Palavras-chave Específicas
```php
// Para categoria "Alimentação"
$mapping['SUPERMERCADO'] = $categoryId;
$mapping['RESTAURANTE'] = $categoryId;
$mapping['LANCHONETE'] = $categoryId;
// etc...
```

#### c) Tags da Categoria
Se a categoria tem tags configuradas:
```php
// tags: "mercado, feira, alimentos"
$mapping['MERCADO'] = $categoryId;
$mapping['FEIRA'] = $categoryId;
$mapping['ALIMENTOS'] = $categoryId;
```

#### d) Regras de Auto-categorização
Se configuradas no campo `regras_auto_categorizacao`:
```json
["WALMART", "CARREFOUR", "EXTRA"]
```

### 3. Mapeamentos Pré-configurados

O sistema já inclui mapeamentos comuns para categorias típicas:

#### 🍔 Alimentação
- Supermercados: `ANTONELLI`, `ATACADÃO`, `POPULAR`, `ROFATTO`
- Restaurantes: `BEER`, `BURGER`, `TOURO`, `TUTTIBOM`
- Lanches: `ACAITERIA`, `COMITIVALANCH`

#### 🚗 Transporte
- Postos: `POSTO`, `SHELL`, `FROGPAY`
- Pneus: `PNEUS`, `JSROSAPNEUS`
- Apps: `UBER`, `99`, `CABIFY`

#### 🛒 Compras
- E-commerce: `SHOPEE`, `MERCADO LIVRE`, `NETSHOES`
- Lojas: `TABACARIA`, `SHOPPING`

#### 💊 Saúde
- Farmácias: `PHARMA`, `DROGARIA`, `FARMÁCIA`

#### 💅 Beleza
- Cosméticos: `BOTICÁRIO`, `NATURA`, `EUDORA`

#### 📱 Telecomunicações
- Operadoras: `CLARO`, `VIVO`, `TIM`, `OI`
- Streaming: `NETFLIX`, `SPOTIFY`

#### 🎭 Entretenimento
- Parques: `HOPI HARI`, `CINEMA`, `TEATRO`

#### ✈️ Viagem
- Hospedagem: `AIRBNB`, `HOTEL`, `POUSADA`, `BOOKING`

## 📊 Como Usar

### 1. Configure suas Categorias
Crie categorias do tipo `transaction` com nomes descritivos:
- `Alimentação`
- `Transporte`
- `Saúde`
- `Entretenimento`
- etc.

### 2. Adicione Tags (Opcional)
No cadastro da categoria, adicione tags separadas por vírgula:
```
Tags: mercado, feira, supermercado, alimentos
```

### 3. Configure Regras de Auto-categorização (Opcional)
No campo `regras_auto_categorizacao` (JSON):
```json
["WALMART", "CARREFOUR", "EXTRA", "PÃO DE AÇÚCAR"]
```

### 4. Faça o Upload
Ao fazer upload de CSV/PDF:
1. O sistema analisa cada descrição de transação
2. Busca palavras-chave no mapeamento
3. Atribui automaticamente a categoria correspondente
4. Se não encontrar, usa a primeira categoria de transação do usuário

## 🎨 Exemplo Prático

### Cenário
Você tem a categoria **"Alimentação"** (ID: 15) criada no sistema.

### Upload de Transações
```csv
Data,Descrição,Tipo,Valor
01/01/2025,SUPERMERCADO ANTONELLI,Débito,150.00
02/01/2025,RESTAURANTE TOURO,Crédito,75.50
03/01/2025,UBER - CORRIDA,Débito,25.00
```

### Resultado Automático
```
✅ SUPERMERCADO ANTONELLI → Categoria: Alimentação (ID: 15)
✅ RESTAURANTE TOURO      → Categoria: Alimentação (ID: 15)
⚠️ UBER - CORRIDA         → Categoria: Transporte (se existir) ou padrão
```

## 🔍 Logs e Debug

O sistema registra logs detalhados:

```php
Log::info('Categoria encontrada', [
    'keyword' => 'SUPERMERCADO',
    'category_id' => 15,
    'description' => 'SUPERMERCADO ANTONELLI'
]);
```

## 💡 Dicas de Otimização

### 1. Crie Categorias Específicas
Ao invés de uma categoria genérica "Despesas", crie categorias específicas:
- ✅ Alimentação
- ✅ Transporte
- ✅ Saúde
- ❌ Despesas (muito genérico)

### 2. Use Tags Estrategicamente
Adicione variações e sinônimos nas tags:
```
Tags: combustivel, gasolina, etanol, posto, abastecimento
```

### 3. Configure Regras JSON
Para estabelecimentos específicos que você frequenta:
```json
["POSTO IPIRANGA", "AUTO POSTO SHELL", "BR MANIA"]
```

### 4. Mantenha Nomes Simples
Use nomes de categorias que sejam fáceis de identificar:
- ✅ "Alimentação" (simples e direto)
- ❌ "Despesas com Alimentação e Bebidas" (muito longo)

## 🚀 Benefícios

- ✨ **Automação:** Categorização automática baseada em palavras-chave
- 🎯 **Precisão:** Usa categorias reais do seu banco de dados
- 🔄 **Flexibilidade:** Pode adicionar novas palavras-chave via tags
- 📊 **Inteligência:** Aprende com suas configurações
- ⚡ **Velocidade:** Processa centenas de transações rapidamente

## 🔜 Próximas Melhorias

- [ ] Machine Learning para aprender com categorizações manuais
- [ ] Sugestões de categorias baseadas no histórico
- [ ] Interface para gerenciar palavras-chave por categoria
- [ ] Importar/exportar regras de categorização
- [ ] Relatório de precisão da categorização automática

---

**Desenvolvido com ❤️ para FlowManager**
