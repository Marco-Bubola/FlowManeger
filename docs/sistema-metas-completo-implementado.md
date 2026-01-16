# 🎯 Sistema de Metas e Objetivos - Estilo Trello

## 📖 Visão Geral

Sistema completo de gerenciamento de metas e objetivos inspirado no Trello, com design moderno, funcionalidade de drag-and-drop e integração total com o sistema de cofrinhos e categorias do FlowManager.

---

## ✨ Funcionalidades Principais

### 🏠 Dashboard de Metas (`/goals`)
- **KPIs Principais**: Total, Ativas, Concluídas, Progresso Médio
- **Alertas Inteligentes**: Metas atrasadas e vencendo em 7 dias
- **Metas Urgentes**: Lista priorizada com status visual
- **Distribuição de Progresso**: Gráfico visual por faixa
- **Quadros do Usuário**: Acesso rápido a todos os boards
- **Metas por Período**: Estatísticas por tipo (diário, mensal, anual)
- **Atividades Recentes**: Timeline de ações no sistema

### 📋 Quadro Kanban (`/goals/board/{id}`)
- **Layout Trello**: Colunas (listas) e cards (metas) arrastáveis
- **Drag & Drop**: Mover metas entre listas com SortableJS
- **Cards Ricos**: Progress bars, badges de prioridade, ícones
- **Modais Completos**: Criar lista, criar meta, detalhes
- **Cores Personalizadas**: Cada lista e board com cor própria
- **Filtros Visuais**: Por prioridade, período, status

---

## 🗄️ Estrutura do Banco de Dados

### Tabelas Criadas (8 total)

#### 1. `goal_boards` - Quadros/Boards
```sql
- user_id (FK para users)
- name (nome do quadro)
- description (descrição)
- tipo (enum: financeiro, pessoal, profissional, saude, estudos)
- background_color (cor do quadro)
- background_image (opcional)
- is_favorite (booleano)
- order (ordem de exibição)
```

#### 2. `goal_lists` - Listas/Colunas
```sql
- board_id (FK para goal_boards)
- name (nome da lista)
- color (cor da lista)
- order (ordem dentro do board)
```

#### 3. `goals` - Metas (Principal)
```sql
- list_id (FK para goal_lists)
- user_id (FK para users)
- title (título da meta)
- description (descrição detalhada)
- periodo (enum: diario, semanal, mensal, trimestral, semestral, anual, livre)
- prioridade (enum: baixa, media, alta, urgente)
- data_inicio, data_limite
- valor_meta, valor_atual (financeiro)
- progresso (0-100)
- cofrinho_id (FK opcional para cofrinhos) - INTEGRAÇÃO
- category_id (FK opcional para category) - INTEGRAÇÃO
- labels (JSON array)
- is_archived, completed_at
- order (ordem dentro da lista)
```

#### 4. `goal_checklists` - Checklists
```sql
- goal_id (FK para goals)
- title (título do checklist)
- order (ordem)
```

#### 5. `goal_checklist_items` - Itens de Checklist
```sql
- checklist_id (FK para goal_checklists)
- content (texto do item)
- is_completed (booleano)
- order (ordem)
```

#### 6. `goal_comments` - Comentários
```sql
- goal_id (FK para goals)
- user_id (FK para users)
- content (texto do comentário)
```

#### 7. `goal_attachments` - Anexos
```sql
- goal_id (FK para goals)
- file_name, file_path, file_type, file_size
```

#### 8. `goal_activities` - Log de Atividades
```sql
- goal_id (FK para goals)
- user_id (FK para users)
- action (tipo de ação)
- description (descrição)
```

---

## 🔗 Integrações

### 💰 Integração com Cofrinhos
Quando uma meta é vinculada a um cofrinho:
- **Progresso Automático**: `valor_atual` atualizado com base no saldo do cofrinho
- **Cálculo**: Entradas - Saídas do cofrinho
- **Visual**: Ícone de cofrinho no card
- **Atualização**: Automática via método `updateProgressoFromCofrinho()`

### 🏷️ Integração com Categorias
Quando uma meta é vinculada a uma categoria:
- **Rastreamento de Gastos**: Acompanha despesas da categoria
- **Controle de Limite**: Pode definir limite de gastos
- **Visual**: Ícone de categoria no card
- **Relatórios**: Análise de gastos vs meta

---

## 🎨 Design e Interface

### Paleta de Cores (Estilo Trello)
- **Azul**: `#0079BF` (Padrão)
- **Verde**: `#61BD4F` (Sucesso)
- **Amarelo**: `#F2D600` (Atenção)
- **Laranja**: `#FF9F1A` (Médio)
- **Vermelho**: `#EB5A46` (Urgente)
- **Roxo**: `#C377E0` (Pessoal)
- **Ciano**: `#00C2E0` (Profissional)
- **Verde Claro**: `#51E898` (Saúde)

