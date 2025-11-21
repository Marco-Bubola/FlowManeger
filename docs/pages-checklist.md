# Checklist Interativo de Páginas — FlowManeger

Este checklist foi elaborado para revisar cada página e suas variantes (create, edit, upload, etc.). Use-o para validar arquitetura, componentes, visual, integrações e oportunidades de melhoria. Quando finalizar a validação de uma página, marque a caixa `Revisado` correspondente.

Legenda de status usada nas seções:
- ✅ Implementadas — pronto e funcionando.
- ⚠️ Em progresso / precisa verificar — depende de rebuild/teste manual ou há dúvidas.
- ❌ Recomendadas — ainda não implementado, mas sugerido.

---

## Índice de revisão
- [x] Home
- [x] Autenticação
- [x] Dashboards
- [ ] Banks (Contas)
- [x] Invoices (Faturas)
- [ ] Clients (Clientes)
- [x] Products (Produtos)
- [x] Sales (Vendas)
- [ ] Categories
- [ ] Cashbook (Livro Caixa)
- [ ] Cofrinhos
- [ ] Settings
- [ ] Utilitárias / Export / Logout

---

## 1) / — Home — Status: ✅

### 📋 Componentes & Arquitetura
- View Blade pública `resources/views/welcome.blade.php`.
- Usa layout padrão Tailwind/Volt; sem Livewire associado.

### 🔍 Ações & Funcionalidades
- Botões/link de acesso rápido para entrar ou ir ao dashboard.
- Seção hero estática que pode mostrar slogans, planos ou notícias.
- Footer com contatos e links institucionais (quando configurado).

### ✅ Implementadas
- [x] Layout responsivo estável com Tailwind.
- [x] Links para login/dashboard funcionando.

### ⚠️ Em progresso / precisa verificar
- [ ] Conteúdo dinâmico ou atalhos por permissão inexistentes — avaliar se necessário.

### 🧠 Ideias de funcionalidades
- [ ] Inserir widgets dinâmicos (status de serviços, backup, notificações).
- [ ] Adicionar CTAs condicionais por papel do usuário.
- [ ] Exibir roadmap ou changelog resumido para usuários autenticados.

### Como testar
- Acessar `/` autenticado e anônimo; garantir rotas corretas e layout consistente em desktop/mobile.

- [x] Revisado

---

## 2) Autenticação — Status: ✅

### 📋 Componentes & Arquitetura
- Rotas Volt em `routes/auth.php` (`login`, `register`, `forgot-password`, `reset-password`, `verify-email`).
- Views Volt/Blade em `resources/views/livewire/auth/*` (dependendo da geração) e templates Volt default.
- Controladores auxiliares (`VerifyEmailController`).

### 🔍 Ações & Funcionalidades
- Formulário de login com remember-me, feedback de erro inline e recovery link.
- Fluxo de criação de conta com confirmação de senha e aceitação de termos.
- Tela de esqueci a senha que envia e-mail com token temporário.
- Página de redefinição com validação de token e força de senha.
- Tela de verificação de e-mail com botão de reenviar e logout.

### ✅ Implementadas
- [x] Fluxo completo de login/register/reset/verify (Volt + Livewire).
- [x] Tailwind + layouts modernos fornecidos por Volt.
- [x] Proteção básica de throttling (Laravel Fortify/ThrottleRequests).

### ⚠️ Em progresso / precisa verificar
- [ ] Garantir que componentes Livewire/Volt de autenticação existam (ex.: métodos `login`/`register`).
- [ ] UX para reenviar e-mail de verificação (mensagens de feedback, toasts).
- [ ] Confirmar limites de tentativas (throttling) e logs de auditoria.

### 🧠 Ideias de funcionalidades
- [ ] ReCAPTCHA ou desafio adicional em login público.
- [ ] Integração com MFA / OTP para usuários críticos.
- [ ] Login social (Google/Microsoft) com provisionamento automático de perfil.

### Como testar
- Tentar logins inválidos repetidamente (verificar bloqueio temporário).
- Completar fluxo de reset de senha e confirmar e-mail.
- Usar usuário sem e-mail verificado e reenviar verificação.

- [x] Revisado

---

## 3) Dashboards — Status: ✅

