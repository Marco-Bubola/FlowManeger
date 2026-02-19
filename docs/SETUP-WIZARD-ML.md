# 🧙‍♂️ SETUP WIZARD - INTEGRAÇÃO MERCADO LIVRE

**Bem-vindo ao Assistente de Configuração!**  
Siga este guia passo a passo para integrar seu sistema com o Mercado Livre em apenas 15 minutos.

---

## 🎯 ANTES DE COMEÇAR

### ✅ Você vai precisar de:
- [ ] Conta Mercado Livre (preferencialmente pessoa jurídica)
- [ ] 15-20 minutos de tempo
- [ ] Computador com acesso à internet
- [ ] Laravel rodando (`php artisan serve`)

### 📱 Opcional para testes locais:
- [ ] ngrok instalado (para webhooks funcionarem localmente)

---

# 🚀 PASSO 1: ACESSAR O DEVCENTER

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  🌐 Abra seu navegador e acesse:                   │
│                                                     │
│  https://developers.mercadolivre.com.br/devcenter/  │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Ações:
1. ✅ Clique no link acima
2. ✅ Faça **login** com sua conta Mercado Livre
3. ✅ Clique em **"Criar uma aplicação"** (botão verde)

**Continue quando estiver na tela de criação →**

---

# 📝 PASSO 2: PREENCHER INFORMAÇÕES

```
┌─────────────────────────────────────────────────────┐
│  📋 FORMULÁRIO DA APLICAÇÃO                         │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Nome: FlowManager - Integração ML                 │
│                                                     │
│  Nome curto: flowmanager-ml                        │
│                                                     │
│  Descrição:                                        │
│  Sistema de gestão integrado com Mercado Livre     │
│  para gerenciar produtos, vendas e estoque.        │
│                                                     │
│  Logo: [Opcional - Pode pular]                     │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Ações:
1. ✅ Copie e cole os textos acima
2. ✅ Logo é opcional (pode enviar depois)
3. ✅ Role a página para baixo

**Continue quando preencher →**

---

# 🔗 PASSO 3: CONFIGURAR REDIRECT URI

```
┌─────────────────────────────────────────────────────┐
│  🔐 URLS DE REDIRECIONAMENTO                        │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ⚠️ ATENÇÃO: Você precisa usar HTTPS!              │
│                                                     │
│  Escolha uma opção:                                │
│                                                     │
│  📍 OPÇÃO A - Desenvolvimento Local (ngrok):       │
│                                                     │
│     1. Abra novo terminal PowerShell               │
│     2. Execute: ngrok http 8000                    │
│     3. Copie a URL HTTPS que aparece               │
│        Ex: https://abcd-1234.ngrok-free.app        │
│     4. Cole abaixo adicionando:                    │
│        /mercadolivre/auth/callback                 │
│                                                     │
│  📍 OPÇÃO B - Produção:                            │
│                                                     │
│     https://seudominio.com/mercadolivre/auth/callback
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Usar ngrok (Desenvolvimento):

**Escolha uma opção de instalação:**

#### 📥 OPÇÃO 1 - Download Manual (SEM necessidade de Admin):
```powershell
# Baixar e extrair ngrok automaticamente:
$ProgressPreference = 'SilentlyContinue'; Invoke-WebRequest -Uri "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip" -OutFile "$env:TEMP\ngrok.zip"; Expand-Archive -Path "$env:TEMP\ngrok.zip" -DestinationPath "$env:USERPROFILE\ngrok" -Force

# Executar:
cd $env:USERPROFILE\ngrok
.\ngrok.exe http 8000
```

#### 🍫 OPÇÃO 2 - Chocolatey (Requer executar PowerShell como Admin):
```powershell
# 1. Feche o PowerShell
# 2. Clique com botão direito > "Executar como Administrador"
# 3. Execute:
choco install ngrok -y

# 4. Executar ngrok:
ngrok http 8000
```

#### 🌐 OPÇÃO 3 - Download do Site:
1. Acesse: https://ngrok.com/download
2. Baixe versão Windows
3. Extraia o ZIP
4. Execute: `ngrok.exe http 8000`

**Copie a URL que aparece:**
```
Exemplo: https://abcd-1234-efgh-5678.ngrok-free.app
```

**Cole no campo Redirect URI:**
```
https://abcd-1234-efgh-5678.ngrok-free.app/mercadolivre/auth/callback
```