### Componentes Visuais
- **Header Moderno**: Gradiente com glassmorphism
- **Cards**: Sombra, hover effects, progress bars
- **Badges**: Prioridade, período, status
- **Ícones**: Bootstrap Icons
- **Animações**: Transições suaves, drag feedback

---

## 🚀 Rotas Disponíveis

```php
GET /goals                    - Dashboard de metas
GET /goals/board/{boardId}    - Quadro Kanban específico
```

### Navegação
- **Sidebar**: Link "Metas e Objetivos" com ícone `bi-bullseye`
- **Cor da Sidebar**: Gradiente roxo/indigo quando ativo
- **Breadcrumbs**: Dashboard > Metas e Objetivos

---

## 🧩 Componentes Livewire

### 1. `GoalsDashboard` (`/goals`)
**Arquivo**: `app/Livewire/Goals/GoalsDashboard.php`

**Métodos principais**:
- `loadDashboardData()` - Carrega todas as estatísticas
- `createDefaultBoardsIfNeeded()` - Cria boards padrão para novos usuários
- `getTipoLabel()`, `getDefaultColor()`, `getTipoIcon()` - Helpers de formatação

**Dados computados**:
- `$stats` - KPIs gerais
- `$boards` - Lista de quadros do usuário
- `$urgentGoals` - Metas urgentes (10 mais próximas do vencimento)
- `$goalsByPeriodo` - Agrupamento por período
- `$goalsByPrioridade` - Agrupamento por prioridade
- `$progressStats` - Distribuição de progresso (0-25%, 26-50%, etc)
- `$recentActivities` - Últimas 15 atividades

### 2. `GoalsBoard` (`/goals/board/{id}`)
**Arquivo**: `app/Livewire/Goals/GoalsBoard.php`

**Métodos principais**:
- `loadBoard()` - Carrega board, listas e goals
- `loadFormOptions()` - Carrega cofrinhos e categorias para selects
- `openCreateListModal()`, `createList()` - Criação de listas
- `openCreateGoalModal()`, `createGoal()` - Criação de metas
- `moveGoal($goalId, $newListId, $newOrder)` - Drag & drop
- `deleteGoal()`, `archiveGoal()`, `completeGoal()` - Ações sobre metas

**Listeners**:
- `goalMoved` - Evento de movimentação
- `refreshBoard` - Recarregar board

---

## 📦 Models Eloquent

### `GoalBoard`
**Relationships**:
- `belongsTo(User)`
- `hasMany(GoalList)`

**Scopes**:
- `favorites()` - Boards favoritos
- `byTipo($tipo)` - Filtrar por tipo

### `GoalList`
**Relationships**:
- `belongsTo(GoalBoard)`
- `hasMany(Goal)`

### `Goal` (Principal - 190+ linhas)
**Relationships**:
- `belongsTo(GoalList, User, Cofrinho, Category)`
- `hasMany(GoalChecklist, GoalComment, GoalAttachment, GoalActivity)`

**Scopes**:
- `active()` - Não arquivadas e não concluídas
- `archived()` - Arquivadas
- `byPrioridade($p)` - Filtrar por prioridade
- `vencendoEm($dias)` - Vencendo em X dias
- `atrasadas()` - Data limite passada

**Accessors**:
- `progresso_percentual` - Calcula % de 0-100
- `is_completed` - Se completed_at está preenchido
- `is_atrasada` - Se passou da data limite

**Métodos de Negócio**:
- `updateProgressoFromCofrinho()` - Sincroniza com saldo do cofrinho
- `calculateProgressoFromChecklists()` - Calcula com base em checklists
- `markAsCompleted()` - Marca como concluída
- `logActivity($action, $description)` - Registra atividade

### Outros Models
- `GoalChecklist` - Com cálculo automático de progresso
- `GoalChecklistItem` - Com event listener que atualiza goal
- `GoalComment` - Comentários simples
- `GoalAttachment` - Com auto-delete de arquivos
- `GoalActivity` - Com helpers de ícones e cores

---

## 🎯 Fluxo de Uso

### Primeiro Acesso
1. Usuário acessa `/goals`
2. Sistema detecta ausência de boards
3. Cria automaticamente 2 boards padrão:
   - **Metas Financeiras** (4 listas: Planejamento, Em Andamento, Próximo da Meta, Concluídas)
   - **Desenvolvimento Pessoal** (3 listas: Novos Hábitos, Em Progresso, Concluídas)

