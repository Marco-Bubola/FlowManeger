# 🎯 Sistema de Metas e Objetivos - Estilo Trello

## 📊 Visão Geral

Sistema completo de gerenciamento de metas e objetivos baseado no conceito Kanban do Trello, totalmente integrado com o FlowManager para tracking financeiro e progresso de objetivos.

---

## 🏗️ Estrutura do Sistema

### Hierarquia de Dados

```
User (Usuário)
  └─ Board (Quadro de Metas)
      ├─ tipo: 'pessoal', 'financeiro', 'profissional', 'saude', 'estudos'
      ├─ cor de fundo
      └─ Lists (Listas de Status)
          ├─ ordem de exibição
          └─ Goals (Metas/Objetivos - Cards)
              ├─ título
              ├─ descrição
              ├─ período: 'diario', 'semanal', 'mensal', 'trimestral', 'anual', 'custom'
              ├─ prioridade: 'baixa', 'media', 'alta', 'urgente'
              ├─ labels (etiquetas coloridas)
              ├─ data_inicio
              ├─ data_vencimento
              ├─ progresso (0-100%)
              ├─ valor_meta (para metas financeiras)
              ├─ valor_atual
              ├─ cofrinho_id (integração)
              ├─ category_id (integração)
              ├─ Checklists (sub-tarefas)
              ├─ Comments (comentários)
              ├─ Attachments (anexos)
              └─ Activities (log de atividades)
```

---

## 🎨 Tipos de Quadros (Boards)

### 1. Metas Financeiras
- **Listas padrão**: "Planejando", "Economizando", "Alcançado", "Arquivado"
- **Integração**: Links com cofrinhos e cashbook
- **Cards incluem**: Valor meta, valor atual, % progresso
- **Exemplos**: "Comprar carro", "Viagem", "Fundo de emergência"

### 2. Metas Pessoais
- **Listas padrão**: "Ideias", "Iniciado", "Em Progresso", "Concluído"
- **Cards incluem**: Hábitos diários, objetivos de vida
- **Exemplos**: "Ler 12 livros", "Perder 10kg", "Aprender inglês"

### 3. Metas Profissionais/Carreira
- **Listas padrão**: "Backlog", "Este Mês", "Fazendo", "Feito"
- **Cards incluem**: Prazos, checklists de etapas
- **Exemplos**: "Certificação AWS", "Aumentar vendas 20%"

### 4. Metas de Saúde
- **Listas padrão**: "Rotina", "Semanal", "Mensal", "Conquistado"
- **Cards incluem**: Frequência, repetições
- **Exemplos**: "Academia 3x semana", "Beber 2L água/dia"

### 5. Metas de Estudos
- **Listas padrão**: "A Estudar", "Estudando", "Revisão", "Dominado"
- **Cards incluem**: Material, progresso em capítulos
- **Exemplos**: "Curso Python", "Certificação PMP"

---

## 💾 Estrutura do Banco de Dados

### Tabela: `goal_boards`
```sql
- id
- user_id (FK users)
- name (nome do quadro)
- description
- tipo (enum: pessoal, financeiro, profissional, saude, estudos, outro)
- background_color
- background_image
- is_favorite (boolean)
- order (ordem de exibição)
- created_at
- updated_at
```

### Tabela: `goal_lists`
```sql
- id
- board_id (FK goal_boards)
- name (nome da lista - ex: "A Fazer")
- color (cor da lista)
- order (posição na board)
- created_at
- updated_at
```

### Tabela: `goals` (cards)
```sql
- id
- list_id (FK goal_lists)
- user_id (FK users)
- title (título da meta)
- description (descrição detalhada)
- periodo (enum: diario, semanal, mensal, trimestral, anual, custom)
- prioridade (enum: baixa, media, alta, urgente)
- data_inicio
- data_vencimento
- progresso (0-100)
- valor_meta (decimal - para metas financeiras)
- valor_atual (decimal)
- cofrinho_id (FK cofrinhos - nullable)
- category_id (FK categories - nullable)
- labels (json - array de labels)
- order (posição no card)
- is_archived (boolean)
- completed_at (quando foi concluído)
- created_at
- updated_at
```

### Tabela: `goal_checklists`
```sql
- id
- goal_id (FK goals)
- title (título do checklist)
- order
- created_at
- updated_at
```

### Tabela: `goal_checklist_items`
```sql
- id
- checklist_id (FK goal_checklists)
- text (descrição do item)
- is_completed (boolean)
- order
- created_at
- updated_at
```

