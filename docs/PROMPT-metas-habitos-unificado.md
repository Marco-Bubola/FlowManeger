# PROMPT — Reestruturar "Metas, Objetivos e Hábitos" em um módulo único e completo

> Use este documento como **briefing/prompt** para implementar a reestruturação.
> Stack do projeto: **Laravel + Livewire 3 + Alpine.js + Tailwind**, MySQL, tema "Midnight Galaxy".
> O sistema de notificações global é `window.notify(type,message)` / store Alpine `fmNotify`.

---

## 1. Contexto atual (NÃO recriar do zero — evoluir o que existe)

Hoje existem **duas áreas separadas**:

- `/goals` (`App\Livewire\Goals\GoalsDashboard`) — estilo Kanban:
  `GoalBoard → GoalList → Goal` com `GoalChecklist`/`GoalChecklistItem`, `GoalComment`,
  `GoalAttachment`, `GoalActivity`, labels, prioridade, datas, `cofrinho_id`
  (progresso financeiro automático via cashbooks), `category_id`.
- `/daily-habits` (`App\Livewire\DailyHabits\DailyHabitsDashboard`) — `DailyHabit`,
  `DailyHabitCompletion`, `DailyHabitStreak` (check do dia, streak, taxa, lembrete).

Serviços existentes: `GoalService`, `HabitService`, `AchievementService`.
Já existe pivô `goal_habit` (com coluna `peso`) ligando metas a hábitos.

**Objetivo desta reestruturação:** unir tudo em **um único módulo "Conquistas"**
(`/conquistas` ou manter `/goals` como raiz) com navegação por abas, integrando
Metas ↔ Hábitos ↔ Financeiro ↔ Gamificação, com visão diária, semanal e por board.

---

## 2. Visão do módulo unificado

Uma área única chamada **"Metas & Hábitos"** (ou "Centro de Conquistas") com **abas**:

1. **Hoje** (Home) — foco do dia: hábitos a marcar, metas vencendo, progresso do dia,
   streak geral, "score do dia". É a tela que abre por padrão.
2. **Metas (Kanban)** — os boards atuais, melhorados (drag-and-drop, filtros, labels).
3. **Hábitos** — grade de hábitos + heatmap anual estilo GitHub + estatísticas.
4. **Calendário** — visão mensal unindo metas (vencimentos) e hábitos (dias concluídos).
5. **Progresso & Insights** — gráficos de evolução, taxa de adesão, correlação
   hábito→meta, projeções.
6. **Conquistas** — gamificação (badges, níveis, XP, troféus) já existente, expandida.

Tudo respeitando dark/light e responsividade (mesmos breakpoints do resto do app).

---

## 3. Modelo de dados — ajustes e novas tabelas

Manter as tabelas atuais e ADICIONAR:

### 3.1 Unificar conceito de "vínculo meta↔hábito"
- Já existe `goal_habit (goal_id, daily_habit_id, peso)`. Usar para: ao completar um
  hábito, **incrementar automaticamente o progresso da meta vinculada** proporcional ao `peso`.

### 3.2 Hábitos — enriquecer
Adicionar colunas em `daily_habits`:
- `type` enum: `boolean` (fez/não fez), `quantity` (ex.: beber 8 copos), `duration` (ex.: 30 min).
- `target_value` (meta numérica diária quando `quantity`/`duration`).
- `unit` (copos, min, km...).
- `frequency_type` enum: `daily`, `weekly`, `specific_days`, `times_per_week`.
- `frequency_days` JSON (ex.: `["mon","wed","fri"]`).
- `start_date`, `end_date` (hábito com prazo).
- `is_archived` boolean.
Em `daily_habit_completions` adicionar `value` (quantidade feita) e `note`.

### 3.3 Metas — enriquecer
- `tipo_meta` enum: `checklist`, `numeric` (valor_meta/valor_atual), `financeira`
  (cofrinho), `habito` (progresso vem dos hábitos vinculados).
- `milestones` (nova tabela `goal_milestones`: goal_id, titulo, valor_alvo, data_alvo, atingido_em).
- `reminders` (nova tabela `goal_reminders`: goal_id, datetime, canal).

### 3.4 Gamificação (expandir Achievement)
- Tabela `user_levels` (user_id, xp, level, current_streak, best_streak).
- Regras de XP: completar hábito (+10), manter streak (+bônus), concluir meta (+50),
  concluir milestone (+25). Centralizar no `AchievementService`/novo `GamificationService`.

> Forneça migrations idempotentes e atualize os `$fillable`/`$casts`/relacionamentos dos models.

---

## 4. Telas e UX (detalhado)

