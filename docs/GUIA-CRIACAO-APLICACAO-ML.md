# 🚀 GUIA COMPLETO - CRIAR APLICAÇÃO NO MERCADO LIVRE

**Data:** 09/02/2026  
**Tutorial Oficial:** https://developers.mercadolivre.com.br/pt_br/crie-uma-aplicacao-no-mercado-livre

---

## 📋 PRÉ-REQUISITOS

✅ Conta Mercado Livre ativa (preferencialmente **pessoa jurídica**)  
✅ Conta validada (dados completos)  
✅ Domínio com **HTTPS** para redirect URI (obrigatório)  
⚠️ **Importante:** Use a conta do **proprietário** da solução (evita problemas futuros)

---

## 🔧 PASSO 1: ACESSAR O DEVCENTER

1. **Acesse o DevCenter Brasil:**
   - 🔗 https://developers.mercadolivre.com.br/devcenter/

2. **Faça login** com sua conta Mercado Livre

3. **Clique em "Criar uma aplicação"** (ou "Create an application")

---

## 📝 PASSO 2: PREENCHER INFORMAÇÕES BÁSICAS

### 2.1 Dados da Aplicação

**Nome da aplicação:**
```
FlowManager - Integração ML
```
- Deve ser **único** no Mercado Livre
- Aparecerá na tela de autorização para o usuário

**Nome curto:**
```
flowmanager-ml
```
- ML usa para gerar URL da aplicação
- Apenas letras minúsculas, números e hífen

**Descrição:** (até 150 caracteres)
```
Sistema de gestão integrado com Mercado Livre para gerenciar produtos, vendas e estoque automaticamente.
```
- Aparece quando solicita autorização do usuário
- Seja claro e objetivo

**Logo:**
- Tamanho recomendado: 200x200px ou 400x400px
- Formato: PNG, JPG
- Fundo transparente (opcional)
- Representa sua marca/empresa

---

## 🔐 PASSO 3: CONFIGURAR REDIRECT URIs

### ⚠️ ATENÇÃO: HTTPS É OBRIGATÓRIO!

O Mercado Livre **exige protocolo HTTPS** nos redirect URIs para segurança.

### Opções de Configuração:

#### 🏠 Para Desenvolvimento Local (usando ngrok):

1. **Instale o ngrok (escolha uma opção):**

   **OPÇÃO A - Download Manual (Recomendado - sem admin):**
   ```powershell
   # Baixar e extrair automaticamente:
   $ProgressPreference = 'SilentlyContinue'
   Invoke-WebRequest -Uri "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip" -OutFile "$env:TEMP\ngrok.zip"
   Expand-Archive -Path "$env:TEMP\ngrok.zip" -DestinationPath "$env:USERPROFILE\ngrok" -Force
   ```

   **OPÇÃO B - Chocolatey (Requer Admin):**
   ```powershell
   # Execute PowerShell como Administrador:
   choco install ngrok -y
   ```

   **OPÇÃO C - Download do site:**
   - https://ngrok.com/download
   - Extraia o ZIP em qualquer pasta

2. **Execute o ngrok:**
   ```powershell
   # Se usou opção A:
   cd $env:USERPROFILE\ngrok
   .\ngrok.exe http 8000
   
   # Se usou opção B ou C:
   ngrok http 8000
   ```

3. **Copie a URL HTTPS** gerada (exemplo):
   ```
   https://abcd-1234-efgh-5678.ngrok-free.app
   ```

4. **Configure o Redirect URI:**
   ```
   https://abcd-1234-efgh-5678.ngrok-free.app/mercadolivre/auth/callback
   ```

#### 🌐 Para Produção:

Configure com seu domínio real:
```
https://seudominio.com/mercadolivre/auth/callback
```

### ⚡ IMPORTANTE:

- ✅ Sempre use HTTPS (nunca HTTP)
- ✅ O caminho deve ser exatamente: `/mercadolivre/auth/callback`
- ✅ Pode adicionar múltiplos redirect URIs (desenvolvimento + produção)