### Tabela: `goal_comments`
```sql
- id
- goal_id (FK goals)
- user_id (FK users)
- comment (texto)
- created_at
- updated_at
```

### Tabela: `goal_attachments`
```sql
- id
- goal_id (FK goals)
- filename
- file_path
- file_type
- file_size
- created_at
```

### Tabela: `goal_activities`
```sql
- id
- goal_id (FK goals)
- user_id (FK users)
- action (enum: created, moved, updated, completed, commented, etc)
- description (texto da ação)
- old_value (json)
- new_value (json)
- created_at
```

---

## 🔗 Integrações com Sistema Existente

### 1. Integração com Cofrinhos
```php
// Meta financeira vinculada a cofrinho
Goal::where('cofrinho_id', $cofrinhoId)
    ->update([
        'valor_atual' => Cashbook::where('cofrinho_id', $cofrinhoId)
            ->where('type_id', 1)->sum('value')
    ]);
```

**Use cases:**
- Meta "Juntar R$ 10.000" → Vincula ao cofrinho "Viagem"
- Progresso automático baseado em depósitos
- Notificação quando 50%, 75%, 100% alcançado

### 2. Integração com Cashbook
```php
// Rastrear despesas relacionadas a uma meta
Goal::where('category_id', $categoryId)
    ->where('periodo', 'mensal')
    ->each(function($goal) {
        $gasto = Cashbook::where('category_id', $goal->category_id)
            ->whereMonth('date', now())
            ->sum('value');
        
        $goal->update(['progresso' => ($gasto / $goal->valor_meta) * 100]);
    });
```

**Use cases:**
- Meta "Gastar máximo R$ 500 em lazer/mês"
- Tracking automático de gastos
- Alerta quando ultrapassar 80% da meta

### 3. Integração com Categorias
- Metas podem ser vinculadas a categorias específicas
- Análise de progresso por categoria
- Relatórios comparativos

### 4. Integração com Dashboard
- Widget de metas no dashboard principal
- Progresso visual de metas em andamento
- Metas próximas do prazo

---

## 🎯 Funcionalidades Principais

### 1. Gerenciamento de Quadros (Boards)
- [x] Criar/Editar/Excluir quadros
- [x] Tipos predefinidos com templates
- [x] Cores e backgrounds personalizados
- [x] Favoritar quadros importantes
- [x] Reordenar quadros

### 2. Gerenciamento de Listas
- [x] Criar/Editar/Excluir listas
- [x] Reordenar listas (drag horizontal)
- [x] Copiar lista
- [x] Mover todos os cards de uma lista
- [x] Arquivar lista

### 3. Gerenciamento de Cards (Metas)
- [x] Criar meta rápida (título apenas)
- [x] Criar meta completa (modal detalhado)
- [x] Editar meta (modal)
- [x] Mover entre listas (drag & drop)
- [x] Reordenar dentro da lista
- [x] Copiar meta
- [x] Arquivar/Desarquivar
- [x] Excluir

### 4. Detalhes do Card (Modal)
**Seção Cabeçalho:**
- Título editável inline
- Lista atual (com opção de mover)
- Etiquetas coloridas

**Seção Principal:**
- Descrição (markdown)
- Período (diário, semanal, mensal, etc)
- Prioridade (visual com cores)
- Datas (início e vencimento)
- Progresso (barra + porcentagem)
- Valores (meta e atual) - se financeira

**Seção Checklists:**
- Criar checklist
- Adicionar itens
- Marcar como concluído
- Progresso do checklist
- Excluir checklist

**Seção Anexos:**
- Upload de arquivos
- Preview de imagens
- Download de documentos

**Seção Comentários:**
- Adicionar comentário
- Editar/Excluir próprios comentários
- Timestamp e autor

**Seção Atividades:**
- Log automático de todas as ações
- Filtrar por tipo de atividade
- Timeline visual

**Sidebar Direita (Ações):**
- Mover para outra lista
- Copiar card
- Adicionar etiqueta
- Alterar datas
- Arquivar
- Excluir

### 5. Labels (Etiquetas)
Cores predefinidas com significados:
- 🟢 Verde: "No prazo"
- 🟡 Amarelo: "Atenção"
- 🟠 Laranja: "Atrasado"
- 🔴 Vermelho: "Urgente"
- 🔵 Azul: "Informação"
- 🟣 Roxo: "Financeiro"
- 🟤 Marrom: "Pessoal"
- ⚫ Cinza: "Baixa prioridade"