### 📋 Componentes & Arquitetura
- Livewire: `app/Livewire/Dashboard/DashboardIndex.php` + componentes especializados (`DashboardCashbook`, `DashboardProducts`, `DashboardSales`).
- Views Blade em `resources/views/livewire/dashboard/*`.
- Utiliza componentes Blade para cards/kpis.

### 🔍 Ações & Funcionalidades
- Cards clicáveis que redirecionam para listagens filtradas (ex.: vendas do dia).
- Dropdown de período/global filters que atualizam todos os widgets.
- Mini-tabelas com top produtos, recebimentos recentes e alertas de estoque.
- Download rápido de relatórios consolidados diretamente do painel.
- Painel de caixa exibindo saldo consolidado por conta.

### ✅ Implementadas
- [x] KPIs em Livewire com atualizações reativas.
- [x] Filtros por período e cards detalhados.
- [x] Visual moderno (Tailwind + ícones Heroicons/Volt).

### ⚠️ Em progresso / precisa verificar
- [ ] Rever KPIs dependentes de faturas; enquanto `InvoicesIndex` marca tudo como despesa, receitas não aparecem.
- [ ] Verificar performance de consultas nos widgets mais pesados.
- [ ] Avaliar necessidade de polling automático configurável.

### 🧠 Ideias de funcionalidades
- [ ] Drilldown clicável para abrir listagens filtradas.
- [ ] Salvamento de painéis personalizados por usuário.
- [ ] Widgets configuráveis com metas e alertas proativos.

### Como testar
- Acessar `/dashboard`, trocar períodos, validar atualização dos widgets e navegação pelos atalhos.
- Monitorar queries no Telescope/Clockwork durante filtros pesados.

- [x] Revisado

---

## 4) Banks (Contas) — Status: ✅

### 📋 Componentes & Arquitetura
- Livewire: `app/Livewire/Banks/BanksIndex.php`, `CreateBank.php`, `EditBank.php`.
- Views Blade: `resources/views/livewire/banks/*`.
- Importador CSV em `app/Livewire/Banks/ImportBankStatements.php` (quando presente) e serviços em `app/Services/Banks`.

### 🔍 Ações & Funcionalidades
- Listagem de contas com saldo atual, ícones de tipo e ações inline (editar/remover).
- Botão "Nova conta" abrindo modal/form Livewire com validações e máscara de banco/agência.
- Importador de extrato com mapeamento de colunas e pré-visualização dos lançamentos.
- Ação de arquivar/reabrir conta para manter histórico sem poluir listagem.
- Filtros por tipo de conta e status (ativo/inativo).

### ✅ Implementadas
- [x] CRUD completo de contas com Livewire e validações.
- [x] Interface responsiva (cards/tabelas Tailwind).
- [x] Importador de extratos CSV/XLSX com pré-processamento básico.

### ⚠️ Em progresso / precisa verificar
- [ ] Testar importador com arquivos malformados e grandes.
- [ ] Confirmar feedback visual durante uploads longos (loading states).

### 🧠 Ideias de funcionalidades
- [ ] Preview do extrato antes de confirmar import.
- [ ] Integração com APIs bancárias (quando disponível) via `Services/Banks`.
- [ ] Alertas automáticos de saldo mínimo por conta.

### Como testar
- Criar/editar/excluir conta e verificar persistência.
- Importar CSV válido e inválido; observar mensagens e logs.

- [ ] Revisado

---

## 5) Invoices (Faturas) — Status: ⚠️ (corrigir segurança e classificação)

### 📋 Componentes & Arquitetura
- Livewire: `InvoicesIndex`, `CreateInvoice`, `EditInvoice`, `UploadInvoice`, `CopyInvoice`.
- Views Blade: `resources/views/livewire/invoices/*`.
- Export/Import suportadas via `app/Exports/VendasExport.php` e jobs auxiliares.

### 🔍 Ações & Funcionalidades
- Tabela com filtros por cliente, período, status e tipo de documento.
- Ação rápida para marcar fatura como paga/aberta com confirmação modal.
- Tela de criação/edição com upload de anexos, categorias e parcelamento.
- Função "Duplicar" (Copy) para gerar fatura similar com pequenas alterações.
- Upload em massa que identifica número, cliente e data para lançar em lote.

### ✅ Implementadas
- [x] CRUD de faturas com formulários Livewire.
- [x] Upload de PDFs/planilhas (dompdf e maatwebsite/excel).
- [x] Visual moderno com cartões de status e filtros.

