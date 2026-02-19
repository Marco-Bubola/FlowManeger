# ✅ CHECKLIST: Configuração Mercado Livre Developer

**Data de início:** ___/___/2026  
**Desenvolvedor:** ________________  
**Status:** 🔄 Em andamento

---

## 📋 PRÉ-REQUISITOS

- [ ] Tenho conta ativa no Mercado Livre Brasil
- [ ] Tenho acesso ao Laravel rodando localmente
- [ ] Todas as migrations foram executadas
- [ ] Settings Component foi criado
- [ ] AuthController foi criado
- [ ] Rotas OAuth foram criadas

---

## 🔐 FASE 1: CRIAR CONTA DEVELOPER (10 min)

- [ ] **1.1** Acessei https://developers.mercadolivre.com.br/
- [ ] **1.2** Fiz login com minha conta ML
- [ ] **1.3** Aceitei os Termos de Uso do Developer
- [ ] **1.4** Confirmei e-mail (se solicitado)
- [ ] **1.5** Completei perfil de desenvolvedor

**Anotações:**
```
Data de criação: ___/___/2026
E-mail usado: ________________________________
```

---

## 🛠️ FASE 2: CRIAR APLICAÇÃO (15 min)

### Informações Básicas

- [ ] **2.1** Cliquei em "Minhas Aplicações"
- [ ] **2.2** Cliquei em "Criar nova aplicação"
- [ ] **2.3** Preenchi informações básicas:

```
Nome da Aplicação: FlowManager
Descrição curta: Sistema de gestão integrado
Descrição completa: Sistema completo para gestão de vendas, estoque e pedidos com integração Mercado Livre
Site: _______________________________
```

- [ ] **2.4** Fiz upload da logo (mínimo 200x200px)
- [ ] **2.5** Selecionei tipo de solução:
  - [ ] Gestão de vendas e estoque
  - [ ] Sincronização de produtos
  - [ ] Importação de pedidos

---

## 🌐 FASE 3: CONFIGURAR NGROK (20 min)

### Opção A: Instalar ngrok

- [ ] **3.1** Baixei de https://ngrok.com/download
- [ ] **3.2** Extraí o ngrok.exe
- [ ] **3.3** Criei conta em https://dashboard.ngrok.com/signup
- [ ] **3.4** Copiei meu token de autenticação
- [ ] **3.5** Executei comando:
```bash
ngrok config add-authtoken MEU_TOKEN_AQUI
```

### Iniciar Túnel

- [ ] **3.6** Executei `setup-ngrok.bat` OU
- [ ] **3.7** Executei manualmente: `ngrok http 8000`
- [ ] **3.8** Copiei a URL HTTPS gerada

```
Minha URL ngrok: https://_________________.ngrok.io
```

⚠️ **IMPORTANTE:** Deixar janela do ngrok aberta!

---

## ⚙️ FASE 4: CONFIGURAR APLICAÇÃO ML (30 min)

### URIs de Redirect

- [ ] **4.1** Na tela da aplicação, encontrei "URIs de redirect"
- [ ] **4.2** Adicionei URL de callback:

```
https://_________________.ngrok.io/mercadolivre/auth/callback
```

- [ ] **4.3** Cliquei em "Adicionar URI de redirect"
- [ ] **4.4** URL foi aceita (apareceu verde)

### Fluxos OAuth

- [ ] **4.5** Marquei:
  - [ ] Authorization Code
  - [ ] Client Credentials
  - [ ] Refresh Token
  - [ ] PKCE necessário

### Negócios

- [ ] **4.6** Selecionei "Mercado Livre"

### Permissões - CRÍTICO!

#### Usuários (OBRIGATÓRIO)
- [ ] **4.7** Selecionei: **LEITURA E ESCRITA**

#### Publicação e Sincronização (ESSENCIAL)
- [ ] **4.8** Selecionei: **LEITURA E ESCRITA**
- [ ] **4.9** Marquei tópicos:
  - [ ] items
  - [ ] questions
  - [ ] items prices
  - [ ] stock-locations

#### Venda e Envios (ESSENCIAL)
- [ ] **4.10** Selecionei: **LEITURA E ESCRITA**
- [ ] **4.11** Marquei tópicos:
  - [ ] orders
  - [ ] orders_v2
  - [ ] shipments

#### Comunicações (RECOMENDADO)
- [ ] **4.12** Selecionei: **LEITURA E ESCRITA**
- [ ] **4.13** Marquei tópicos:
  - [ ] messages

#### Métricas (OPCIONAL)
- [ ] **4.14** Selecionei: **LEITURA**

### Notificações (Deixar em branco por enquanto)

- [ ] **4.15** Deixei "URL de retorno" em branco (criar WebhookController depois)

### Finalizar

- [ ] **4.16** Revisei todas as permissões
- [ ] **4.17** Marquei checkbox: "Aceito os Termos e Condições"
- [ ] **4.18** Cliquei em "Criar"
- [ ] **4.19** Aplicação foi criada com sucesso! 🎉

---

## 🔑 FASE 5: OBTER CREDENCIAIS (5 min)

- [ ] **5.1** Copiei o **App ID**:

```
App ID: _________________________________
```

- [ ] **5.2** Cliquei em "Mostrar" no Secret Key
- [ ] **5.3** Copiei o **Secret Key**:

```
Secret Key: _________________________________
```

⚠️ **SEGURANÇA:** Não compartilhar estas credenciais!

---

## ⚙️ FASE 6: CONFIGURAR FLOWMANAGER (10 min)

### Editar .env