### Aba "Hoje"
- **Anel de progresso do dia** (% de hábitos concluídos hoje) + streak global.
- Lista de **hábitos de hoje** com 1 toque para concluir (boolean) ou stepper (quantity/duration),
  animação de check + XP "+10" que sobe (igual animação de "Salvo!" feita no bulk-edit).
- **Metas vencendo em 7 dias** (cards compactos com barra de progresso).
- **Frase/insight do dia** (ex.: "Você está há 12 dias mantendo 'Ler 20 min' 🔥").

### Aba "Metas (Kanban)"
- Drag-and-drop de cards entre listas e reordenação (persistir `order`/`list_id`).
- Filtros: prioridade, label, board, categoria, status (atrasada/vencendo/concluída).
- Card abre **modal de detalhe** com: descrição, checklist (progress bar), comentários,
  anexos, atividades (timeline), hábitos vinculados, vínculo com cofrinho.
- Quick-add de meta inline em cada lista.

### Aba "Hábitos"
- Grade de hábitos com check rápido.
- **Heatmap anual** (estilo GitHub contributions) por hábito e geral.
- Estatísticas: streak atual/recorde, taxa 7/30/90 dias, melhor dia da semana.
- Reordenar via drag-and-drop. Arquivar/pausar hábito.

### Aba "Calendário"
- Mês com pontinhos por dia: verde (hábitos completos), âmbar (parcial), e marcadores
  de vencimento de metas. Clique no dia abre o resumo daquele dia.

### Aba "Progresso & Insights"
- Gráfico de linha: adesão de hábitos ao longo do tempo.
- Gráfico de barras: metas concluídas por mês.
- "Correlação": metas que mais avançam quando o hábito X é mantido.
- Projeção: "no ritmo atual, a meta Y conclui em DD/MM".

### Aba "Conquistas"
- Grid de badges (bloqueadas/desbloqueadas), barra de XP/nível, troféus por streak.

---

## 5. Regras de negócio (lógica central)

1. **Hábito → Meta:** completar hábito vinculado soma `peso` ao `valor_atual`/`progresso`
   da(s) meta(s) do tipo `habito`. Desfazer reverte.
2. **Cofrinho → Meta financeira:** manter o cálculo automático já existente
   (`updateProgressoFromCofrinho`), agora disparado por evento ao mudar o cashbook.
3. **Checklist → Meta:** `calculateProgressoFromChecklists` ao marcar item.
4. **Streak:** recalcular no `complete/uncomplete` (já existe em `HabitService::updateStreak`).
5. **XP/Nível:** disparar via eventos de domínio (`HabitCompleted`, `GoalCompleted`,
   `MilestoneReached`) ouvidos por um `GamificationListener`.
6. **Lembretes:** job agendado (scheduler) verifica `reminder_time`/`goal_reminders` e
   dispara notificação (web push e/ou e-mail).
7. **Idempotência:** completar o mesmo hábito 2x no mesmo dia não duplica (constraint
   `unique(habit_id, user_id, completion_date)`).

---

## 6. Arquitetura sugerida

- **Um componente Livewire "pai"** `ConquistasHub` com as abas; cada aba é um
  sub-componente (`Howhen`, `GoalsBoard`, `HabitsGrid`, `CalendarView`, `InsightsView`,
  `AchievementsView`) — usar `wire:model`/eventos para comunicação.
- **Camada de serviço** já existente (`GoalService`, `HabitService`) + novo
  `GamificationService` e `InsightsService` (cálculos pesados, cacheados).
- **Eventos/Listeners** para gamificação e atualização de progresso (desacoplar).
- **Cache** das estatísticas (heatmap, insights) por usuário/dia (`Cache::remember`).
- **API interna** (opcional) para o app mobile futuro: endpoints REST de hábitos/metas.

---

## 7. Critérios de aceite

- [ ] Uma única entrada no menu ("Metas & Hábitos") substitui as duas atuais
      (com redirect das rotas antigas).
- [ ] Marcar hábito atualiza streak, XP e progresso de metas vinculadas em tempo real.
- [ ] Kanban com drag-and-drop persistente.
- [ ] Heatmap anual e calendário funcionando.
- [ ] Insights com pelo menos 3 gráficos reais.
- [ ] Notificações de lembrete disparando via scheduler.
- [ ] Dark/light + responsivo (iPhone, iPad retrato/paisagem, 1920, ultrawide).
- [ ] Sem N+1 (eager loading), estatísticas cacheadas.

---

## 8. Plano de implementação em fases

1. **Fase 0 — Migrations & models** (campos novos, tabelas `goal_milestones`,
   `goal_reminders`, `user_levels`; atualizar fillable/casts/relations).