**Exemplo de múltiplos URIs:**
```
https://seudominio.com/mercadolivre/auth/callback
https://abcd-1234.ngrok-free.app/mercadolivre/auth/callback
```

---

## 🔔 PASSO 4: CONFIGURAR NOTIFICAÇÕES (WEBHOOKS)

### 4.1 URL de Retorno de Notificações

**Para produção:**
```
https://seudominio.com/mercadolivre/webhooks
```

**Para desenvolvimento (ngrok):**
```
https://abcd-1234.ngrok-free.app/mercadolivre/webhooks
```

### 4.2 Selecionar Tópicos

Marque os seguintes tópicos para receber notificações:

✅ **Orders** (Pedidos)
- Novos pedidos
- Atualizações de status
- Pagamentos confirmados
- Cancelamentos

✅ **Items** (Produtos)
- Produtos publicados
- Alterações de preço/estoque
- Pausas/ativações
- Status do anúncio

✅ **Questions** (Perguntas)
- Novas perguntas de clientes
- Respostas pendentes

✅ **Messages** (Mensagens)
- Mensagens do chat
- Conversas com compradores

⚪ **Catalog** (Catálogo) - Opcional
⚪ **Shipments** (Envios) - Opcional
⚪ **Promotions** (Promoções) - Opcional
⚪ **Claims** (Reclamações) - Opcional

### ⚠️ IMPORTANTE:

- O endpoint `/mercadolivre/webhooks` já está implementado no sistema
- ML faz requisições POST para esta URL
- Resposta deve ser < 3 segundos
- Sempre retorne **200 OK**

---

## 🔑 PASSO 5: CONFIGURAR AUTENTICAÇÃO E SEGURANÇA

### 5.1 Escopos de Permissão

Selecione:

✅ **Leitura (read)** - Métodos GET
- Buscar pedidos
- Consultar produtos
- Ver informações

✅ **Escrita (write)** - Métodos POST, PUT, DELETE
- Publicar produtos
- Atualizar estoque/preço
- Processar pedidos

✅ **Offline Access** (Acesso offline)
- ⭐ **MUITO IMPORTANTE** para sincronização automática
- Permite refresh token
- Sistema funciona mesmo quando usuário está offline

### 5.2 PKCE (Proof Key for Code Exchange)

**Recomendação:** ✅ **HABILITAR**

- Segurança adicional
- Previne ataques CSRF
- Proteção contra injeção de código
- Já implementado no sistema

---

## 💾 PASSO 6: SALVAR E OBTER CREDENCIAIS

1. **Clique em "Salvar"** ou "Save"

2. **Você será redirecionado** para a página da aplicação

3. **Copie as credenciais:**

   **Client ID:** (exemplo)
   ```
   1234567890123456
   ```
   
   **Client Secret:** (exemplo)
   ```
   abcdefghijklmnopqrstuvwxyz123456789ABCDEF
   ```

### ⚠️ SEGURANÇA DO CLIENT SECRET:

- 🔒 **NUNCA compartilhe** o Client Secret
- 🔒 **NÃO commite** no Git
- 🔒 Mantenha apenas no arquivo `.env`
- 🔒 Renove periodicamente (a cada 3-6 meses)

---

## ⚙️ PASSO 7: CONFIGURAR NO SISTEMA

### 7.1 Atualizar arquivo `.env`

**Abra o arquivo:** `c:\projetos\FlowManeger\.env`

**Adicione/Atualize as seguintes linhas:**

```env
# ====================================
# MERCADO LIVRE CONFIGURATION
# ====================================

# Credenciais da aplicação ML
ML_CLIENT_ID=SEU_CLIENT_ID_AQUI
ML_CLIENT_SECRET=SEU_CLIENT_SECRET_AQUI

# Redirect URI (deve ser HTTPS)
ML_REDIRECT_URI=https://seudominio.com/mercadolivre/auth/callback

# URL de webhook (deve ser HTTPS)
ML_WEBHOOK_URL=https://seudominio.com/mercadolivre/webhooks

# Webhook Secret (opcional - para validação adicional)
# ML_WEBHOOK_SECRET=seu_secret_opcional

# País (Brasil)
ML_COUNTRY=BR

# Site ID (MLB = Mercado Livre Brasil)
ML_SITE_ID=MLB

# Ambiente (production ou sandbox)
ML_ENVIRONMENT=production
```