- [ ] **6.1** Abri arquivo `.env` no VS Code
- [ ] **6.2** Adicionei no final:

```env
# ============================================
# MERCADO LIVRE INTEGRATION
# ============================================

# Credenciais da Aplicação ML
MERCADOLIVRE_APP_ID=_________________________________
MERCADOLIVRE_SECRET_KEY=_________________________________
MERCADOLIVRE_REDIRECT_URI=https://_________________.ngrok.io/mercadolivre/auth/callback

# Webhook (deixar vazio por enquanto)
MERCADOLIVRE_WEBHOOK_SECRET=

# Ambiente
MERCADOLIVRE_ENVIRONMENT=production
```

- [ ] **6.3** Salvei o arquivo `.env`

### Limpar Cache Laravel

- [ ] **6.4** Executei comandos:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

- [ ] **6.5** Vi mensagens de sucesso

---

## 🧪 FASE 7: TESTAR INTEGRAÇÃO (15 min)

### Verificar Servidores

- [ ] **7.1** Laravel está rodando (Terminal 1):
```bash
php artisan serve
# Rodando em: http://127.0.0.1:8000
```

- [ ] **7.2** ngrok está rodando (Terminal 2):
```bash
ngrok http 8000
# URL: https://_________________.ngrok.io
```

### Acessar Settings

- [ ] **7.3** Fiz login no sistema:
```
http://localhost:8000/login
```

- [ ] **7.4** Acessei Settings:
```
http://localhost:8000/mercadolivre/settings
```

- [ ] **7.5** Vi a interface de desconectado:
  - [ ] Título "Não Conectado"
  - [ ] Botão amarelo "Conectar com Mercado Livre"
  - [ ] 4 cards de benefícios

### Testar OAuth Flow

- [ ] **7.6** Cliquei em "Conectar com Mercado Livre"
- [ ] **7.7** Fui redirecionado para tela do ML
- [ ] **7.8** Vi as permissões solicitadas
- [ ] **7.9** Cliquei em "Permitir"
- [ ] **7.10** Voltei para o FlowManager
- [ ] **7.11** Vi mensagem de sucesso: ✅ "Conectado com Mercado Livre com sucesso!"

### Verificar Conexão

- [ ] **7.12** Vi badge verde "Conta Conectada"
- [ ] **7.13** Vi meu nickname do ML:

```
Nickname ML: _________________________________
User ID ML: _________________________________
```

- [ ] **7.14** Vi tempo de expiração do token
- [ ] **7.15** Cliquei em "Testar Conexão"
- [ ] **7.16** Vi mensagem de sucesso: ✅ "Conexão testada com sucesso!"

### Verificar Banco de Dados

- [ ] **7.17** Executei SQL:

```sql
SELECT * FROM mercadolivre_tokens WHERE user_id = 1;
```

- [ ] **7.18** Vi registro com:
  - [ ] access_token (preenchido)
  - [ ] refresh_token (preenchido)
  - [ ] ml_user_id (seu ID)
  - [ ] ml_nickname (seu nickname)
  - [ ] expires_at (data futura)
  - [ ] is_active = 1

- [ ] **7.19** Executei SQL para logs:

```sql
SELECT * FROM mercadolivre_sync_log ORDER BY created_at DESC LIMIT 5;
```

- [ ] **7.20** Vi registros de:
  - [ ] POST /oauth/token (status 200)
  - [ ] GET /users/me (status 200)

---

## ✅ VALIDAÇÃO FINAL

### Funcionalidades Testadas

- [ ] ✅ Conectar com Mercado Livre
- [ ] ✅ OAuth flow completo
- [ ] ✅ Token salvo no banco
- [ ] ✅ Informações do vendedor carregadas
- [ ] ✅ Testar conexão funcionando
- [ ] ✅ Logs sendo criados

### Testar Renovar Token (Opcional)

- [ ] **Extra 1** Cliquei em "Renovar Token"
- [ ] **Extra 2** Vi mensagem de sucesso
- [ ] **Extra 3** Expiração foi atualizada

### Testar Desconectar

- [ ] **Extra 4** Cliquei em "Desconectar"
- [ ] **Extra 5** Confirmei na modal
- [ ] **Extra 6** Vi mensagem de desconexão
- [ ] **Extra 7** Voltei para tela de desconectado
- [ ] **Extra 8** Token foi desativado no banco (is_active = 0)

---

## 🎉 CONCLUSÃO

- [ ] ✅ **TUDO FUNCIONANDO!**
- [ ] 📸 Tirei screenshots das telas de sucesso
- [ ] 📝 Documentei problemas encontrados (se houver)
- [ ] 💾 Fiz backup das credenciais em local seguro
- [ ] 🚀 Pronto para implementar ProductService!

---

## 📊 ESTATÍSTICAS

```
Tempo total gasto: _______ horas
Dificuldades encontradas: _______________________________
Notas importantes: _______________________________________
```

---

## 🐛 PROBLEMAS ENCONTRADOS

Se algo não funcionou, documente aqui:

**Problema 1:**
```
Descrição: _____________________________________________
Solução: _______________________________________________
```

**Problema 2:**
```
Descrição: _____________________________________________
Solução: _______________________________________________
```

---

## 📝 PRÓXIMOS PASSOS

Após conclusão desta configuração:

- [ ] Implementar ProductService
- [ ] Criar ProductIntegration Component
- [ ] Implementar OrderService
- [ ] Criar WebhookController
- [ ] Configurar Jobs automáticos

---

**✅ CHECKLIST CONCLUÍDO EM:** ___/___/2026  
**🎊 PARABÉNS! Integração OAuth 100% funcional!**