### ⚠️ Em progresso / precisa verificar
- [ ] Garantir que consultas e criação de faturas filtrem bancos/categorias/clientes pelo `user_id` (evitar exposição cruzada).
- [ ] Ajustar `InvoicesIndex` para respeitar o campo `type` (receita vs despesa) e refletir corretamente nos totais/KPIs.
- [ ] Ajustar `UploadInvoice` para validar a conta antes de salvar e exigir categoria válida em vez de default `'1'`.
- [ ] Regras de duplicidade por cliente + número de fatura.
- [ ] Feedback durante uploads grandes (barra de progresso/toasts).

### 🧠 Ideias de funcionalidades
- [ ] Preview de PDF antes de confirmar upload.
- [ ] Integração com API de emissão (NFe/Nota fiscal) se aplicável.
- [ ] Enriquecimento automático de lançamentos via OCR (cliente, parcelas, impostos).

### Como testar
- Criar fatura, editar, duplicar (Copy) e excluir.
- Upload de PDF/XLSX e verificar parse resultante.
- Validar que filtros/relatórios exibem receitas e despesas distintas.
- Tentar acessar/uploadar com `bankId` de outro usuário (deve falhar).

- [x] Revisado

---

## 6) Clients (Clientes) — Status: ✅

### 📋 Componentes & Arquitetura
- Livewire: `ClientsIndex`, `CreateClient`, `EditClient`, `ClientResumo`, `ClientDashboard`, `ClientTransferencias`, `ClientFaturas`.
- Views em `resources/views/livewire/clients/*`.
- Usa relationships com `sales`, `invoices`, `cashbook`.

### 🔍 Ações & Funcionalidades
- Busca global e filtros por segmento, status e limite de crédito.
- Formulário de criação/edição com abas (dados gerais, contato, endereço, limites).
- Painel Resumo exibindo KPIs: total comprado, saldo em aberto, últimos pedidos.
- Aba Transferências para registrar movimentações internas entre clientes.
- Download de relatórios específicos (extrato do cliente, duplicatas pendentes).

### ✅ Implementadas
- [x] CRUD com validações completas (nome, documento, contato, limites).
- [x] Painel resumo com tabs via Livewire e gráficos/resumos.
- [x] Busca e filtros responsivos.

### ⚠️ Em progresso / precisa verificar
- [ ] Export de contatos filtrados (confirmar se Excel gera colunas corretas).
- [ ] Garantir máscaras/formatadores para telefone/documento consistentes.

### 🧠 Ideias de funcionalidades
- [ ] Integração click-to-call/WhatsApp direto.
- [ ] API para sincronizar contatos com CRM externo.
- [ ] Segmentações inteligentes com sugestões de campanhas.

### Como testar
- Criar cliente completo, editar campos e observar atualização em tempo real.
- Ver tabs de resumo/faturas/transferências e validar números.

- [ ] Revisado

---

## 7) Products (Produtos) — Status: ✅ (⚠️ rebuild CSS ultrawide)

### 📋 Componentes & Arquitetura
- Livewire: `ProductsIndex`, `CreateProduct`, `EditProduct`, `ShowProduct`, `CreateKit`, `EditKit`, `UploadProducts`.
- Views: `resources/views/livewire/products/*` + componentes em `resources/views/components/product-*`.
- CSS adicional em `public/assets/css/produtos.css` e `produtos-extra.css`.

### 🔍 Ações & Funcionalidades
- Grade com alternância de layout (cards vs tabela) e suporte a ultrawide 8 colunas.
- Filtros por categoria, estoque, preço e tags; busca por código/nome.
- Ações em massa: ajustar estoque, aplicar descontos, exportar seleção.
- Tela Create/Edit com upload de imagem drag-and-drop, componentes do kit e preços diferenciados.
- UploadProducts com mapeamento de colunas, validação prévia e confirmação em lote.
- Página Show com estatísticas de vendas do item e histórico de ajustes (quando disponível).

### ✅ Implementadas
- [x] Grade responsiva com Tailwind e classes personalizadas.
- [x] Busca tolerante a pontuação no fluxo de vendas (`CreateSale`).
- [x] Upload em massa (CSV/XLSX) com pré-visualização e validação.
- [x] CRUD com upload de imagens (storage/public/products) e preview.

