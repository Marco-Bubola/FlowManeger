# 🚀 GUIA RÁPIDO: 7 Passos para Conectar com Mercado Livre

**Tempo estimado:** 30-40 minutos  
**Dificuldade:** ⭐⭐☆☆☆ Fácil

---

## 📌 RESUMO VISUAL

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUXO COMPLETO                           │
└─────────────────────────────────────────────────────────────┘

1️⃣  CRIAR CONTA         2️⃣  CRIAR APP          3️⃣  CONFIGURAR
    Developer               no ML Portal           Permissões
        ↓                       ↓                      ↓
   ✅ Login              ✅ FlowManager         ✅ Leitura/Escrita
   ✅ Aceitar            ✅ Logo/Info           ✅ Products
   ✅ Perfil             ✅ Categoria           ✅ Orders
                                                ✅ Messages

        ↓                       ↓                      ↓

4️⃣  CONFIGURAR          5️⃣  COPIAR              6️⃣  CONFIGURAR
    Redirect URI            Credenciais            .env
        ↓                       ↓                      ↓
   ✅ ngrok              ✅ App ID              ✅ MERCADOLIVRE_APP_ID
   ✅ HTTPS URL          ✅ Secret Key          ✅ SECRET_KEY
                                                ✅ REDIRECT_URI

        ↓                       ↓                      ↓

                    7️⃣  TESTAR OAUTH
                            ↓
                    ✅ Conectar
                    ✅ Autorizar
                    ✅ Token Salvo
                    ✅ Sucesso! 🎉
```

---

## 1️⃣ CRIAR CONTA DEVELOPER (5 min)

### 🌐 Acesse:
```
https://developers.mercadolivre.com.br/
```

### ✅ Faça:
- Clique em "Começar agora"
- Login com sua conta Mercado Livre
- Aceite os Termos de Uso
- Confirme e-mail (se pedido)

### ✔️ Pronto quando:
- Ver dashboard do desenvolvedor
- Menu "Minhas Aplicações" disponível

---

## 2️⃣ CRIAR APLICAÇÃO (10 min)

### 📝 Informações básicas:

```
┌──────────────────────────────────────────┐
│ Nome: FlowManager                        │
│ Descrição: Sistema de gestão integrado  │
│ Tipo: Gestão de vendas e estoque        │
│ Logo: [Upload PNG 200x200]              │
└──────────────────────────────────────────┘
```

### 📍 Localização no portal:
```
Dashboard → Minhas Aplicações → Criar nova aplicação
```

---

## 3️⃣ CONFIGURAR NGROK (10 min)

### 🔧 Por que preciso disso?
- ML exige HTTPS para callbacks
- ngrok cria túnel HTTPS → localhost
- Desenvolvimento local sem certificado

### 💻 Passo a passo:

**Instalar:**
```bash
# Baixar: https://ngrok.com/download
# Ou via Chocolatey:
choco install ngrok
```

**Configurar token:**
```bash
# 1. Criar conta: https://dashboard.ngrok.com/signup
# 2. Copiar token e executar:
ngrok config add-authtoken SEU_TOKEN_AQUI
```

**Iniciar túnel:**
```bash
# Opção 1: Usar script pronto
setup-ngrok.bat

# Opção 2: Comando manual
ngrok http 8000
```

**Resultado esperado:**
```
Session Status    online
Forwarding        https://abc123.ngrok.io -> http://localhost:8000
                  ^^^^^^^^^^^^^^^^^^^^^^
                  COPIE ESTA URL!
```

⚠️ **IMPORTANTE:** Deixe janela aberta!

---

## 4️⃣ CONFIGURAR REDIRECT URI (5 min)

### 📍 Na aplicação ML:

Encontre seção: **"URIs de redirect"**

### ✏️ Adicione:
```
https://abc123.ngrok.io/mercadolivre/auth/callback
       ^^^^^^^^^^^     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^
       Sua URL ngrok   Rota do callback (fixo)
```

### ✅ Verificar:
- URL começa com `https://`
- Termina com `/mercadolivre/auth/callback`
- Apareceu verde (aceito)

---

## 5️⃣ CONFIGURAR PERMISSÕES (10 min)

### ⭐ ESSENCIAIS (marque LEITURA E ESCRITA):

#### 1. Usuários
```
✅ LEITURA E ESCRITA
```
- Necessário para OAuth
- Acessa informações da conta

#### 2. Publicação e sincronização
```
✅ LEITURA E ESCRITA

Tópicos:
✅ items          (produtos)
✅ questions      (perguntas)
✅ items prices   (preços)
✅ stock-locations (estoque)
```

#### 3. Venda e envios
```
✅ LEITURA E ESCRITA

Tópicos:
✅ orders         (pedidos)
✅ orders_v2      (pedidos v2)
✅ shipments      (envios)
```

### 🔵 RECOMENDADOS:

#### 4. Comunicações
```
✅ LEITURA E ESCRITA

Tópicos:
✅ messages (mensagens)
```

### 📊 OPCIONAIS:

#### 5. Métricas do negócio
```
✅ LEITURA (somente leitura)
```