2. **Fase 1 — Hub + aba "Hoje"** (esqueleto de abas, foco do dia, check de hábito com XP).
3. **Fase 2 — Kanban melhorado** (drag-and-drop, modal de detalhe, filtros).
4. **Fase 3 — Hábitos** (heatmap, estatísticas, tipos quantity/duration).
5. **Fase 4 — Calendário + Insights** (gráficos, projeções).
6. **Fase 5 — Gamificação completa** (XP/níveis/badges via eventos) + lembretes/scheduler.
7. **Fase 6 — Polimento** (dark/light, responsivo, cache, testes).

---

## 9. Análise de bibliotecas / APIs recomendadas

### Frontend (já no stack: Livewire + Alpine + Tailwind)
| Necessidade | Biblioteca | Por quê |
|---|---|---|
| **Drag-and-drop** (Kanban, reordenar hábitos) | **SortableJS** (`sortablejs`) | Leve, integra com Alpine via `x-sort` (Alpine v3.13+ tem `x-sort` nativo) ou wrapper; persiste ordem por evento Livewire. |
| **Gráficos** (insights, progresso) | **ApexCharts** (já incluído no projeto via CDN) | Já está no `head`; linha/barra/radial/heatmap prontos. Alternativa: **Chart.js**. |
| **Heatmap anual** (estilo GitHub) | **Cal-Heatmap** ou heatmap do **ApexCharts** | Cal-Heatmap é dedicado a contribuições por dia; ApexheatMap evita nova dependência. |
| **Calendário** | **FullCalendar** (`@fullcalendar/core`) | Visão mês/semana, eventos de metas+hábitos, drag de datas. |
| **Animações de XP/confete** | **canvas-confetti** + animações CSS (já criadas) | Feedback de conquista ao concluir meta/hábito. |
| **Date picker** | **Flatpickr** (já incluído no projeto) | Reaproveitar; já tem locale pt. |
| **Ícones** | **Bootstrap Icons** (já no projeto) | Manter consistência. |

### Backend / Laravel
| Necessidade | Pacote | Por quê |
|---|---|---|
| **Eventos/Gamificação** | Eventos nativos do Laravel + Listeners | Desacopla XP/streak/badges. Sem dependência externa. |
| **Lembretes agendados** | **Laravel Scheduler** (nativo) + Queue | `php artisan schedule:work`; dispara notificações de hábitos/metas. |
| **Notificações** | **Laravel Notifications** (database + mail) + **Web Push** (`laravel-notification-channels/webpush`) | Lembrete no navegador/app. |
| **Permissões/equipe** | Já existe `visibleTeamUserIds` no projeto | Reusar para metas compartilhadas. |
| **Relatórios PDF** (export de progresso) | **barryvdh/laravel-dompdf** (provável já usado nos exports) | Exportar relatório mensal de hábitos/metas. |
| **Cache de insights** | `Cache` nativo (Redis se disponível) | Heatmap/insights por usuário/dia. |

### APIs externas (opcionais, agregam valor)
| Ideia | API | Uso |
|---|---|---|
| **Frases motivacionais do dia** | ZenQuotes / Quotable (`api.quotable.io`) | Insight diário na aba "Hoje". |
| **Integração com calendário pessoal** | **Google Calendar API** | Exportar vencimentos de metas para o Google Agenda. |
| **Push real no celular** | **Firebase Cloud Messaging (FCM)** | Lembretes mesmo com app fechado (futuro app mobile). |
| **IA de sugestão de metas/hábitos** | **Claude API (Anthropic)** | "Sugira hábitos para atingir a meta X"; quebrar meta em milestones automaticamente. |

### Recomendação mínima (para começar já, sem inflar dependências)
- **SortableJS** (drag-and-drop) + **ApexCharts** (já presente) para gráficos e heatmap
  + **FullCalendar** (calendário) + **canvas-confetti** (feedback).
- Backend 100% nativo (Eventos + Scheduler + Notifications). Web Push e Claude API como
  incrementos numa fase posterior.

---

## 10. Observações de implementação no projeto

- Reaproveitar o **sistema de notificações** já refeito (`window.notify`, store `fmNotify`).
- Reaproveitar **animação de "Salvo!"** e cores do tema **Midnight Galaxy**.
- Registrar stores Alpine com o **padrão robusto** (registra se `window.Alpine` existe,
  senão em `alpine:init`) — necessário por causa do `wire:navigate` (SPA).
- Manter rotas antigas com **redirect 301** para as novas abas.
- Eager loading em tudo (sem N+1); paginar listas longas; cachear heatmap/insights.
