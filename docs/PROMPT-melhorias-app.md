# FlowManager — Análise Geral + Prompt de Melhorias e Ideias

> Documento de diagnóstico e roadmap. Pode ser usado como **briefing/prompt** para evoluir o app.
> Gerado a partir de uma varredura do código (Laravel 12 + Livewire 3 + Alpine + Tailwind, MySQL).

---

## 1. O que é o app hoje (panorama)

FlowManager é um **ERP/CRM SaaS** robusto para pequenos negócios, com **64 models**, **~38 services**, **187 rotas** e múltiplos módulos:

| Domínio | Módulos |
|---|---|
| **Produtos** | CRUD, edição em massa, upload em lote (PDF/CSV via IA), kits, componentes, imagens |
| **Vendas** | Vendas, itens, parcelas, pagamentos |
| **Financeiro** | Cashbook, bancos, faturas (extração por IA Gemini), cofrinhos, lançamentos recorrentes, orçamentos |
| **Clientes** | CRM + Portal do Cliente (login Google) + solicitações de orçamento |
| **Consórcios** | Participantes, sorteios, pagamentos, contemplações, notificações |
| **Metas & Hábitos** | Goals (Kanban), hábitos diários, conquistas, XP/níveis (novo hub unificado) |
| **Marketplaces** | Mercado Livre (completo: publicações, pedidos, perguntas, mensagens, reputação, mediações, promoções, webhooks) e Shopee |
| **SaaS** | Planos, assinaturas, gateways (Stripe, PagSeguro, MercadoPago, Manual) |
| **Equipes** | Times, convites, membros, segmentos, permissões por módulo |
| **Dashboards** | Métricas financeiras e de produtos |

**Stack/dependências:** Laravel 12, Livewire 3 (+ Flux, Volt), Socialite (Google), dompdf, maatwebsite/excel, smalot/pdfparser, **Gemini** (IA p/ faturas) e **Claude** (IA p/ metas — novo).

---

## 2. Avaliação de saúde (o que está bom x frágil)

### ✅ Pontos fortes
- Cobertura funcional enorme e coesa para o nicho.
- Camada de **services** bem separada (bom para evoluir).
- Integrações de marketplace maduras.
- Já usa IA (Gemini) para automação de lançamentos financeiros.
- UI temática consistente (Midnight Galaxy) e responsiva por dispositivo.

### ⚠️ Pontos frágeis (encontrados/corrigidos nesta sessão)
- **Notificações duplicadas** (3 listeners) — corrigido (store única + dedupe).
- **OAuth ML**: faltava PKCE (erro 400) + "refresh storm" derrubando o token — corrigido.
- **Hábitos**: código morto quebrando o toggle + coluna errada (`completion_count`) — corrigido.
- **Favicons/ícones** de 600KB–2MB — substituídos por PNGs leves.
- **`alert()/confirm()` nativos** espalhados — substituídos por modal/toast global.

### 🔴 Riscos que ainda merecem atenção
- **Baixa cobertura de testes** (~11 arquivos para 64 models) → regressões fáceis.
- **Segredos no `.env`** versionado/visível (ex.: `ML_CLIENT_SECRET`) e **rota de debug** pública (`/mercadolivre/debug`).
- **Logs com dados sensíveis** (tokens parciais, payloads de request).
- **Deploy**: `public/build` e `public/hot` ignorados → exige `npm run build` no host; risco de assets quebrados.
- **N+1 / queries pesadas** em telas com muitos cards (produtos, ML).
- **Dois provedores de IA** (Gemini + Claude) sem camada unificada.

---

## 3. PROMPT DE MELHORIAS — por categoria