### ⚙️ Fluxos OAuth:
```
✅ Authorization Code
✅ Refresh Token
✅ PKCE necessário
```

---

## 6️⃣ COPIAR CREDENCIAIS (5 min)

### 🔑 No dashboard da aplicação:

```
┌─────────────────────────────────────────┐
│ FlowManager                             │
│                                         │
│ App ID:                                 │
│ 1234567890              [Copiar] ←──────── COPIE!
│                                         │
│ Secret Key:                             │
│ [Mostrar] ←──────────────────────────────── CLIQUE AQUI
│ aBc123XyZ456...         [Copiar] ←──────── DEPOIS COPIE!
└─────────────────────────────────────────┘
```

### 📝 Anote:
```
App ID: _________________________________

Secret Key: _________________________________
```

---

## 7️⃣ CONFIGURAR NO FLOWMANAGER (5 min)

### 📂 Abrir arquivo `.env`:
```bash
code .env
```

### ➕ Adicionar no final:
```env
# ============================================
# MERCADO LIVRE INTEGRATION
# ============================================

# Suas credenciais aqui ↓
MERCADOLIVRE_APP_ID=1234567890
MERCADOLIVRE_SECRET_KEY=aBc123XyZ456PqRsTuV789
MERCADOLIVRE_REDIRECT_URI=https://abc123.ngrok.io/mercadolivre/auth/callback

# Configurações
MERCADOLIVRE_WEBHOOK_SECRET=
MERCADOLIVRE_ENVIRONMENT=production
```

### 🔄 Limpar cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## 8️⃣ TESTAR OAUTH (5 min)

### 🚀 Iniciar servidores:

**Terminal 1: Laravel**
```bash
php artisan serve
# http://127.0.0.1:8000
```

**Terminal 2: ngrok**
```bash
setup-ngrok.bat
# ou: ngrok http 8000
```

### 🌐 Acessar sistema:

1. **Login:**
   ```
   http://localhost:8000/login
   ```

2. **Settings:**
   ```
   http://localhost:8000/mercadolivre/settings
   ```

3. **Conectar:**
   - Clicar em botão amarelo "Conectar com Mercado Livre"
   - Redireciona para ML
   - Clicar em "Permitir"
   - Volta para sistema

4. **Verificar:**
   ```
   ✅ Badge verde "Conta Conectada"
   ✅ Seu nickname aparecendo
   ✅ Botões "Testar Conexão" e "Desconectar"
   ```

5. **Testar:**
   - Clicar em "Testar Conexão"
   - Ver mensagem: ✅ "Conexão testada com sucesso!"

---

## 🎉 PARABÉNS!

### ✅ Você completou:
- ✅ Criou aplicação no ML Developer
- ✅ Configurou permissões corretas
- ✅ Configurou ngrok para desenvolvimento
- ✅ Obteve credenciais
- ✅ Configurou FlowManager
- ✅ Testou OAuth flow com sucesso

### 🚀 Próximos passos:
1. **ProductService** - Publicar produtos no ML
2. **OrderService** - Importar pedidos automaticamente
3. **WebhookController** - Receber notificações em tempo real
4. **Jobs** - Sincronização automática

---

## 📚 ARQUIVOS DE APOIO

### No projeto:
```
📁 docs/
  ├── 📄 GUIA-CONFIGURACAO-MERCADO-LIVRE-DEV.md (guia completo)
  ├── 📄 CHECKLIST-CONFIGURACAO-ML.md (checklist detalhado)
  └── 📄 GUIA-RAPIDO-CONFIGURACAO-ML.md (este arquivo)

📁 root/
  └── 📄 setup-ngrok.bat (script automático)
```

### Documentação ML:
- Portal: https://developers.mercadolivre.com.br/
- API Docs: https://developers.mercadolivre.com.br/pt_br/api-docs
- OAuth: https://developers.mercadolivre.com.br/pt_br/autenticacao-e-autorizacao

---

## 🆘 PRECISA DE AJUDA?

### Problemas comuns:

**"Redirect URI mismatch"**
```
Solução: Verificar se URL do .env é EXATAMENTE igual à do ML
```

**"Invalid client_id"**
```
Solução: Verificar App ID, limpar cache com: php artisan config:clear
```

**ngrok URL mudou**
```
Solução: Atualizar no ML Developer E no .env, limpar cache
```

**Token expira rápido**
```
Solução: Sistema renova automaticamente via refresh_token
```

---

## 📊 CHECKLIST RÁPIDO

```
☐ Conta Developer criada
☐ Aplicação criada
☐ ngrok instalado e rodando
☐ Redirect URI configurado
☐ Permissões marcadas (Usuários, Publicação, Vendas)
☐ Credenciais copiadas
☐ .env configurado
☐ Cache limpo
☐ OAuth testado com sucesso
☐ Token salvo no banco
```

---

**Criado por:** GitHub Copilot  
**Data:** 08/02/2026  
**Tempo médio:** 30-40 minutos  
**Nível:** ⭐⭐☆☆☆ Iniciante/Intermediário

🎯 **Objetivo:** Conectar FlowManager com Mercado Livre em menos de 1 hora!