### ✅ Ações:
1. ✅ Configurou ngrok OU tem domínio HTTPS
2. ✅ Redirect URI preenchido
3. ✅ Termina com `/mercadolivre/auth/callback`

**Continue quando configurar →**

---

# 🔔 PASSO 4: CONFIGURAR WEBHOOKS

```
┌─────────────────────────────────────────────────────┐
│  📬 NOTIFICAÇÕES (WEBHOOKS)                         │
├─────────────────────────────────────────────────────┤
│                                                     │
│  URL de retorno de notificações:                   │
│                                                     │
│  [Sua URL ngrok ou domínio]/mercadolivre/webhooks  │
│                                                     │
│  Exemplo com ngrok:                                │
│  https://abcd-1234.ngrok-free.app/mercadolivre/webhooks
│                                                     │
│  Exemplo produção:                                 │
│  https://seudominio.com/mercadolivre/webhooks      │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### Tópicos para marcar:

```
✅ Orders     → Pedidos (novos, atualizações, cancelamentos)
✅ Items      → Produtos (alterações, pausas, ativações)
✅ Questions  → Perguntas de clientes
✅ Messages   → Mensagens do chat
```

### ✅ Ações:
1. ✅ URL de webhook preenchida
2. ✅ 4 tópicos marcados (Orders, Items, Questions, Messages)

**Continue quando configurar →**

---

# 🔐 PASSO 5: CONFIGURAR PERMISSÕES

```
┌─────────────────────────────────────────────────────┐
│  🔓 AUTENTICAÇÃO E SEGURANÇA                        │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Escopos de permissão:                             │
│                                                     │
│  ✅ read           → Leitura (buscar dados)        │
│  ✅ write          → Escrita (criar/atualizar)     │
│  ✅ offline_access → ⭐ MUITO IMPORTANTE!          │
│                                                     │
│  Use o PKCE:                                       │
│  ✅ Habilitado (Recomendado para segurança)        │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### ⚠️ NÃO ESQUEÇA:
- **offline_access** é ESSENCIAL para sincronização automática!

### ✅ Ações:
1. ✅ Marcou **read**
2. ✅ Marcou **write**
3. ✅ Marcou **offline_access** ⭐
4. ✅ Habilitou PKCE

**Continue quando marcar todos →**

---

# 💾 PASSO 6: SALVAR APLICAÇÃO

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│           🎉 TUDO PRONTO PARA SALVAR! 🎉           │
│                                                     │
│  📋 Revise rapidamente:                            │
│                                                     │
│  ✅ Nome preenchido                                │
│  ✅ Redirect URI configurado (HTTPS)               │
│  ✅ Webhook URL configurado                        │
│  ✅ Tópicos marcados                               │
│  ✅ Permissões marcadas (read, write, offline)     │
│                                                     │
│  👇 Clique em SALVAR                               │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### ✅ Ações:
1. ✅ Revisei tudo
2. ✅ Cliquei em **"Salvar"**
3. ✅ Fui redirecionado para página da aplicação

**Continue quando salvar →**

---

# 🔑 PASSO 7: COPIAR CREDENCIAIS

```
┌─────────────────────────────────────────────────────┐
│  🎊 APLICAÇÃO CRIADA COM SUCESSO!                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Agora você verá suas credenciais:                 │
│                                                     │
│  📋 Client ID:                                     │
│  └─ 1234567890123456                               │
│                                                     │
│  📋 Client Secret: (clique para mostrar)           │
│  └─ abcdefghijklmnopqrstuvwxyz123456789ABCDEF      │
│                                                     │
│  ⚠️  IMPORTANTE: Client Secret é SECRETO!          │
│      Nunca compartilhe publicamente!               │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### ✅ Ações:
1. ✅ Cliquei em "Mostrar" Client Secret
2. ✅ Copiei **Client ID** para área de transferência
3. ✅ Copiei **Client Secret** para área de transferência
4. ✅ Salvei ambos em local seguro

**Continue quando copiar →**

---

# ⚙️ PASSO 8: CONFIGURAR .ENV

```
┌─────────────────────────────────────────────────────┐
│  📝 ATUALIZAR ARQUIVO DE CONFIGURAÇÃO               │
└─────────────────────────────────────────────────────┘
```

### 1. Abra o arquivo `.env`:
```
c:\projetos\FlowManeger\.env
```

### 2. Localize a seção MERCADO LIVRE

### 3. Cole suas credenciais:

```env
# ====================================
# MERCADO LIVRE CONFIGURATION
# ====================================