### 3.1 Arquitetura & Código
- Criar uma **camada de IA unificada** (`AiProviderInterface`) com drivers `gemini` e `anthropic`, escolhidos por config. Hoje há `GeminiClient` + `AiSuggestionService` separados.
- Padronizar **eventos de domínio** (`SaleCreated`, `GoalCompleted`, `HabitCompleted`, `MlOrderReceived`) + listeners — desacopla gamificação, notificações e sync.
- Mover regras repetidas dos componentes Livewire para os **services** (ex.: toggle de hábito agora existe em 2 lugares; centralizar).
- **Form Requests / DTOs** para validações de criação/edição (produtos, vendas, metas).
- Remover **rotas de debug** de produção (`/mercadolivre/debug`) ou protegê-las por `gate:admin`.

### 3.2 Performance
- **Eager loading** em todas as listagens (auditar N+1 com Laravel Debugbar/Telescope).
- **Cache** de métricas de dashboard e estatísticas (heatmap, KPIs) por usuário/dia (`Cache::remember`).
- **Índices** de banco nas colunas mais filtradas (`user_id+status`, `data_vencimento`, `completion_date`, `ml_item_id`).
- **Lazy loading** de componentes Livewire pesados (`#[Lazy]`) e de imagens (`loading="lazy"`).
- Consolidar os múltiplos arquivos CSS responsivos (build único minificado via Vite).
- Fila (**queue**) para tarefas lentas: sync ML/Shopee, geração de PDF, extração de faturas por IA.

### 3.3 Segurança
- Tirar segredos do controle de versão; usar **secret manager** do host e `.env.example` sem valores.
- **Mascarar/limpar logs** (não logar tokens, payloads completos).
- **Rate limiting** nas rotas de auth, webhooks e endpoints AJAX.
- **Validar assinatura** dos webhooks (ML/Shopee) com `ML_WEBHOOK_SECRET`.
- Revisar **policies/gates** por módulo (já há `visibleTeamUserIds`) — garantir que toda query escopa por usuário/time.
- **2FA** opcional para login (Fortify/Sanctum já no ecossistema Laravel).
- Forçar **HTTPS** e cookies `Secure`/`SameSite` (já há forceScheme em produção).

### 3.4 UX / UI
- **Command palette** (Ctrl+K) para navegação rápida entre módulos.
- **Estados vazios** ilustrados e **skeletons** de carregamento em todas as listas.
- **Onboarding** guiado para o primeiro acesso (tour por módulo).
- **Busca global** unificada (produtos, clientes, vendas, metas).
- **Atalhos de teclado** e ações em massa consistentes.
- **Acessibilidade**: foco visível, ARIA, contraste (rodar auditoria WCAG).
- **PWA**: instalar como app, ícones (favicons já corrigidos), offline básico.

### 3.5 Qualidade & Testes
- **Pest/PHPUnit**: testes de feature para fluxos críticos (criar venda, OAuth ML, salvar produto, completar hábito).
- **Testes Livewire** (`Livewire::test`) para os componentes principais.
- **CI** (GitHub Actions): lint (Pint), análise estática (**Larastan/PHPStan**), testes, build Vite.
- **Pre-commit hooks** (Pint + testes rápidos).

### 3.6 DevOps / Deploy
- Pipeline de deploy com: `composer install --no-dev`, `npm ci && npm run build`, `migrate --force`, `config:cache`, `route:cache`, `view:cache`, `storage:link`.
- Garantir que `public/hot` **nunca** vá para produção.
- **Healthcheck** (`/up` do Laravel 11+) + monitoração (uptime).
- **Backups** automáticos do MySQL e do storage.

### 3.7 Observabilidade
- **Laravel Telescope** (dev) e **Sentry** (prod) para erros/performance.
- Tela de **logs/sync** dos marketplaces já existe (`MercadoLivreSyncLog`) — expor um painel de saúde das integrações (último sync, erros, tokens a expirar).

---

## 4. Ideias de novas funcionalidades (alto valor)

