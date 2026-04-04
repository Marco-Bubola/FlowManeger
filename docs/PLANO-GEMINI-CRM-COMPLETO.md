# FlowManager - Plano de IA, Gemini e Evolução para CRM Completo

Documento gerado em 03/04/2026 com base na análise do código atual do projeto.

## 1. Resumo Executivo

O FlowManager hoje já é um sistema operacional forte para financeiro, vendas, catálogo, consórcios, metas e integrações com marketplaces. Ele ainda não é um CRM completo no sentido clássico, porque faltam pilares de funil comercial, histórico unificado de relacionamento, automações de follow-up, atendimento omnichannel, gestão de equipe e governança comercial.

Ao mesmo tempo, o projeto já tem sinais claros de que a IA faz sentido aqui:

- já existe Gemini em importação de produtos por PDF
- já existe Gemini em processamento de transações de fatura
- já existe venda de plano com flag de recurso de IA
- o dashboard já calcula muitos indicadores que podem virar insights automáticos
- o módulo de Mercado Livre já tem perguntas, mensagens, reputação, mediações e promoções, que são pontos naturais para copiloto operacional

Conclusão prática:

- vale a pena padronizar Gemini como camada base da IA do app
- vale a pena usar outras IAs em casos pontuais, não trocar tudo por outro provedor
- o melhor caminho é expandir em fases, começando por copilotos internos e não por chat aberto irrestrito

## 2. Onde o Gemini já existe hoje

### 2.1 Produtos

Página e fluxo atual:

- resources/views/livewire/products/upload-products.blade.php
- app/Livewire/Products/UploadProducts.php
- app/Services/GeminiPdfExtractorService.php

Uso atual:

- leitura de PDF
- filtragem de texto
- extração estruturada de produtos

Valor já comprovado:

- reduz digitação manual
- melhora importação em massa
- encaixa perfeitamente com catálogo

### 2.2 Faturas

Página e fluxo atual:

- resources/views/livewire/invoices/upload-invoice.blade.php
- app/Livewire/Invoices/UploadInvoice.php
- app/Services/GeminiTransactionProcessorService.php

Uso atual:

- leitura de transações
- detecção de parcelas
- limpeza da descrição
- sugestão de categoria

Valor já comprovado:

- acelera conciliação financeira
- reduz erro manual de categorização

## 3. Implementação técnica feita agora

Foi criada uma base reaproveitável para o Gemini:

- app/Services/AI/GeminiClient.php

E os dois fluxos já existentes passaram a usar essa base:

- app/Services/GeminiPdfExtractorService.php
- app/Services/GeminiTransactionProcessorService.php

Ganhos dessa base:

- remove duplicação de chamada HTTP
- centraliza tratamento de erro e configuração
- facilita logging por feature
- deixa a expansão para outras telas muito menos custosa
- elimina dependência do modelo antigo Gemini 2.0 Flash experimental no fluxo de faturas

## 4. Plano para colocar Gemini no app inteiro

## Fase 1 - Padronização da camada de IA

Objetivo: criar base estável antes de espalhar botão de IA pela interface.

### Implementações recomendadas

1. Cliente Gemini centralizado
2. Serviços por domínio em vez de prompts soltos nos componentes
3. Limite de uso por plano
4. Log de uso por usuário e por feature
5. Timeout, fallback e mensagens claras quando IA falhar
6. Prompts versionados por caso de uso

### Estrutura sugerida

- app/Services/AI/GeminiClient.php
- app/Services/AI/FeatureGateService.php
- app/Services/AI/UsageTrackerService.php
- app/Services/AI/PromptCatalogService.php
- app/Models/AiUsageLog.php

## Fase 2 - Copilotos internos de alta produtividade

Essas são as áreas com melhor ROI imediato.

### 4.1 Dashboard principal

Arquivos alvo:

- app/Livewire/Dashboard/DashboardIndex.php
- resources/views/livewire/dashboard/dashboard-index.blade.php
- app/Services/Dashboard/DashboardFinanceMetricsService.php

Implementações:

- resumo financeiro do mês em linguagem natural
- alerta de risco de caixa
- sugestão de corte de despesas por categoria
- explicação automática de variação entre meses
- previsão básica de 30/60/90 dias

Tipo de IA ideal:

- Gemini 2.5 Flash ou Flash-Lite

### 4.2 Clientes

Arquivos alvo:

- app/Livewire/Clients/ClientDashboard.php
- app/Livewire/Clients/ClientResumo.php
- app/Livewire/Clients/ClientsIndex.php

Implementações:

- resumo automático do cliente
- classificação de cliente quente, morno ou frio
- detecção de risco de churn ou inadimplência
- sugestão de próxima ação comercial
- geração de mensagem personalizada para cobrança ou follow-up

### 4.3 Vendas

Arquivos alvo:

- app/Livewire/Sales/CreateSale.php
- app/Livewire/Sales/ShowSale.php
- app/Livewire/Sales/SalesIndex.php

Implementações:

- sugestão de produtos complementares
- geração de proposta comercial
- resumo automático da venda
- recomendação de parcelamento baseada em histórico
- identificação de clientes com alto potencial de recompra

### 4.4 Produtos

Arquivos alvo:

- app/Livewire/Products/CreateProduct.php
- app/Livewire/Products/EditProduct.php
- app/Livewire/Products/ShowProduct.php

Implementações:

- gerar título comercial
- gerar descrição de produto
- sugerir categoria e atributos
- sugerir preço com base em custo, margem e histórico
- detectar cadastro incompleto

### 4.5 Mercado Livre

Arquivos alvo:

- app/Livewire/MercadoLivre/Questions.php
- app/Livewire/MercadoLivre/Messages.php
- app/Livewire/MercadoLivre/EditPublication.php
- app/Livewire/MercadoLivre/PublishProduct.php

Implementações:

- sugerir resposta para pergunta de comprador
- sugerir resposta para mensagem pós-venda
- reescrever título e descrição de anúncio
- sugerir melhoria de reputação baseada em problemas recorrentes

### 4.6 Shopee

Arquivos alvo:

- app/Livewire/Shopee/PublishProduct.php
- app/Livewire/Shopee/PublicationsList.php
- app/Livewire/Shopee/Settings.php

Implementações:

- gerar descrição otimizada
- sugerir ajustes de catálogo
- copiloto para publicação em lote

### 4.7 Consórcios

Arquivos alvo:

- app/Livewire/Consortiums/ShowConsortium.php
- app/Services/ConsortiumNotificationService.php

Implementações:

- score de risco de atraso
- sugestão de ação preventiva
- resumo financeiro do grupo
- previsão de inadimplência

### 4.8 Metas e hábitos

Arquivos alvo:

- app/Livewire/Goals/GoalsDashboard.php
- app/Livewire/DailyHabits/DailyHabitsDashboard.php

Implementações:

- coach de produtividade
- resumo semanal automático
- sugestão de ajuste de meta
- explicação de padrões de falha e consistência

## Fase 3 - IA voltada ao cliente final

Essa fase só deve vir depois da fase 2, porque exige mais controle.

### Implementações recomendadas

1. chatbot para clientes autenticados
2. central de ajuda inteligente
3. assistente de cobrança no WhatsApp
4. copiloto de suporte para equipe interna

## 5. Páginas onde a IA faz mais sentido primeiro

Ordem recomendada de entrega:

1. dashboard principal
2. upload de faturas
3. produtos create/edit/show
4. mercado livre questions/messages/edit-publication
5. client dashboard e client resumo
6. sales create/show/index
7. consórcios
8. goals e daily habits

## 6. Outras IAs que fazem sentido além do Gemini

## 6.1 Gemini

Melhor uso no seu app:

- copiloto geral
- classificação
- resumos
- extração de dados
- multimodal com PDF/imagem
- custo mais baixo para alto volume

Quando usar:

- praticamente em quase todo o app

## 6.2 OpenAI

Melhor uso:

- tarefas de texto premium
- fluxos que exigem escrita comercial muito refinada
- agentes mais sofisticados

Quando usar:

- geração de propostas comerciais premium
- textos de marketing
- atendimento assistido de maior qualidade

## 6.3 Claude

Melhor uso:

- análises longas
- sínteses extensas
- documentos grandes
- respostas mais cuidadosas para suporte e operação

Quando usar:

- relatórios executivos
- análise de mediações complexas
- análise longa de cliente e histórico

## 6.4 Embeddings e busca semântica

Melhor uso:

- memória do cliente
- busca em histórico
- FAQ inteligente
- recomendação contextual

Tecnologia sugerida:

- Gemini Embedding ou OpenAI embeddings
- banco vetorial simples no início, como pgvector

## 7. Isso seria pago?

Sim, em produção quase certamente será pago. O ponto não é se paga ou não, e sim como controlar o custo por feature.

## 7.1 Gemini

Situação atual oficial:

- existe nível sem custo para desenvolvimento e pequenos testes
- produção pede plano pago quando o uso cresce
- Gemini 2.5 Flash é barato para uso geral
- Gemini 2.5 Flash-Lite é ainda mais barato para tarefas simples e alto volume