### 6. Filtros e Busca
- Filtrar por label
- Filtrar por período
- Filtrar por prioridade
- Filtrar por status
- Busca por texto
- Filtrar com vencimento próximo
- Mostrar apenas favoritos

### 7. Automações
- Auto-calcular progresso de metas financeiras
- Auto-mover cards quando 100% concluído
- Notificações de prazo próximo (3 dias)
- Update automático de valores de cofrinhos
- Cálculo de progresso de checklists

### 8. Estatísticas e Relatórios
- Total de metas por período
- Taxa de conclusão
- Tempo médio de conclusão
- Metas mais demoradas
- Progresso mensal
- Comparativo ano a ano

---

## 🎨 Interface Visual (Estilo Trello)

### Layout Principal
```
+----------------------------------------------------------+
|  Header: [Logo] [Busca] [Filtros] [+ Novo Quadro] [User]|
+----------------------------------------------------------+
|                                                          |
|  Sidebar (Opcional):                    Main Content:    |
|  - Meus Quadros               +------------------------+ |
|  - Favoritos                  | Board: Metas Financ... | |
|  - Arquivados                 +------------------------+ |
|  - Todos                      |                        | |
|                               | +------+ +------+ +--+ | |
|                               | | A    | | Em   | |C | | |
|                               | | Fazer| | Prog.| |..| | |
|                               | +------+ +------+ +--+ | |
|                               | | Card | | Card | |C | | |
|                               | | Card | | Card | |a | | |
|                               | | Card | +------+ |r | | |
|                               | +------+ + Add   | |d | | |
|                               | + Add  |         | +--+ | |
|                               +------------------------+ |
+----------------------------------------------------------+
```

### Card Visual
```
+-------------------------------------+
| 🏷️ [Labels coloridas]              |
| Título da Meta                      |
| 📅 12 Fev  ✓ 2/5  💬 3  📎 1       |
| [========>........] 40%             |
+-------------------------------------+
```

### Modal de Detalhes (Popup)
```
+-------------------------------------------------------+
| [X] Título da Meta                    [⋮ Menu]       |
|     na lista: Em Progresso            [Mover] [Copiar]|
| 🏷️ [Etiquetas]                                        |
+-------------------------------------------------------+
| Descrição: [Editor Markdown]                          |
| Período: [Mensal ▼]  Prioridade: [Alta 🔴]           |
| 📅 Início: 01/01/26  Vencimento: 31/01/26            |
| 💰 Meta: R$ 1.000  Atual: R$ 450  [========>...] 45% |
|                                                       |
| ✓ Checklist (2/5 concluídos)                         |
|   [x] Item 1                                          |
|   [x] Item 2                                          |
|   [ ] Item 3                                          |
|   [ ] Item 4                                          |
|   [ ] Item 5                                          |
|   + Adicionar item                                    |
|                                                       |
| 📎 Anexos (1)                                         |
|   [documento.pdf] [Download]                          |
|   + Adicionar anexo                                   |
|                                                       |
| 💬 Comentários (3)                                    |
|   João • há 2 dias                                    |
|   Comentário aqui...                                  |
|   [Editar] [Excluir]                                  |
|   + Adicionar comentário                              |
|                                                       |
| 📋 Atividades                                         |
|   • João moveu de "A Fazer" para "Em Progresso"      |
|   • Maria adicionou checklist                         |
|   • João criou este card                              |
+-------------------------------------------------------+
```

---

## 🚀 Fluxo de Uso

### Cenário 1: Meta Financeira
1. Usuário cria quadro "Metas Financeiras 2026"
2. Cria lista "Economizando"
3. Adiciona card "Juntar R$ 10.000 para viagem"
4. Vincula ao cofrinho "Viagem Europa"
5. Sistema atualiza progresso automaticamente conforme depósitos
6. Quando atinge 100%, move para lista "Alcançado"

### Cenário 2: Meta Pessoal
1. Usuário cria quadro "Desenvolvimento Pessoal"
2. Cria listas: "Backlog", "Fazendo", "Feito"
3. Adiciona card "Ler 12 livros em 2026"
4. Cria checklist com 12 itens (1 por livro)
5. Marca livros conforme lê
6. Progresso atualiza automaticamente (items/total)