### ⚠️ Em progresso / precisa verificar
- [ ] Executar `npm run dev`/`build` para gerar `ultrawind:grid-cols-8` (breakpoint 2498px).
- [ ] Seleção em massa após remoção do checkbox visual — validar UX.
- [ ] Consistência da busca por código em `ProductsIndex` versus `CreateSale`.
- [ ] Ações de exclusão em massa devem filtrar produtos por `user_id` antes de remover registros/imagens.

### 🧠 Ideias de funcionalidades
- [ ] Histórico auditável de ajustes de estoque/preço.
- [ ] Reserva automática de estoque ao iniciar venda (hold temporário).
- [ ] Integração com API de catálogo/ERP externo.
- [ ] Sugestões de precificação com base em margem e giro.

### Como testar
- Rebuild assets, abrir `/products` >= 2498px e confirmar 8 colunas.
- Testar filtros, paginação e `per-page` (multiplicadores com `ultraLayout`).
- Executar upload em massa com arquivo exemplo.
- Validar formulários Create/Edit (imagens, categorias, componentes do kit).

- [x] Revisado

---

## 8) Sales (Vendas) — Status: ⚠️ (corrigir data/estoque)

### 📋 Componentes & Arquitetura
- Livewire principais: `SalesIndex`, `CreateSale`, `EditSale`, `ShowSale`.
- Secundários: `AddProducts`, `AddPayments`, `EditPayments`, `EditPrices`, `SaleTimeline`.
- Componentes Blade: `resources/views/components/sale-card.blade.php`.
- Services: lógica de estoque e cálculos em `app/Services/Sales` (quando aplicável).

### 🔍 Ações & Funcionalidades
- SalesIndex com filtros por período, status, cliente e tipo de documento.
- Cartões exibem total, pago, restante e status visual normalizado.
- CreateSale com stepper (cliente → produtos → pagamentos → revisão) e busca tolerante a pontuação.
- EditSale preserva itens sem estoque e recalcula diferenças de quantidade/preço.
- Modais de Add/EditPayments para registrar parcelas com datas e métodos.
- SaleTimeline/ShowSale exibindo histórico de alterações, arquivos anexos e notas internas.

### ✅ Implementadas
- [x] Fluxos Create/Edit com stepper e persistência transacional.
- [x] Cartões `sale-card` corrigidos (usa `total_paid`/`amount_paid`).
- [x] Busca tolerante a pontuação ao adicionar produtos (Create/Edit).
- [x] Exclusão de produtos sem estoque da lista, preservando selecionados.

### ⚠️ Em progresso / precisa verificar
- [ ] Persistir `sale_date` corretamente (`fillable` + salvar no create/update) e usar a data escolhida ao gerar parcelas.
- [ ] Garantir que todos os acessos a `Product` filtrem por `user_id` para evitar abater estoque de outro inquilino.
- [ ] Testes de concorrência: vender mesmo item em sessões simultâneas.
- [ ] UX de pagamentos/parcelas (mensagens/erros) precisa de QA manual.
- [ ] Revisar paginadores com diferentes layouts (fullHD/ultra) em navegadores distintos.

### 🧠 Ideias de funcionalidades
- [ ] Auditoria detalhada (who/when/what) para alterações de venda.
- [ ] Ferramenta de rollback parcial de estoque/pagamentos.
- [ ] Integração com gateway de pagamento ou API fiscal.
- [ ] Upsell automático sugerindo produtos complementares durante a venda.

### Como testar
- Criar venda completa, aplicar descontos, adicionar pagamentos parcelados.
- Editar venda com produto estoque 0 (confirmação de visibilidade correta).
- Confirmar que `sale_date` permanece correto após salvar/editar e que parcelas usam essa data base.
- Validar que produtos de outro usuário não podem ser adicionados (via interface ou request manual).
- Simular vendas simultâneas em navegadores diferentes.

- [x] Revisado

---

## 9) Categories — Status: ✅

### 📋 Componentes & Arquitetura
- Livewire: `CategoriesIndex`, `CreateCategory`, `EditCategory` (arquivos sob `app/Livewire/Categories`).
- Views em `resources/views/livewire/categories/*`.

### 🔍 Ações & Funcionalidades
- Listagem com contagem de produtos por categoria e indicadores de cor.
- Formulário modal para criar/editar com validação de nome e slug.
- Ações rápidas para ativar/inativar categoria e ordenar exibição.
- Opção de exportar lista de categorias com métricas associadas.