### 7.2 Exemplo Completo com ngrok (Desenvolvimento):

```env
# ====================================
# MERCADO LIVRE CONFIGURATION
# ====================================

# Credenciais da aplicação ML
ML_CLIENT_ID=1234567890123456
ML_CLIENT_SECRET=abcdefghijklmnopqrstuvwxyz123456789ABCDEF

# Redirect URI (ngrok para desenvolvimento)
ML_REDIRECT_URI=https://abcd-1234-efgh-5678.ngrok-free.app/mercadolivre/auth/callback

# URL de webhook (ngrok para desenvolvimento)
ML_WEBHOOK_URL=https://abcd-1234-efgh-5678.ngrok-free.app/mercadolivre/webhooks

# País e Site
ML_COUNTRY=BR
ML_SITE_ID=MLB

# Ambiente
ML_ENVIRONMENT=production
```

### 7.3 Exemplo Completo (Produção):

```env
# ====================================
# MERCADO LIVRE CONFIGURATION
# ====================================

# Credenciais da aplicação ML
ML_CLIENT_ID=1234567890123456
ML_CLIENT_SECRET=abcdefghijklmnopqrstuvwxyz123456789ABCDEF

# Redirect URI (produção)
ML_REDIRECT_URI=https://flowmanager.com.br/mercadolivre/auth/callback

# URL de webhook (produção)
ML_WEBHOOK_URL=https://flowmanager.com.br/mercadolivre/webhooks

# País e Site
ML_COUNTRY=BR
ML_SITE_ID=MLB

# Ambiente
ML_ENVIRONMENT=production
```

---

## 🔄 PASSO 8: ATUALIZAR CONFIG NO LARAVEL

**Execute no terminal:**

```powershell
php artisan config:clear
php artisan cache:clear
```

---

## ✅ PASSO 9: TESTAR A CONFIGURAÇÃO

### 9.1 Acessar a página de configurações:

```
http://localhost:8000/mercadolivre/settings
```

Ou se estiver usando ngrok:
```
https://abcd-1234.ngrok-free.app/mercadolivre/settings
```

### 9.2 Conectar com Mercado Livre:

1. Clique em **"Conectar com Mercado Livre"**
2. Você será redirecionado para o ML
3. **Autorize a aplicação**
4. Será redirecionado de volta com sucesso

### 9.3 Verificar Status:

- ✅ Status deve aparecer **"Conectado"** (verde)
- ✅ Nome do vendedor deve aparecer
- ✅ Data de expiração do token deve aparecer
- ✅ Teste de conexão deve funcionar

---

## 🎉 PASSO 10: PUBLICAR SEU PRIMEIRO PRODUTO

### 10.1 Acessar integração de produtos:

```
http://localhost:8000/mercadolivre/products
```

### 10.2 Publicar produto:

1. Selecione um produto
2. Clique em **"Publicar no Mercado Livre"**
3. Preencha os dados:
   - ✅ Categoria (sistema prevê automaticamente)
   - ✅ Tipo de anúncio (Gold Special, Gold Pro, etc)
   - ✅ Condição (Novo/Usado)
   - ✅ Garantia (opcional)
   - ✅ Atributos obrigatórios
4. Clique em **"Publicar"**

---

## 🔍 PASSO 11: TESTAR WEBHOOK (OPCIONAL)

### 11.1 Endpoint de Teste:

Acesse no navegador:
```
https://seudominio.com/mercadolivre/webhooks/test
```

Deve retornar:
```json
{
  "success": true,
  "message": "Webhook endpoint is working",
  "timestamp": "2026-02-09 10:30:45"
}
```

### 11.2 Configurar Webhook no ML:

1. Acesse o **DevCenter** do ML
2. Vá em **"Editar"** sua aplicação
3. Role até **"Configurações de notificações"**
4. Cole a URL do webhook:
   ```
   https://seudominio.com/mercadolivre/webhooks
   ```
