# Sistema de Consórcios - Documentação Completa

## 📋 Visão Geral

Sistema completo para gerenciar consórcios com participantes, pagamentos, sorteios e contemplações.

## 🎯 Funcionalidades Implementadas

### ✅ 1. GERENCIAMENTO DE CONSÓRCIOS

**Criar Consórcio:**
- Nome e descrição
- Valor mensal (R$)
- Duração (1-120 meses)
- Valor total (calculado automaticamente)
- Número máximo de participantes (2-1000)
- Data de início
- Frequência de sorteio (mensal/bimestral/semanal)
- Status (ativo/concluído/cancelado)

**Visualizar Consórcio:**
- Cards com estatísticas: Participantes Ativos, Contemplados, Total Arrecadado, Progresso
- 5 abas: Visão Geral, Participantes, Pagamentos, Sorteios, Contemplados

**Editar Consórcio:**
- Validações inteligentes
- Restrições quando há participantes ou sorteios

---

### ✅ 2. GERENCIAMENTO DE PARTICIPANTES

**Adicionar Participante:**
1. Selecionar cliente da lista
2. Definir data de entrada
3. Adicionar observações (opcional)
4. Sistema gera automaticamente:
   - Número de participação (sequencial)
   - Status inicial: ativo
   - Total pago: R$ 0,00
   - **TODAS as parcelas mensais** (baseado na duração do consórcio)

**Detalhes do Participante:**
- Avatar com inicial do nome
- Nome e email
- Data de entrada
- Status com cor (Ativo/Contemplado/Desistente/Inadimplente)
- Progresso de pagamentos (barra visual)
- Total pago
- Ícone de contemplação

**Validações:**
- Verifica se consórcio tem vagas disponíveis
- Impede participante duplicado no mesmo consórcio

---

### ✅ 3. GERENCIAMENTO DE PAGAMENTOS

**Geração Automática:**
Quando um participante é adicionado, o sistema AUTOMATICAMENTE cria:
- Uma parcela para cada mês da duração
- Referência: Mês/Ano
- Valor: mensalidade do consórcio
- Data de vencimento: baseada na data de início
- Status inicial: pendente

**Exemplo:**
```
Consórcio:
- Início: 01/02/2026
- Duração: 8 meses
- Mensalidade: R$ 50,00

Parcelas Geradas:
1. Fev/2026 - Vencimento: 01/02/2026 - R$ 50,00 - Pendente
2. Mar/2026 - Vencimento: 01/03/2026 - R$ 50,00 - Pendente
3. Abr/2026 - Vencimento: 01/04/2026 - R$ 50,00 - Pendente
... até 8 parcelas
```

**Registrar Pagamento:**
1. Acessar aba "Pagamentos"
2. Clicar em "Registrar" na parcela pendente
3. Informar:
   - Data do pagamento
   - Método (Dinheiro/PIX/Cartão/Transferência/Boleto)
   - Observações (opcional)
4. Sistema atualiza:
   - Status da parcela: pago
   - Total pago do participante
   - Porcentagem de pagamento

**Visualização:**
- Tabela completa com todos os pagamentos
- Filtros por status
- Ações: Registrar pagamento (se pendente)
- Status com cores: Pago/Pendente/Atrasado/Cancelado

---

### ✅ 4. SISTEMA DE SORTEIOS

**Realizar Sorteio:**
- Botão disponível quando há participantes ativos
- Página exclusiva com animação de loteria
- Seleção aleatória entre participantes elegíveis
- Registro do sorteio com data/hora
- Atualização do participante contemplado

**Lógica de Elegibilidade:**
- Participante deve estar ativo
- Não pode já ter sido contemplado
- Deve ter pagamentos em dia (configurável)

**Visualização:**
- Lista de todos os sorteios realizados
- Data, hora e número do sorteio
- Nome do vencedor e número de participação
- Visual atrativo com troféu

---

### ✅ 5. SISTEMA DE CONTEMPLAÇÕES

**Quando Sorteado:**
1. Participante marcado como contemplado
2. Status atualizado
3. Data de contemplação registrada
4. Tipo: Sorteio ou Lance

**Resgate:**
- Dinheiro
- Produtos
- Pendente

**Visualização:**
- Grid com cards dourados
- Informações completas do contemplado
- Data e tipo de contemplação
- Status do resgate

---

## 🔄 Fluxo Completo do Sistema

### Sequência Típica:

