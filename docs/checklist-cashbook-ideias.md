## Checklist — Cashbook (página: `resources/views/livewire/cashbook/cashbook-index.blade.php`)

Breve: checklist das funcionalidades já visíveis no template, itens a verificar e ideias de funcionalidades com ações recomendadas.

### ✅ Implementadas
- [x] UI de calendário com navegação por mês (botões "previous" / "next") e selects de mês/ano — Ação: confirmar métodos Livewire (`changeMonth`).
- [x] Destaque para o dia atual e seleção de data (`selectDay`) com filtro — Ação: testar `selectDay` e comportamento do filtro.
- [x] Cards de resumo (Receitas / Despesas / Saldo) responsivos com Tailwind — Ação: validar valores calculados no backend.
- [x] Lista de transações agrupadas por categoria com painel expansível (Alpine + collapse) — Ação: checar compatibilidade sem JS.
- [x] Barra de progresso / gráfico por categoria (barras percentuais) — Ação: validar cálculos de porcentagem.
- [x] Modal de confirmação de exclusão com animação e Livewire (`confirmDelete`, `deleteTransaction`, `cancelDelete`) — Ação: testar fluxo de exclusão (soft/hard).
- [x] Dark mode persistente via `localStorage` e toggle (`toggleDarkMode`).
- [x] Atalhos de teclado (Ctrl+N, ESC, Ctrl+R) e notificações toast — Ação: testar conflitos com atalhos do navegador.
- [x] Auto-refresh a cada 5 minutos chamando `loadData` do Livewire.
- [x] Feedback visual (toasts, animações, badges de pendente) e pequenos tooltips.

### ⚠️ Em progresso / precisa verificar
- [ ] Validar existência/implementação dos métodos Livewire mencionados: `changeMonth`, `selectDay`, `clearDateFilter`, `loadData`, `confirmDelete`, `deleteTransaction`, `cancelDelete`.  
  - Ação: revisar componente Livewire correspondente (`app/Http/Livewire/...`) e adicionar testes unitários.
- [ ] Testar performance com grande volume de transações (UI lag, número de queries).  
  - Ação: habilitar paginação e/ou virtual scroll; profile das queries (Eloquent queries, N+1).
- [ ] Verificar persistência do modo escuro entre rotas/usuarios e comportamento sem JS.  
  - Ação: garantir fallback server-side para apresentação inicial.
- [ ] Acessibilidade do modal (foco, trap focus, `aria-*`) e navegação por teclado consistente.  
  - Ação: adicionar foco inicial, `aria-modal`, e teste com leitor de tela.
- [ ] Internacionalização: meses usando `Carbon::locale('pt_BR')` — confirmar configuração global do locale.  
  - Ação: checar `config/app.php` e `AppServiceProvider`.
- [ ] Segurança / autorização para edição/exclusão (políticas) — Ação: adicionar gates/policies e testes.

### 🧠 Ideias de funcionalidades (com ações sugeridas)
- [ ] Exportar transações (CSV / PDF) — Ação: adicionar rota/controller `CashbookExportController`, método Livewire `exportCsv()` e botão UI; usar `maatwebsite/excel` ou `dompdf` para PDF.
- [ ] Busca global e filtros avançados (por descrição, valor, cliente, conta, tag, categoria, recorrência) — Ação: inserir inputs de filtro no topo, passar parâmetros para a query Eloquent via Livewire, considerar debounce para busca.
- [ ] Paginação ou infinite scroll para listas longas — Ação: usar `->paginate()` no backend e Livewire pagination, ou implementar `intersection observer` para carregamento incremental.
- [ ] Ações em lote (selecionar múltiplas transações: excluir, exportar, marcar como conciliado) — Ação: adicionar checkboxes, endpoint Livewire para ações em massa, confirmação e feedback.
- [ ] Undo delete (soft delete + snackbar com "Desfazer") — Ação: implementar `SoftDeletes` no model, ao deletar enviar snackbar com opção `restore(id)` que chama método Livewire para restaurar dentro de X segundos.
- [ ] Gráficos interativos (Chart.js / ApexCharts) com drilldown por categoria/dia — Ação: criar endpoints JSON para dados e componentes JS; permitir clicar na barra para filtrar lista abaixo.
- [ ] Visualizar transações recorrentes no calendário (marcar ocorrências futuras) — Ação: modelar entidade de recorrência e gerar ocorrências no front (ou query mensal), exibir ícone diferente.
- [ ] Importação de extratos (CSV) e extração de PDFs (GeminiPdfExtractorService já presente) — Ação: criar parser/serviço para mapear colunas, UI de upload e reconciliação automática sugerida.
- [ ] Integração com invoices/clients (link direto para `invoice` ou `client` quando aplicável) — Ação: adicionar relações Eloquent e botões de navegação/preview.
- [ ] Cache por mês/categoria para melhorar performance — Ação: usar `Cache::tags(['cashbook','month:YYYY-MM'])->remember(...)`, invalidar em CRUD.
- [ ] Testes automatizados: Unit + Livewire + Dusk (E2E) — Ação: escrever testes para filtros, exclusão, exportação e fluxo do calendário.
- [ ] Melhorar UX mobile: otimizar grid, esconder/colapsar seções não essenciais e adicionar atalhos touch — Ação: criar breakpoints e testar em dispositivos.
- [ ] Auditoria/Log de mudanças (quem excluiu/alterou) — Ação: adicionar activity log (p.ex. `spatie/laravel-activitylog`) e painel de auditoria para admins.
- [ ] Permissões finas (roles/abilities) para edição/exclusão por usuário/conta — Ação: definir policies e middleware.

---

Observações rápidas:
- Arquivo de referência: `resources/views/livewire/cashbook/cashbook-index.blade.php`.
- Quer que eu gere checklists semelhantes para outras páginas (ex.: vendas, produtos, clientes)? Indique os arquivos ou pastas e eu crio em um único `docs/` MD combinado.

Path do arquivo criado: `docs/checklist-cashbook-ideias.md`