# Cole aqui seu Client ID
ML_CLIENT_ID=1234567890123456

# Cole aqui seu Client Secret
ML_CLIENT_SECRET=abcdefghijklmnopqrstuvwxyz123456789ABCDEF

# Cole aqui seu Redirect URI (mesma do ML)
ML_REDIRECT_URI=https://abcd-1234.ngrok-free.app/mercadolivre/auth/callback

# Cole aqui sua Webhook URL (mesma do ML)
ML_WEBHOOK_URL=https://abcd-1234.ngrok-free.app/mercadolivre/webhooks
```

### ✅ Ações:
1. ✅ Arquivo `.env` aberto
2. ✅ **ML_CLIENT_ID** preenchido
3. ✅ **ML_CLIENT_SECRET** preenchido
4. ✅ **ML_REDIRECT_URI** preenchido (HTTPS)
5. ✅ **ML_WEBHOOK_URL** preenchido
6. ✅ Arquivo **salvo** (Ctrl+S)

**Continue quando salvar →**

---

# 🧹 PASSO 9: LIMPAR CACHE

```
┌─────────────────────────────────────────────────────┐
│  🗑️  LIMPAR CACHE DO LARAVEL                        │
└─────────────────────────────────────────────────────┘
```

### Abra terminal PowerShell na pasta do projeto:

```powershell
# Limpar cache de configuração
php artisan config:clear

# Limpar cache geral
php artisan cache:clear
```

### ✅ Deve aparecer:
```
✓ Configuration cache cleared successfully.
✓ Application cache cleared successfully.
```

### ✅ Ações:
1. ✅ Executei `php artisan config:clear`
2. ✅ Executei `php artisan cache:clear`
3. ✅ Sem erros retornados

**Continue quando limpar →**

---

# 🧪 PASSO 10: TESTAR CONEXÃO

```
┌─────────────────────────────────────────────────────┐
│  🎯 MOMENTO DA VERDADE!                             │
│  Vamos testar se tudo está funcionando:            │
└─────────────────────────────────────────────────────┘
```

### 1. Certifique-se que está rodando:

```powershell
# Se não estiver rodando:
php artisan serve
```

### 2. Abra no navegador:
```
http://localhost:8000/mercadolivre/settings
```

**OU** se usar ngrok:
```
https://abcd-1234.ngrok-free.app/mercadolivre/settings
```

### 3. Você verá a página de configurações!

```
┌─────────────────────────────────────────────────────┐
│  ⚙️  Configurações Mercado Livre                    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Status: ⚪ Desconectado                           │
│                                                     │
│  [🔗 Conectar com Mercado Livre]                   │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 4. Clique em **"Conectar com Mercado Livre"**

### 5. Você será redirecionado para o ML:

```
┌─────────────────────────────────────────────────────┐
│  🏪 Mercado Livre                                   │
├─────────────────────────────────────────────────────┤
│                                                     │
│  FlowManager - Integração ML deseja acessar:       │
│                                                     │
│  ✅ Ler suas informações                           │
│  ✅ Gerenciar seus produtos                        │
│  ✅ Gerenciar seus pedidos                         │
│  ✅ Acesso offline                                 │
│                                                     │
│  [✅ Autorizar]  [❌ Cancelar]                      │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 6. Clique em **"Autorizar"**

### 7. Você voltará para o sistema:

```
┌─────────────────────────────────────────────────────┐
│  ⚙️  Configurações Mercado Livre                    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Status: ✅ Conectado                              │
│                                                     │
│  👤 Vendedor: Seu Nome                             │
│  📧 Email: seu@email.com                           │
│  🕐 Token expira em: 180 dias                      │
│                                                     │
│  [🔌 Desconectar]  [🧪 Testar Conexão]             │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### ✅ Sucesso quando:
1. ✅ Status aparece **"Conectado"** (verde)
2. ✅ Seu nome de vendedor aparece
3. ✅ Email aparece
4. ✅ Data de expiração aparece

**🎉 PARABÉNS! Sistema conectado! →**

---

# 📦 PASSO 11: PUBLICAR PRODUTO