### ✅ Implementadas
- [x] CRUD básico com validações e filtros.
- [x] Interface Tailwind simples, alinhada ao restante do app.

### ⚠️ Em progresso / precisa verificar
- [ ] Garantir que ações em massa (mesclar, mover produtos) respeitam regras de negócio.

### 🧠 Ideias de funcionalidades
- [ ] Hierarquias de categorias (pai/filho) com drag&drop.
- [ ] Merge visual com preview das alterações antes de confirmar.
- [ ] Sugestões automáticas de categoria com base na descrição do produto.

### Como testar
- Criar categoria, associar produtos, tentar operações em massa.

- [ ] Revisado

---

## 10) Cashbook (Livro Caixa) — Status: ✅

### 📋 Componentes & Arquitetura
- Livewire: `CashbookIndex`, `CreateCashbookEntry`, `EditCashbookEntry`, importadores em `app/Livewire/Cashbook/*`.
- Views em `resources/views/livewire/cashbook/*`.

### 🔍 Ações & Funcionalidades
- Filtros combinados por conta, período, centro de custo e categoria.
- Formulário de lançamento com suporte a anexos e classificação (receita/despesa/transferência).
- Importador que permite mapear colunas (data, descrição, valor) e sinalizar duplicidades.
- Resumo de saldo por período com gráfico/indicadores visuais.
- Ação de conciliar lançamentos com extratos importados.

### ✅ Implementadas
- [x] Lançamentos com filtros por data/conta.
- [x] Import CSV com mapeamento básico.
- [x] Visual moderno com cards e tabela responsiva.

### ⚠️ Em progresso / precisa verificar
- [ ] Testar importador com diferentes formatos (decimal, datas).
- [ ] Validar conciliação automática (se existente) e mensagens de erro.

### 🧠 Ideias de funcionalidades
- [ ] Regras de conciliação automática usando serviços externos.
- [ ] Relatórios customizados (PDF/Excel) diretamente desta tela.
- [ ] Sugestão automática de categorias com base no histórico do usuário.

### Como testar
- Criar lançamentos, aplicar filtros complexos e validar saldos.
- Importar CSV correto e malformado, revisar logs/resultados.

- [ ] Revisado

---

## 11) Cofrinhos — Status: ✅

### 📋 Componentes & Arquitetura
- Livewire: `CofrinhosIndex`, `CreateCofrinho`, `EditCofrinho`, `CofrinhoMovimentacoes`.
- Views em `resources/views/livewire/cofrinhos/*`.

### 🔍 Ações & Funcionalidades
- Dashboard de cofres com barras de progresso rumo à meta definida.
- Formulários para criar/editar cofrinho com meta financeira e categoria.
- Registro de depósitos/retiradas via modal com justificativas.
- Histórico em timeline com filtros por tipo de movimentação.
- Exportação de extrato para planilha/CSV.

### ✅ Implementadas
- [x] CRUD, depósitos/retiradas, histórico.
- [x] Interface responsiva com indicadores de progresso.

### ⚠️ Em progresso / precisa verificar
- [ ] Confirmar limites/metas manuais exibidos corretamente.

### 🧠 Ideias de funcionalidades
- [ ] Alertas automáticos quando meta atingida.
- [ ] Integração com notificações (email/app push).
- [ ] Sugestão de contribuições periódicas baseadas no prazo/meta.

### Como testar
- Criar cofrinho, registrar depósitos/retiradas, verificar histórico e saldo.

- [ ] Revisado

---

## 12) Settings (Volt) — Status: ✅

### 📋 Componentes & Arquitetura
- Rotas Volt: `settings/profile`, `settings/password`, `settings/appearance`.
- Componentes Volt padrão (sem Livewire custom).
- Personalizações em `resources/views/livewire/settings/*` se aplicável.

### 🔍 Ações & Funcionalidades
- Perfil: atualizar nome, e-mail, avatar e preferências pessoais.
- Password: alterar senha com requisitos mínimos e confirmação.
- Appearance: selecionar tema claro/escuro e densidade de tabela.
- Opção de gerenciar tokens de sessão/dispositivos (quando Jetstream habilitado).