### Criar Meta
1. Acessa um board (`/goals/board/1`)
2. Clica em "Adicionar Meta" em uma lista
3. Preenche formulário:
   - Título * (obrigatório)
   - Descrição
   - Período (diário a anual)
   - Prioridade (baixa a urgente)
   - Datas (início e limite)
   - Valor da meta (opcional)
   - **Vincular a Cofrinho** (progresso automático)
   - **Vincular a Categoria** (rastreamento de gastos)
4. Sistema cria meta e registra atividade

### Mover Meta (Drag & Drop)
1. Clica e arrasta um card de meta
2. Solta em outra lista
3. JavaScript (SortableJS) captura evento
4. Livewire chama `moveGoal()`
5. Backend atualiza `list_id` e `order`
6. Registra atividade "Meta movida para lista X"

### Progresso Automático (Cofrinho)
1. Meta vinculada a cofrinho
2. Usuário faz transação no cashbook para o cofrinho
3. Cofrinho atualiza saldo (entradas - saídas)
4. Goal Model observa mudanças
5. Método `updateProgressoFromCofrinho()` executa
6. Calcula: `($cofrinhoSaldo / $valorMeta) * 100`
7. Atualiza `valor_atual` e `progresso`

---

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 10, Livewire 3, Eloquent ORM
- **Frontend**: Blade Templates, TailwindCSS 3, Alpine.js
- **Drag & Drop**: SortableJS 1.15
- **Ícones**: Bootstrap Icons
- **Database**: MySQL (8 tabelas relacionadas)
- **Autenticação**: Laravel Auth

---

## 📊 Casos de Uso

### 1. Meta Financeira com Cofrinho
```
Título: "Economizar R$ 10.000 para viagem"
Período: Anual
Prioridade: Alta
Valor Meta: R$ 10.000,00
Cofrinho: "Viagem Europa"

Progresso: Automático baseado no saldo do cofrinho
- Depósito R$ 1.000 → Progresso: 10%
- Depósito R$ 2.000 → Progresso: 30%
- Retirada R$ 500 → Progresso: 25%
```

### 2. Meta de Controle de Gastos
```
Título: "Reduzir gastos com alimentação em 20%"
Período: Mensal
Prioridade: Média
Categoria: "Alimentação"

Sistema rastreia gastos da categoria automaticamente
Alerta quando ultrapassar limite definido
```

### 3. Meta Pessoal com Checklists
```
Título: "Ler 12 livros este ano"
Período: Anual
Prioridade: Baixa

Checklist:
☑ Janeiro - "Sapiens" (completo)
☑ Fevereiro - "1984" (completo)
☐ Março - "..." (pendente)
...

Progresso: 2/12 = 16.67%
```

---

## 🔄 Sistema de Atividades

Todas as ações são registradas em `goal_activities`:
- ✅ Meta criada
- 🔄 Meta movida para lista X
- ✏️ Meta editada
- 📎 Anexo adicionado
- 💬 Comentário adicionado
- ☑️ Checklist item marcado
- 🏆 Meta concluída
- 📦 Meta arquivada

Cada atividade tem:
- **Ícone**: Font Awesome mapeado por ação
- **Cor**: Código de cor por tipo de ação
- **Timestamp**: created_at
- **Usuário**: user_id

---

## 🎨 Personalização

### Cores de Board
Usuário pode escolher cor de fundo do board:
- Verde (#10B981) - Financeiro
- Laranja (#F59E0B) - Pessoal
- Azul (#3B82F6) - Profissional
- Vermelho (#EF4444) - Saúde
- Roxo (#8B5CF6) - Estudos

### Cores de Lista
8 opções de cores para listas (padrão Trello)

### Labels em Metas
Array JSON de labels personalizadas com cor e nome

---

## 📱 Responsividade

- **Desktop**: Layout completo com scrolls horizontais e verticais
- **Tablet**: Grid adaptativo, cards menores
- **Mobile**: Coluna única, navegação otimizada

---

## 🔐 Segurança

- Todas as queries filtradas por `user_id`
- Validação de propriedade antes de modificar
- CSRF protection (Livewire automático)
- Foreign keys com `onDelete('cascade')`

---

## 🚀 Próximas Melhorias (Futuras)

- [ ] Notificações push para metas vencendo
- [ ] Compartilhamento de boards entre usuários
- [ ] Templates de metas prontos
- [ ] Exportação de relatórios em PDF
- [ ] Gamificação (badges, conquistas)
- [ ] Calendário integrado de metas
- [ ] Gráficos avançados (ApexCharts)
- [ ] Modo offline (PWA)

---

## 📄 Licença

Este sistema faz parte do FlowManager. Todos os direitos reservados.

---

## 👨‍💻 Desenvolvido com ❤️ usando:
- Laravel + Livewire + TailwindCSS
- Inspirado no Trello
- Integrado com sistema de Cofrinhos e Categorias