```
┌─────────────────────────────────────────────────────┐
│  🎯 FINAL: PUBLICAR SEU PRIMEIRO PRODUTO!           │
└─────────────────────────────────────────────────────┘
```

### 1. Acesse:
```
http://localhost:8000/mercadolivre/products
```

### 2. Você verá seus produtos:

```
┌─────────────────────────────────────────────────────┐
│  📦 Integração com Mercado Livre                    │
├─────────────────────────────────────────────────────┤
│                                                     │
│  [🔍 Buscar...]  [🗂️ Categoria ▼]                 │
│                                                     │
│  ┌──────────────┐  ┌──────────────┐               │
│  │ 🖼️  Produto 1 │  │ 🖼️  Produto 2 │               │
│  │              │  │              │               │
│  │ R$ 299,90    │  │ R$ 450,00    │               │
│  │ Estoque: 10  │  │ Estoque: 5   │               │
│  │              │  │              │               │
│  │ [🚀 Publicar]│  │ [🚀 Publicar]│               │
│  └──────────────┘  └──────────────┘               │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 3. Clique em **"Publicar"** em um produto

### 4. Modal abrirá:

```
┌─────────────────────────────────────────────────────┐
│  🚀 Publicar no Mercado Livre                       │
├─────────────────────────────────────────────────────┤
│                                                     │
│  Categoria: [MLB1234 - Eletrônicos ▼]              │
│  Tipo: [Gold Special ▼]                            │
│  Condição: [● Novo  ○ Usado]                       │
│  Garantia: [12] meses                              │
│                                                     │
│  Atributos:                                        │
│  Marca: [__________]                               │
│  Modelo: [__________]                              │
│                                                     │
│  [❌ Cancelar]  [✅ Publicar Agora]                │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### 5. Preencha e clique **"Publicar Agora"**

### 6. Sucesso! 🎉

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ✅ Produto publicado com sucesso no ML!            │
│                                                     │
│  🔗 Ver no ML: [Link]                              │
│                                                     │
└─────────────────────────────────────────────────────┘
```

### ✅ Ações finais:
1. ✅ Produto publicado
2. ✅ Link do ML funcionando
3. ✅ Status mudou para "Publicado"

---

# 🎊 PARABÉNS!

```
╔═══════════════════════════════════════════════════╗
║                                                   ║
║    🎉  CONFIGURAÇÃO 100% COMPLETA!  🎉            ║
║                                                   ║
║  ✅ Aplicação criada no ML                        ║
║  ✅ Credenciais configuradas                      ║
║  ✅ Sistema conectado                             ║
║  ✅ Primeiro produto publicado                    ║
║                                                   ║
║         🚀 PRONTO PARA VENDER! 🚀                 ║
║                                                   ║
╚═══════════════════════════════════════════════════╝
```

---

## 📚 O QUE VOCÊ PODE FAZER AGORA:

### Produtos:
- ✅ Publicar mais produtos
- ✅ Sincronizar estoque automático
- ✅ Atualizar preços
- ✅ Pausar/Ativar anúncios

### Pedidos:
- ✅ Receber pedidos automaticamente (via webhook)
- ✅ Pedidos viram vendas no sistema
- ✅ Clientes criados automaticamente
- ✅ Estoque atualizado automaticamente

### Sincronização:
- ✅ Sync manual quando quiser
- ✅ Sync automático configurável
- ✅ Histórico completo

---

## 🆘 PROBLEMAS?

### Consulte os guias:
- `/docs/GUIA-CRIACAO-APLICACAO-ML.md` (Detalhado)
- `/docs/QUICK-START-ML.md` (Rápido)
- `/docs/CHECKLIST-CONFIGURACAO-ML.md` (Checklist)

### Problemas comuns:
- "Invalid redirect_uri" → URIs devem ser idênticos
- "Invalid credentials" → Verifique Client ID/Secret
- Webhook não funciona → Use ngrok ou HTTPS

---

## ⏭️ PRÓXIMOS PASSOS (OPCIONAL):

1. Configure SSL próprio para produção
2. Configure cron jobs para sync automático
3. Implemente notificações por email
4. Configure backup automático
5. Monitore logs regularmente

---

**🎉 Você completou o wizard com sucesso!**

**Tempo gasto:** ~15-20 minutos  
**Data:** ___/___/2026  
**Configurado por:** _______________

**Status:** ✅ 100% Operacional