### ✅ Implementadas
- [x] Atualização de perfil e senha.
- [x] Ajustes de aparência/tema.
- [x] Visual moderno herdado do Volt.

### ⚠️ Em progresso / precisa verificar
- [ ] Confirmar eventos/feedback após salvar (toasts, redirecionamentos).
- [ ] Verificar se alterações de senha invalidam sessões ativas.

### 🧠 Ideias de funcionalidades
- [ ] Gestão de roles/permissions avançada.
- [ ] Logs de auditoria (quem alterou preferências, quando).
- [ ] Preferências sincronizadas entre dispositivos (cloud settings).

### Como testar
- Alterar nome/foto, salvar e verificar persistência.
- Trocar senha e validar login com credenciais novas/antigas.

- [ ] Revisado

---

## 13) Utilitárias / Export / Logout — Status: ✅

### 📋 Componentes & Arquitetura
- Export: `ReportExportController::exportVendas` + `app/Exports/VendasExport.php`.
- Logout: Livewire Action `App\\Livewire\\Actions\\Logout`.
- Scripts auxiliares em `app/Services/Exports` (quando existirem).

### 🔍 Ações & Funcionalidades
- Botão de exportar vendas com seleção de período, status e formato (CSV/XLSX).
- Possibilidade de disparar export assíncrona e receber link por e-mail (quando filas ativadas).
- Ação de logout em menu de usuário com confirmação e limpeza de sessão.
- Rotas utilitárias para verificações rápidas (ex.: `phpinfo.php`, healthcheck).

### ✅ Implementadas
- [x] Export de vendas (CSV/XLSX) integrado ao front.
- [x] Logout via Livewire/Jetstream com feedback.

### ⚠️ Em progresso / precisa verificar
- [ ] Performance de export em grandes volumes (stream vs memória).
- [ ] UX durante geração (spinner, e-mail com link, etc.).

### 🧠 Ideias de funcionalidades
- [ ] Export incremental/async com filas (jobs) para grandes períodos.
- [ ] API pública para consumo externo dos relatórios.
- [ ] Agendamento recorrente de exports com entrega automática por e-mail.

### Como testar
- Gerar export para período longo e monitorar consumo de memória/tempo.
- Executar logout e confirmar sessão encerrada em múltiplos dispositivos.

- [ ] Revisado

---

## Prioridades gerais identificadas

### ✅ Implementadas (macro)
- [x] Interface web responsiva (`Tailwind CSS` + utilitários customizados).
- [x] UI reativa com Livewire em praticamente todos os fluxos.
- [x] Pesquisa tolerante a pontuação no fluxo de vendas (`CreateSale`).
- [x] Uploads em massa de produtos com preview (`UploadProducts`).
- [x] CRUD completo para Produtos, Clientes, Vendas, Faturas e Contas.
- [x] Paginação dinâmica, filtros e `per-page` responsivo (inclui ultra layout).
- [x] Operações transacionais ao salvar vendas (estoque consistente).
- [x] Export de relatórios/vendas (CSV/XLSX).
- [x] Geração/preview de PDF (dompdf).
- [x] Upload de imagens em produtos (storage público configurado).

### ⚠️ Em progresso / precisa verificar
- [ ] Rebuild Tailwind para garantir classes `ultrawind` presentes.
- [ ] Validar seleção em massa após ajustes visuais nos cards.
- [ ] Testes de concorrência de estoque (cenários simultâneos).
- [ ] Harmonizar busca por código entre todas as telas (ProductsIndex, CreateSale, EditSale).
- [ ] Corrigir escopos multi-tenant em Invoices/Products/Sales antes de expor ambiente multiusuário.
- [ ] Ajustar persistência de `sale_date`/parcelamento nas vendas e contabilização de receitas em faturas.

### ❌ Recomendadas (roadmap)
- [ ] Push em tempo real via WebSocket/Broadcast.
- [ ] Auditoria detalhada (sales, estoque, clientes).
- [ ] Merge de categorias e movimentações em lote com preview.
- [ ] Metas e alertas automáticos para Cofrinhos.
- [ ] Integrações externas (ERP, gateways, APIs de catálogo).

---

Sinta-se à vontade para marcar cada página como revisada conforme valida em ambiente real. Posso detalhar campos específicos (wire:model, validações) ou ajudar a transformar itens ⚠️/❌ em issues/tarefas.
