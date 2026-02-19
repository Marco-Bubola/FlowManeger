# 🚀 GUIA COMPLETO: Configurar Aplicação Mercado Livre Developer

**Data:** 08/02/2026  
**Versão:** 1.0  
**Objetivo:** Criar e configurar aplicação ML para integração OAuth 2.0

---

## 📋 SUMÁRIO

1. [Pré-requisitos](#pré-requisitos)
2. [Criar Conta Developer](#criar-conta-developer)
3. [Criar Aplicação](#criar-aplicação)
4. [Configurações Obrigatórias](#configurações-obrigatórias)
5. [Permissões e Scopes](#permissões-e-scopes)
6. [Webhooks e Notificações](#webhooks-e-notificações)
7. [Obter Credenciais](#obter-credenciais)
8. [Configurar no Sistema](#configurar-no-sistema)
9. [Testar Integração](#testar-integração)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 PRÉ-REQUISITOS

### Antes de Começar:

✅ **Conta no Mercado Livre**
- Ter uma conta ativa no Mercado Livre Brasil
- Acesso: https://www.mercadolivre.com.br/

✅ **Servidor com HTTPS**
- ML exige HTTPS para callbacks
- Opções:
  - **Produção:** Domínio com certificado SSL
  - **Desenvolvimento:** ngrok, localtunnel, ou similar
  - **Alternativa:** Usar domínio de teste fornecido pelo ML

✅ **Sistema FlowManager Rodando**
- Migrations executadas
- Servidor Laravel funcionando
- Rotas OAuth criadas

---

## 🔐 PASSO 1: CRIAR CONTA DEVELOPER

### 1.1 Acessar Portal de Desenvolvedores

```
URL: https://developers.mercadolivre.com.br/
```

**Ações:**
1. Clicar em **"Começar agora"** ou **"Entrar"**
2. Fazer login com sua conta Mercado Livre
3. Aceitar os Termos de Uso do Developer

### 1.2 Verificar Conta

- Confirme seu e-mail se solicitado
- Complete seu perfil de desenvolvedor
- Leia a documentação inicial (recomendado)

---

## 🛠️ PASSO 2: CRIAR NOVA APLICAÇÃO

### 2.1 Acessar Minhas Aplicações

```
Dashboard > Minhas Aplicações > Criar nova aplicação
```

### 2.2 Informações Básicas

**Campos obrigatórios:**

| Campo | Valor Sugerido | Observações |
|-------|---------------|-------------|
| **Nome da Aplicação** | `FlowManager` | Nome único, sem espaços especiais |
| **Descrição curta** | `Sistema de gestão integrado` | Máximo 100 caracteres |
| **Descrição completa** | `Sistema completo para gestão de vendas, estoque e pedidos com integração Mercado Livre` | Detalhada |
| **Site** | `http://seusite.com.br` | URL do seu site/sistema |
| **Logo** | Upload da logo | PNG/JPG, mínimo 200x200px |

### 2.3 Qual Solução Planeja Desenvolver?

**Selecione:**
- ✅ **Gestão de vendas e estoque**
- ✅ **Sincronização de produtos**
- ✅ **Importação de pedidos**

---

## ⚙️ PASSO 3: CONFIGURAÇÕES OBRIGATÓRIAS

### 3.1 URIs de Redirect (CRÍTICO!)

**O que é:**
- URL para onde o ML redireciona após autorização OAuth
- Deve ser HTTPS (obrigatório)

#### Para DESENVOLVIMENTO (com ngrok):

1. **Instalar ngrok:**
```bash
# Baixar de: https://ngrok.com/download
# Ou via Chocolatey:
choco install ngrok

# Criar conta e pegar token em: https://dashboard.ngrok.com/
ngrok config add-authtoken SEU_TOKEN_AQUI
```

2. **Iniciar túnel HTTPS:**
```bash
# No terminal (deixar rodando):
ngrok http 8000
```

**Resultado:**
```
Forwarding  https://abc123.ngrok.io -> http://localhost:8000
```

3. **Copiar URL do ngrok e adicionar:**
```
https://abc123.ngrok.io/mercadolivre/auth/callback
```

#### Para PRODUÇÃO:

```
https://seudominio.com.br/mercadolivre/auth/callback
```

**Configuração no ML:**
```
URIs de redirect: 
└─> https://abc123.ngrok.io/mercadolivre/auth/callback
    [Adicionar URI de redirect] ← Clique para adicionar mais
```

⚠️ **IMPORTANTE:**
- Sempre use HTTPS (nunca HTTP)
- URL deve ser exata (com /mercadolivre/auth/callback)
- Pode adicionar múltiplas URIs (dev, staging, prod)

---

### 3.2 Fluxos OAuth

**Selecione os seguintes:**

✅ **Authorization Code** (OBRIGATÓRIO)
- Usado para obter tokens de acesso
- Fluxo padrão de autenticação

✅ **Client Credentials** (Opcional)
- Para chamadas sem contexto de usuário
- Útil para consultas públicas

✅ **Refresh Token** (OBRIGATÓRIO)
- Permite renovar tokens expirados
- Essencial para integração contínua

✅ **PKCE necessário** (RECOMENDADO)
- Segurança adicional
- Proteção contra CSRF e injeção de código
- Marque esta opção!

---

### 3.3 Negócios

**Selecione:**

✅ **Mercado Livre**
- Integração com marketplace principal

⬜ **VIS** (Opcional)
- Apenas se for usar o sistema de imobiliária/veículos

---

## 🔑 PASSO 4: PERMISSÕES E SCOPES

### 4.1 Usuários (OBRIGATÓRIO)

```
Permissão: Usuários
Descrição: Acessar a API, consultar e atualizar a conta registrada
Acesso: ✅ LEITURA E ESCRITA
```

**Por que:**
- Necessário para obter informações do vendedor
- Usado no OAuth flow (/users/me)

---

### 4.2 Publicação e Sincronização (ESSENCIAL)

```
Permissão: Publicação e sincronização
Descrição: Criar, atualizar, pausar e/ou excluir anúncios
Acesso: ✅ LEITURA E ESCRITA
```

**Por que:**
- Publicar produtos no ML
- Sincronizar estoque e preços
- Pausar/despausar anúncios

**Tópicos relacionados:**
- ✅ `items` - Produtos/Anúncios
- ✅ `questions` - Perguntas de clientes
- ✅ `items prices` - Preços
- ✅ `stock-locations` - Locais de estoque

---

### 4.3 Venda e Envios (ESSENCIAL)

```
Permissão: Venda e envios de um produto
Descrição: Gerenciar vendas e envios
Acesso: ✅ LEITURA E ESCRITA
```

**Por que:**
- Importar pedidos
- Gerenciar envios
- Atualizar status de entrega

**Tópicos relacionados:**
- ✅ `orders` - Pedidos
- ✅ `orders_v2` - Pedidos v2
- ✅ `shipments` - Envios

---

### 4.4 Comunicações Pré e Pós-Vendas (RECOMENDADO)

```
Permissão: Comunicações pré e pós-vendas
Descrição: Ler e enviar mensagens
Acesso: ✅ LEITURA E ESCRITA
```

**Por que:**
- Responder perguntas automaticamente
- Enviar atualizações ao comprador

**Tópicos relacionados:**
- ✅ `messages` - Mensagens

---

### 4.5 Métricas do Negócio (OPCIONAL - mas útil)

```
Permissão: Métricas do negócio
Descrição: Acompanhar métricas e indicadores
Acesso: ✅ LEITURA
```

**Por que:**
- Dashboard com estatísticas
- Relatórios de vendas
- Monitorar reputação

---

### 4.6 Outras Permissões (Deixar SEM ACESSO por enquanto)

⬜ **Publicidade de um produto** - Só se for usar ML Ads
⬜ **Faturamento** - Só se precisar gerar NF pelo ML
⬜ **Promoções e cupons** - Implementar depois se necessário

---

## 📡 PASSO 5: TÓPICOS DE WEBHOOKS

### 5.1 O Que São Tópicos?

- Eventos que o ML envia notificações
- Webhooks são chamados quando algo muda
- Permite sincronização em tempo real

### 5.2 Tópicos Essenciais

**Marque os seguintes:**

#### Orders (Pedidos) - OBRIGATÓRIO
```
✅ orders          - Pedidos gerais
✅ orders_v2       - Pedidos versão 2 (recomendado)
✅ orders feedback - Feedback de compradores
```

#### Items (Produtos) - OBRIGATÓRIO
```
✅ items           - Produtos/Anúncios
✅ questions       - Perguntas
✅ items prices    - Mudanças de preço
```

#### Shipments (Envios) - RECOMENDADO
```
✅ shipments       - Status de envio
```

#### Messages (Mensagens) - RECOMENDADO
```
✅ messages        - Novas mensagens
```

### 5.3 Outros Tópicos (Opcional)

```
⬜ payments        - Pagamentos
⬜ invoices        - Notas fiscais
⬜ promotions      - Promoções
```

---

## 🔔 PASSO 6: CONFIGURAÇÃO DE NOTIFICAÇÕES

### 6.1 URL de Retorno (Webhook Endpoint)

**Para DESENVOLVIMENTO (ngrok):**
```
https://abc123.ngrok.io/mercadolivre/webhooks
```

**Para PRODUÇÃO:**
```
https://seudominio.com.br/mercadolivre/webhooks
```

⚠️ **ATENÇÃO:**
- Deve ser HTTPS
- URL deve estar acessível publicamente
- ML testará a URL antes de salvar
- Endpoint precisa retornar 200 OK

### 6.2 Criar Rota de Webhook (FAZER DEPOIS)

**Nota:** Ainda vamos criar esta rota. Por enquanto:
- Deixe em branco OU
- Use uma URL de teste: `https://webhook.site/` (gera URL temporária)

---

## 📊 PASSO 7: VISUALIZAÇÃO DE ESCOPOS

### 7.1 Revisar Permissões

Antes de criar, você verá uma tela de revisão:

```
Autorize a integração da aplicação
Revise as permissões que você vai conceder:

✅ Usuários
   └─> Acessar a API e consultar a conta registrada

✅ Publicação e sincronização
   └─> Criar, atualizar, pausar anúncios

✅ Venda e envios
   └─> Gerenciar vendas e envios

... etc
```

### 7.2 Aceitar Termos

```
☑️ Aceito os Termos e Condições e autorizo o uso dos meus dados
   conforme a Declaração de Privacidade.
```

### 7.3 Criar Aplicação

**Clique no botão:**
```
[Criar] ← Finalizar criação
```

---

## 🎉 PASSO 8: OBTER CREDENCIAIS

### 8.1 Após Criar a Aplicação

Você será redirecionado para o dashboard da aplicação.

### 8.2 Copiar Credenciais

Na página principal da aplicação, você verá:

```
┌─────────────────────────────────────────────┐
│ FlowManager                                 │
│ ID: 1234567890                              │
│                                             │
│ App ID (Client ID):                         │
│ 1234567890                      [Copiar]    │
│                                             │
│ Secret Key (Client Secret):                │
│ aBc123XyZ456...                 [Copiar]    │
│ [Mostrar]                                   │
└─────────────────────────────────────────────┘
```

**Copie:**
1. **App ID** (Client ID)
2. **Secret Key** (Client Secret) - clique em "Mostrar" antes

⚠️ **SEGURANÇA:**
- NUNCA compartilhe o Secret Key
- Não commite no Git
- Use variáveis de ambiente

---

## ⚙️ PASSO 9: CONFIGURAR NO FLOWMANAGER

### 9.1 Abrir Arquivo .env

```bash
# No VS Code:
code .env
```

### 9.2 Adicionar Credenciais

**Adicione no final do arquivo:**

```env
# ============================================
# MERCADO LIVRE INTEGRATION
# ============================================

# Credenciais da Aplicação ML
MERCADOLIVRE_APP_ID=1234567890
MERCADOLIVRE_SECRET_KEY=aBc123XyZ456PqRsTuV789
MERCADOLIVRE_REDIRECT_URI=https://abc123.ngrok.io/mercadolivre/auth/callback

# Webhook
MERCADOLIVRE_WEBHOOK_SECRET=webhook_secret_opcional

# Ambiente (sandbox ou production)
MERCADOLIVRE_ENVIRONMENT=production

# Notas:
# - Use 'production' para ambiente real
# - Use 'sandbox' apenas para testes iniciais
# - Atualize REDIRECT_URI se mudar ngrok
```

### 9.3 Limpar Cache do Laravel

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## 🧪 PASSO 10: TESTAR INTEGRAÇÃO

### 10.1 Verificar Servidor

**Terminal 1: Laravel**
```bash
php artisan serve
# Deve estar rodando em: http://127.0.0.1:8000
```

**Terminal 2: ngrok (se usando)**
```bash
ngrok http 8000
# Copie a URL HTTPS: https://abc123.ngrok.io
```

### 10.2 Criar Rota de Teste para Settings

**Adicione em `routes/web.php`:**

```php
// Rota temporária para testar Settings
Route::middleware('auth')->group(function () {
    Route::get('/mercadolivre/settings', \App\Livewire\MercadoLivre\Settings::class)
        ->name('mercadolivre.settings');
});
```

### 10.3 Acessar Settings Component

1. **Fazer login no sistema**
   ```
   http://localhost:8000/login
   ```

2. **Acessar Settings:**
   ```
   http://localhost:8000/mercadolivre/settings
   ```

3. **Você deve ver:**
   - Interface de desconectado
   - Botão "Conectar com Mercado Livre"
   - 4 cards de benefícios

### 10.4 Testar OAuth Flow

**Passo a passo:**

1. **Clicar em "Conectar com Mercado Livre"**
   - Loading aparece
   - Redirecionamento para ML

2. **Tela do Mercado Livre:**
   ```
   ┌─────────────────────────────────────────┐
   │  FlowManager deseja acessar sua conta   │
   │                                         │
   │  Permissões solicitadas:                │
   │  ✓ Ler informações da conta             │
   │  ✓ Gerenciar produtos                   │
   │  ✓ Gerenciar vendas                     │
   │                                         │
   │  [Permitir] [Cancelar]                  │
   └─────────────────────────────────────────┘
   ```

3. **Clicar em "Permitir"**
   - ML redireciona de volta
   - URL será: `https://abc123.ngrok.io/mercadolivre/auth/callback?code=TG-...&state=...`

4. **Callback processa:**
   - AuthController::callback() recebe o code
   - AuthService troca code por token
   - Token salvo no banco
   - Informações do usuário ML carregadas

5. **Redirecionamento final:**
   - Volta para dashboard ou settings
   - Mensagem: "✅ Conectado com Mercado Livre com sucesso!"

6. **Settings atualizado:**
   - Badge verde "Conta Conectada"
   - Mostra seu nickname ML
   - Mostra ML User ID
   - Tempo de expiração
   - Botões: Testar Conexão, Desconectar

### 10.5 Testar Conexão

1. **Clicar em "Testar Conexão"**
2. **Sistema faz requisição para `/users/me`**
3. **Deve aparecer:**
   ```
   ✅ Conexão testada com sucesso!
   Conectado como: SEU_NICKNAME
   ```

### 10.6 Verificar Banco de Dados

```sql
-- Ver token salvo
SELECT 
    id, 
    user_id, 
    ml_user_id, 
    ml_nickname, 
    expires_at,
    is_active,
    created_at
FROM mercadolivre_tokens
WHERE user_id = 1;

-- Ver logs
SELECT 
    endpoint,
    method,
    status_code,
    created_at
FROM mercadolivre_sync_log
ORDER BY created_at DESC
LIMIT 10;
```

---

## 🐛 TROUBLESHOOTING

### Erro: "O endereço deve conter https://"

**Causa:** URL sem HTTPS

**Solução:**
- Use ngrok para desenvolvimento
- Use certificado SSL em produção
- Nunca use http://127.0.0.1 ou http://localhost

---

### Erro: "Redirect URI mismatch"

**Causa:** URL configurada no ML diferente da usada

**Verificar:**
1. URL exata em `.env` (MERCADOLIVRE_REDIRECT_URI)
2. URL cadastrada no ML Developer
3. Se usando ngrok, URL muda a cada restart

**Solução:**
```bash
# No ML Developer:
https://abc123.ngrok.io/mercadolivre/auth/callback

# No .env:
MERCADOLIVRE_REDIRECT_URI=https://abc123.ngrok.io/mercadolivre/auth/callback

# Devem ser IDÊNTICAS (case sensitive)
```

---

### Erro: "Invalid client_id"

**Causa:** App ID incorreto

**Verificar:**
```env
MERCADOLIVRE_APP_ID=1234567890  # Deve ser numérico
```

**Solução:**
- Copie novamente do ML Developer
- Limpe cache: `php artisan config:clear`

---

### Erro: "Invalid client_secret"

**Causa:** Secret Key incorreto

**Solução:**
- Clique em "Mostrar" no ML Developer
- Copie novamente (pode ter espaços extras)
- Limpe cache

---

### Erro: "State parameter mismatch"

**Causa:** State token expirou (> 5 min) ou inválido

**Solução:**
- Tente conectar novamente
- Verifique se cache está funcionando
- Limpe cache: `php artisan cache:clear`

---

### Erro: 404 no callback

**Causa:** Rota não encontrada

**Verificar:**
```bash
# Ver rotas:
php artisan route:list | grep mercadolivre
```

**Deve mostrar:**
```
GET    mercadolivre/auth/redirect   mercadolivre.auth.redirect
GET    mercadolivre/auth/callback   mercadolivre.auth.callback
```

---

### ngrok: "ERR_NGROK_3200"

**Causa:** Túnel expirou (free plan expira em 2h)

**Solução:**
```bash
# Matar processo:
Ctrl+C

# Reiniciar:
ngrok http 8000

# IMPORTANTE: URL vai mudar!
# Atualize no ML Developer E no .env
```

---

### Token expirando rápido

**Causa:** ML tokens expiram em 6 horas por padrão

**Solução:**
- Sistema renova automaticamente com refresh_token
- Certifique-se que refresh_token está sendo salvo
- Verifique logs de renovação

---

### Webhook não recebe notificações

**Causa:** URL não acessível ou não retorna 200

**Verificar:**
```bash
# Testar webhook endpoint:
curl -X POST https://abc123.ngrok.io/mercadolivre/webhooks \
  -H "Content-Type: application/json" \
  -d '{"test": true}'
```

**Solução:**
- Crie o WebhookController (próxima fase)
- Certifique-se que retorna 200 OK
- Verifique firewall/segurança

---

## 📝 CHECKLIST FINAL

Antes de prosseguir, confirme:

- [ ] ✅ Aplicação criada no ML Developer
- [ ] ✅ URIs de redirect configuradas (HTTPS)
- [ ] ✅ Fluxos OAuth selecionados (Authorization Code, Refresh Token, PKCE)
- [ ] ✅ Permissões configuradas (Usuários, Publicação, Vendas)
- [ ] ✅ Tópicos de webhook selecionados
- [ ] ✅ App ID copiado
- [ ] ✅ Secret Key copiado
- [ ] ✅ .env configurado
- [ ] ✅ Cache limpo
- [ ] ✅ ngrok rodando (se dev)
- [ ] ✅ Laravel rodando
- [ ] ✅ Rota de settings criada
- [ ] ✅ OAuth flow testado
- [ ] ✅ Token salvo no banco
- [ ] ✅ Conexão testada com sucesso

---

## 🎯 PRÓXIMOS PASSOS

Após configuração bem-sucedida:

1. **Implementar ProductService** - Publicar produtos no ML
2. **Implementar OrderService** - Importar pedidos
3. **Implementar WebhookController** - Receber notificações em tempo real
4. **Criar ProductIntegration Component** - UI para publicação
5. **Jobs automáticos** - Sincronização contínua

---

## 📚 RECURSOS ÚTEIS

**Documentação Oficial:**
- Portal: https://developers.mercadolivre.com.br/
- API Reference: https://developers.mercadolivre.com.br/pt_br/api-docs
- OAuth: https://developers.mercadolivre.com.br/pt_br/autenticacao-e-autorizacao

**Ferramentas:**
- ngrok: https://ngrok.com/
- Postman ML: https://www.postman.com/mercadolibre/
- Webhook.site: https://webhook.site/ (testes)

**Suporte:**
- Fórum: https://developers.mercadolivre.com.br/community
- FAQ: https://developers.mercadolivre.com.br/pt_br/faq

---

**Criado por:** GitHub Copilot  
**Data:** 08/02/2026  
**Versão:** 1.0  
**Status:** ✅ Pronto para uso

🚀 **Boa sorte com sua integração!**