```
1. CRIAR CONSÓRCIO
   ↓
2. ADICIONAR PARTICIPANTES
   ├─ Sistema gera automaticamente TODAS as parcelas
   ├─ Participante recebe número sequencial
   └─ Status: Ativo

3. REGISTRAR PAGAMENTOS
   ├─ Marcar parcelas como pagas
   ├─ Informar método de pagamento
   └─ Total pago atualizado automaticamente

4. REALIZAR SORTEIO
   ├─ Selecionar vencedor aleatório
   ├─ Marcar como contemplado
   └─ Registrar contemplação

5. GERENCIAR RESGATE
   ├─ Definir tipo (dinheiro/produtos)
   ├─ Registrar data
   └─ Atualizar status
```

---

## 📊 Cálculos Automáticos

**Participante:**
- `payment_percentage`: (total_paid / valor_total_consorcio) * 100
- `pending_payments_count`: Contagem de parcelas pendentes
- `late_payments_count`: Contagem de parcelas atrasadas

**Consórcio:**
- `active_participants_count`: Participantes ativos
- `contemplated_count`: Participantes contemplados
- `total_collected`: Soma de todos os pagamentos
- `completion_percentage`: (total_collected / valor_total) * 100

---

## 🎨 Interface e Experiência

**Design:**
- Dark mode completo
- Gradientes modernos (emerald/teal/purple)
- Animações suaves
- Responsivo (mobile/tablet/desktop)

**Feedback Visual:**
- Toast messages (sucesso/erro)
- Loading states
- Empty states informativos
- Status com cores intuitivas

**Navegação:**
- Sidebar com menu
- Breadcrumbs
- Tabs para organização
- Modais para ações

---

## ✅ Validações e Regras de Negócio

**Consórcio:**
- Duração: 1-120 meses
- Max participantes: 2-1000
- Valor mensal: > 0
- Não pode editar se há sorteios realizados

**Participante:**
- Cliente único por consórcio (ativo)
- Respeitamax_participants
- Data entrada válida

**Pagamento:**
- Não pode pagar duas vezes
- Data pagamento <= data atual
- Método obrigatório

**Sorteio:**
- Requer participantes ativos
- Participante não contemplado
- Aleatório e justo

---

## 🗂️ Estrutura do Banco de Dados

**Tabelas:**
1. `consortiums` - Dados principais
2. `consortium_participants` - Participantes
3. `consortium_payments` - Parcelas/pagamentos
4. `consortium_draws` - Sorteios realizados
5. `consortium_contemplations` - Detalhes contemplação

**Relacionamentos:**
- Consortium → hasMany → Participants
- Consortium → hasMany → Draws
- Participant → belongsTo → Client
- Participant → hasMany → Payments
- Participant → hasOne → Contemplation
- Draw → belongsTo → Winner (Participant)

---

## 🚀 Status da Implementação

### ✅ COMPLETO:
- [x] CRUD de Consórcios
- [x] Adicionar Participantes
- [x] **Geração Automática de Parcelas**
- [x] **Registrar Pagamentos**
- [x] Listagem de Participantes
- [x] Listagem de Pagamentos
- [x] Listagem de Sorteios
- [x] Listagem de Contemplados
- [x] Sistema de Sorteio
- [x] Validações completas
- [x] Interface moderna
- [x] Dark mode
- [x] Cálculos automáticos

### 🔄 FUNCIONAL:
Todas as funcionalidades principais estão implementadas e funcionando!

---

## 📝 Como Usar

### 1. Criar um Novo Consórcio:
```
1. Acessar "Consórcios" no menu
2. Clicar em "Novo Consórcio"
3. Preencher formulário (3 etapas)
4. Salvar
```

### 2. Adicionar Participante:
```
1. Abrir o consórcio
2. Clicar "Adicionar Participante"
3. Selecionar cliente
4. Confirmar
5. Sistema gera TODAS as parcelas automaticamente ✨
```

### 3. Registrar Pagamento:
```
1. Ir na aba "Pagamentos"
2. Localizar parcela pendente
3. Clicar "Registrar"
4. Informar data e método
5. Confirmar
```

### 4. Realizar Sorteio:
```
1. Clicar "Realizar Sorteio"
2. Confirmar participantes elegíveis
3. Executar sorteio
4. Vencedor é marcado automaticamente
```

---

## 🎯 Sistema 100% Funcional!

**Tudo está implementado e pronto para uso:**
✅ Criar consórcios
✅ Gerenciar participantes
✅ **Parcelas geradas automaticamente**
✅ **Registrar pagamentos com modal**
✅ Realizar sorteios
✅ Gerenciar contemplações
✅ Validações completas
✅ Interface profissional

**Próximos passos sugeridos (opcionais):**
- Relatórios em PDF
- Envio de notificações por email
- Dashboard com gráficos
- Histórico de alterações
- Integração com WhatsApp