5. Salve

---

## 📊 MONITORAMENTO

### Logs do Sistema:

```powershell
# Ver logs em tempo real
Get-Content storage\logs\laravel.log -Tail 50 -Wait
```

### Tabelas de Auditoria:

- `mercadolivre_webhooks` - Todos webhooks recebidos
- `mercadolivre_sync_log` - Histórico de sincronizações
- `mercadolivre_tokens` - Tokens de acesso
- `mercadolivre_orders` - Pedidos importados

---

## 🛠️ TROUBLESHOOTING

### Erro: "Invalid redirect_uri"

**Solução:**
- Verifique se o URI no `.env` é **exatamente igual** ao configurado no ML
- Certifique-se de usar **HTTPS**
- Execute `php artisan config:clear`

### Erro: "Invalid client credentials"

**Solução:**
- Verifique se copiou o Client ID e Secret corretamente
- Sem espaços extras
- Execute `php artisan config:clear`

### Webhook não está sendo recebido

**Solução:**
- Verifique se a URL está acessível publicamente
- Teste com: `https://seudominio.com/mercadolivre/webhooks/test`
- Certifique-se de que o servidor está rodando
- Se usar ngrok, verifique se não expirou

### Token expirando rapidamente

**Solução:**
- Certifique-se de selecionar **"Offline Access"** no ML
- Sistema fará refresh automático
- Verifique logs de refresh em `storage/logs/laravel.log`

---

## 🔐 SEGURANÇA - MELHORES PRÁTICAS

### Client Secret:

✅ **Sempre mantenha secreto**  
✅ **Nunca versione no Git** (use .gitignore)  
✅ **Renove a cada 3-6 meses**  
✅ **Use variáveis de ambiente**

### Webhook:

✅ **Use HTTPS sempre**  
✅ **Valide assinatura X-Hub-Signature**  
✅ **Implemente rate limiting**  
✅ **Log todas requisições**

### Tokens:

✅ **Armazene criptografados no banco**  
✅ **Implemente refresh automático**  
✅ **Revogue tokens inutilizados**  
✅ **Monitore expiração**

---

## 📚 REFERÊNCIAS OFICIAIS

- 📖 [Criar Aplicação](https://developers.mercadolivre.com.br/pt_br/crie-uma-aplicacao-no-mercado-livre)
- 📖 [Autenticação OAuth 2.0](https://developers.mercadolivre.com.br/pt_br/autenticacao-e-autorizacao)
- 📖 [Webhooks/Notificações](https://developers.mercadolivre.com.br/pt_br/produto-receba-notificacoes)
- 📖 [API Reference](https://developers.mercadolivre.com.br/pt_br/api-docs-pt-br)
- 📖 [DevCenter Brasil](https://developers.mercadolivre.com.br/devcenter/)

---

## ✅ CHECKLIST FINAL

Antes de ir para produção, certifique-se:

- [ ] Aplicação criada no DevCenter ML
- [ ] Client ID e Secret configurados no `.env`
- [ ] Redirect URI com HTTPS configurado
- [ ] Webhook URL com HTTPS configurado
- [ ] Tópicos de notificação selecionados
- [ ] Escopos: read, write, offline_access
- [ ] PKCE habilitado
- [ ] Página de settings acessível
- [ ] Conexão testada com sucesso
- [ ] Primeiro produto publicado
- [ ] Webhook testado
- [ ] Logs funcionando
- [ ] Backup do Client Secret armazenado com segurança

---

## 🎊 PRONTO!

Sua integração com Mercado Livre está **100% configurada** e pronta para uso!

**Próximos passos:**
1. Publique mais produtos
2. Teste a importação de pedidos
3. Configure sincronização automática (opcional)
4. Monitore logs e webhooks

**Suporte:**
- Documentação local: `/docs`
- API ML: https://developers.mercadolivre.com.br
- Sistema: 100% funcional e testado

---

**Desenvolvido por:** GitHub Copilot  
**Data:** 09/02/2026  
**Status:** ✅ Pronto para Produção