1. **Central de Integrações**: status unificado de ML + Shopee (conectado, token, último sync, erros) num só lugar, com reconexão em 1 clique.
2. **Relatórios & BI**: exportações agendadas (PDF/Excel) de vendas, financeiro e estoque; gráficos comparativos por período.
3. **Alertas inteligentes**: estoque baixo, metas atrasadas, contas a vencer, perguntas do ML sem resposta — via notificação + e-mail + (futuro) WhatsApp.
4. **Precificação assistida por IA**: sugerir preço de venda com base em custo, margem e concorrência do ML.
5. **Respostas automáticas no ML** (perguntas/mensagens) com sugestão da IA + aprovação humana.
6. **App mobile / PWA** com foco em: marcar hábitos, ver vendas do dia, responder ML.
7. **Conciliação financeira**: casar pagamentos de vendas/ML com lançamentos do cashbook automaticamente.
8. **Multi-loja / multi-empresa** dentro do mesmo time.
9. **Automação no estilo "se isto, então aquilo"** (ex.: venda no ML → baixa estoque + lança no cashbook + atualiza meta).
10. **Catálogo público / loja** do cliente (já há `portal-catalog-layout`) — evoluir para mini e-commerce.

---

## 5. Oportunidades de IA (já há base com Gemini)

- **Unificar provedores** (Gemini + Claude) atrás de uma interface; escolher por tarefa/custo.
- **Importação de produtos por foto** (visão): tirar foto → IA extrai nome, marca, categoria.
- **Resumo do dia/semana** do negócio (vendas, financeiro, metas) gerado por IA na home.
- **Assistente conversacional** ("quanto vendi esse mês?", "quais metas estão atrasadas?") com tool-calling sobre os dados.
- **Categorização automática** de produtos e lançamentos (já existe `*CategoryLearning` — evoluir para ML real).

---

## 6. Roadmap priorizado

### 🟢 Quick wins (1–3 dias cada)
- Remover/proteger rota de debug do ML; limpar logs sensíveis.
- Adicionar índices de banco nas colunas mais filtradas.
- Eager loading nas 3–4 telas mais pesadas.
- Painel de saúde das integrações (status + token a expirar).
- `.env.example` limpo + checklist de deploy documentado.

### 🟡 Médio prazo (1–2 semanas)
- Camada de IA unificada + 1 feature nova (resumo do dia ou precificação).
- Eventos/Listeners para gamificação e automações.
- Cobertura de testes dos fluxos críticos + CI.
- Fases 2–4 do módulo Metas & Hábitos (Kanban drag-drop, heatmap, calendário, insights).

### 🔵 Estratégico (1+ mês)
- Central de Integrações + automações "se-então".
- PWA/mobile.
- BI/relatórios agendados.
- Conciliação financeira automática.

---

## 7. Bibliotecas/APIs sugeridas

| Necessidade | Opção |
|---|---|
| Erros/perf em produção | **Sentry** (`sentry/sentry-laravel`) |
| Debug/inspeção dev | **Laravel Telescope**, **Debugbar** |
| Análise estática | **Larastan/PHPStan**, **Laravel Pint** |
| Testes | **Pest** |
| Filas/cache | **Redis** (queue + cache + locks) |
| Drag-and-drop | **SortableJS** / Alpine `x-sort` |
| Gráficos | **ApexCharts** (já no projeto) |
| Calendário | **FullCalendar** |
| Push | **laravel-notification-channels/webpush** + **FCM** |
| IA | **Gemini** (já) + **Claude/Anthropic** (já) atrás de interface única |
| Excel/PDF | **maatwebsite/excel**, **dompdf** (já) |
| Busca | **Laravel Scout** (+ Meilisearch) para busca global |

---

## 8. Observações finais
- Reaproveitar o que já foi padronizado nesta sessão: **toast/modal global**, **animações**, **tema Midnight Galaxy**, **registro robusto de stores Alpine** (compatível com `wire:navigate`).
- Tratar **deploy no host (Hostoo)** como item de primeira classe: build Vite + caches + `.env` correto (inclui `ML_REDIRECT_URI` e `ANTHROPIC_API_KEY`).