### Cenário 3: Meta de Gastos
1. Usuário cria meta "Gastar max R$ 500 em lazer/mês"
2. Vincula à categoria "Lazer"
3. Sistema rastreia gastos automaticamente
4. Quando ultrapassa 80%, alerta aparece
5. No fim do mês, analisa se cumpriu meta

---

## 📱 Responsividade

### Desktop
- Layout 3-4 listas lado a lado
- Modais grandes com todos os detalhes
- Drag & drop total

### Tablet
- Layout 2-3 listas
- Modais medianos
- Touch drag & drop

### Mobile
- Layout 1 lista por vez (swipe horizontal)
- Modals fullscreen
- Touch gestures

---

## 🔔 Notificações

### Tipos de Notificações
1. **Prazo próximo**: 3 dias antes do vencimento
2. **Meta alcançada**: Quando chega a 100%
3. **Milestone**: 25%, 50%, 75% de progresso
4. **Comentário**: Quando alguém comenta (futuro multi-user)
5. **Atrasada**: Passou da data de vencimento

---

## 📊 Dashboard de Metas (Página Inicial)

### Visão Geral
```
+----------------------------------------------------------+
| 🎯 Minhas Metas                          [+ Nova Meta]   |
+----------------------------------------------------------+
| [Resumo Geral]                                           |
| 📈 15 Metas Ativas  ✅ 8 Concluídas  ⏰ 3 Atrasadas     |
| Progresso Geral: [==========>............] 45%           |
+----------------------------------------------------------+
| [Quadros]                                                |
| 📊 Metas Financeiras (5)  →  Visualizar                 |
| 💪 Metas Pessoais (8)     →  Visualizar                 |
| 🏢 Metas Profissionais (2)→  Visualizar                 |
+----------------------------------------------------------+
| [Metas Urgentes]                                         |
| 🔴 Juntar R$ 10k (vence em 2 dias) - 89%                |
| 🟠 Academia 3x semana (atrasada) - 30%                   |
+----------------------------------------------------------+
| [Progresso Mensal]                                       |
| [Gráfico de barras: Jan a Dez]                          |
+----------------------------------------------------------+
```

---

## ✅ Checklist de Desenvolvimento

### Fase 1: Estrutura Base
- [ ] Migrations (8 tabelas)
- [ ] Models com relacionamentos
- [ ] Seeders (dados exemplo)

### Fase 2: Backend
- [ ] GoalBoard Controller/Livewire
- [ ] GoalList Controller/Livewire
- [ ] Goal Controller/Livewire (CRUD)
- [ ] GoalChecklist Service
- [ ] GoalComment Service
- [ ] GoalActivity Logger

### Fase 3: Frontend - Listagem
- [ ] Página principal (dashboard metas)
- [ ] Listagem de quadros
- [ ] View de board (estilo Trello)
- [ ] Cards nas listas

### Fase 4: Frontend - Interatividade
- [ ] Drag & Drop (SortableJS)
- [ ] Modal de detalhes do card
- [ ] Formulários de criação
- [ ] Edição inline

### Fase 5: Features Avançadas
- [ ] Checklists
- [ ] Comentários
- [ ] Anexos (upload)
- [ ] Labels
- [ ] Filtros

### Fase 6: Integrações
- [ ] Link com cofrinhos
- [ ] Link com cashbook
- [ ] Cálculo automático de progresso
- [ ] Notificações

### Fase 7: Automações
- [ ] Auto-mover cards concluídos
- [ ] Auto-atualizar valores financeiros
- [ ] Alertas de prazo
- [ ] Relatórios

### Fase 8: Polimento
- [ ] Responsividade mobile
- [ ] Animações
- [ ] Testes
- [ ] Documentação

---

## 🎨 Paleta de Cores

### Backgrounds de Boards
- `#0079BF` - Azul Trello
- `#D29034` - Laranja
- `#519839` - Verde
- `#B04632` - Vermelho
- `#89609E` - Roxo
- `#CD5A91` - Rosa
- `#4BBF6B` - Verde Lima
- `#00AECC` - Cyan

### Labels
- `#61BD4F` - Verde Sucesso
- `#F2D600` - Amarelo Atenção
- `#FF9F1A` - Laranja
- `#EB5A46` - Vermelho Urgente
- `#0079BF` - Azul Info
- `#C377E0` - Roxo
- `#00C2E0` - Cyan
- `#344563` - Cinza

---

Esta é a especificação completa! Agora vou implementar todo o sistema passo a passo.