Leitura prática:

- para o seu app, Gemini é a opção mais natural para começar
- dá para prototipar barato
- dá para escalar sem destruir margem, desde que você não mande contexto demais em cada chamada

## 7.2 OpenAI

Situação prática:

- é pago em produção
- tende a custar mais que Gemini para uso massivo de backoffice
- faz mais sentido em fluxos premium de texto e agentes

## 7.3 Claude

Situação prática:

- é pago para API
- normalmente faz mais sentido em tarefas de análise profunda, não em alto volume operacional

## 7.4 Estratégia correta de custo

Use o modelo certo para cada feature:

- Flash-Lite para classificação simples e limpeza
- Flash para copilotos operacionais
- Pro ou outro modelo premium só em casos de alto valor

## 8. Estratégia comercial para IA no seu SaaS

Seu banco e telas de planos já apontam para monetização de IA. Isso deve virar regra de produto.

### Modelo sugerido

Plano gratuito:

- sem IA ou com limite muito pequeno

Plano intermediário:

- insights automáticos
- assistente de cadastro
- respostas sugeridas em marketplaces

Plano avançado:

- copiloto financeiro
- previsões
- automações com WhatsApp
- atendimento assistido

### Regras de proteção

- limite mensal por usuário
- limite por feature
- fila assíncrona para tarefas pesadas
- logs de uso para faturamento interno

## 9. O que falta para o FlowManager ser um CRM completo

Hoje o sistema é mais ERP operacional + financeiro + marketplace + produtividade. Para virar CRM completo, faltam os blocos abaixo.

## 9.1 Gestão de leads

Está faltando:

- captura de leads
- origem do lead
- score do lead
- estágio do funil
- conversão de lead para cliente

Evidência prática no código:

- o dashboard de vendas já comenta taxa de conversão simulada e que faltam dados de leads

## 9.2 Pipeline comercial

Está faltando:

- oportunidades
- negócios em andamento
- kanban comercial
- previsão de fechamento
- valor ponderado por probabilidade

## 9.3 Timeline unificada do cliente

Hoje você já tem vendas, parcelas e pagamentos por cliente, mas falta uma timeline única com:

- contato realizado
- mensagem enviada
- reunião
- proposta
- observação comercial
- tarefa futura

## 9.4 Tarefas e follow-up

Está faltando:

- tarefas por cliente
- lembretes de follow-up
- agenda comercial
- tarefas automáticas por evento

## 9.5 Atendimento omnichannel

Está faltando:

- WhatsApp integrado
- e-mail integrado de verdade
- histórico de conversa por cliente
- ticket de suporte
- SLA

## 9.6 Equipes e governança comercial

Está faltando:

- multiusuário de verdade por equipe
- papéis e permissões finas
- carteira por vendedor
- metas por vendedor
- auditoria de mudanças

## 9.7 Marketing e automação

Está faltando:

- campanhas
- segmentação
- automação de jornada
- reativação de cliente inativo
- cobrança automática multicanal

## 9.8 Base de conhecimento

Está faltando:

- FAQ interna
- scripts de atendimento
- central de ajuda pública
- busca inteligente com IA

## 10. Roadmap realista de implementação

## Sprint 1

- padronizar camada Gemini
- criar logs de uso de IA
- criar gate por plano
- colocar insights no dashboard

## Sprint 2

- descrição inteligente de produto
- respostas sugeridas para ML
- resumo inteligente do cliente

## Sprint 3

- copiloto de vendas
- copiloto de cobrança
- previsão financeira

## Sprint 4

- módulo de leads
- pipeline comercial
- tarefas e follow-up

## Sprint 5

- WhatsApp
- omnichannel
- automações de CRM

## 11. Próxima implementação recomendada

Se eu fosse conduzir isso no código a partir daqui, eu faria nesta ordem:

1. log de uso de IA por usuário e feature
2. permissão por plano para IA
3. card de insights no dashboard principal
4. botão gerar descrição com IA em produtos
5. botão sugerir resposta em Mercado Livre perguntas
6. resumo de cliente com IA no dashboard do cliente
7. módulo de leads e pipeline

## 12. Recomendação final

Para o seu caso, a melhor estratégia é:

- Gemini como motor principal do app
- OpenAI apenas para fluxos premium de texto, se necessário
- Claude apenas em análises longas e relatórios premium, se necessário
- CRM completo só depois de criar leads, pipeline, timeline, follow-up e omnichannel

O que mais gera resultado agora não é chatbot aberto. É copiloto operacional nas telas que você já tem.