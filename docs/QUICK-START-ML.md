# 📝 QUICK START - CONFIGURAÇÃO MERCADO LIVRE

**⏱️ Tempo estimado:** 15-20 minutos

---

## 🎯 PASSO A PASSO RÁPIDO

### 1️⃣ CRIAR APLICAÇÃO NO ML (5 min)

1. **Acesse:** https://developers.mercadolivre.com.br/devcenter/
2. **Faça login** com sua conta ML
3. **Clique em** "Criar uma aplicação"

**Preencha:**
```
Nome: FlowManager - Integração ML
Nome curto: flowmanager-ml
Descrição: Sistema de gestão integrado com ML
```

---

### 2️⃣ CONFIGURAR URLS (2 min)

#### Para Desenvolvimento Local:

**Opção A - Usar ngrok (Recomendado):**

```powershell
# Jeito mais rápido (sem precisar de admin):
$ProgressPreference = 'SilentlyContinue'; Invoke-WebRequest -Uri "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-windows-amd64.zip" -OutFile "$env:TEMP\ngrok.zip"; Expand-Archive -Path "$env:TEMP\ngrok.zip" -DestinationPath "$env:USERPROFILE\ngrok" -Force

# Executar:
cd $env:USERPROFILE\ngrok
.\ngrok.exe http 8000

# OU via Chocolatey (precisa de admin):
# choco install ngrok -y
# ngrok http 8000
```

Copie a URL HTTPS gerada (ex: `https://abcd-1234.ngrok-free.app`)

**Configure no ML:**
```
Redirect URI: https://abcd-1234.ngrok-free.app/mercadolivre/auth/callback
Webhook URL: https://abcd-1234.ngrok-free.app/mercadolivre/webhooks
```

**Opção B - Usar localhost (Apenas para testes iniciais):**
```
Redirect URI: http://localhost:8000/mercadolivre/auth/callback
```
⚠️ **Nota:** Webhooks NÃO funcionarão com localhost

---

### 3️⃣ SELECIONAR PERMISSÕES (1 min)

Marque:
- ✅ **read** (Leitura)
- ✅ **write** (Escrita)
- ✅ **offline_access** (IMPORTANTE!)

Tópicos de webhook:
- ✅ **Orders** (Pedidos)
- ✅ **Items** (Produtos)
- ✅ **Questions** (Perguntas)
- ✅ **Messages** (Mensagens)

---

### 4️⃣ COPIAR CREDENCIAIS (1 min)

Após salvar, copie:
- **Client ID:** `1234567890123456`
- **Client Secret:** `abcd...XYZ`

---

### 5️⃣ CONFIGURAR .ENV (2 min)

**Abra:** `c:\projetos\FlowManeger\.env`

**Cole suas credenciais:**

```env
# Suas credenciais do ML
ML_CLIENT_ID=1234567890123456
ML_CLIENT_SECRET=abcd...XYZ

# Se estiver usando ngrok
ML_REDIRECT_URI=https://abcd-1234.ngrok-free.app/mercadolivre/auth/callback
ML_WEBHOOK_URL=https://abcd-1234.ngrok-free.app/mercadolivre/webhooks
```

---

### 6️⃣ LIMPAR CACHE (30 seg)

```powershell
php artisan config:clear
php artisan cache:clear
```

---

### 7️⃣ TESTAR (5 min)

1. **Acesse:** http://localhost:8000/mercadolivre/settings

2. **Clique em** "Conectar com Mercado Livre"

3. **Autorize** no ML

4. **Deve voltar** com status "Conectado" ✅

---

### 8️⃣ PUBLICAR PRODUTO (5 min)

1. **Acesse:** http://localhost:8000/mercadolivre/products

2. **Selecione** um produto

3. **Clique** "Publicar no Mercado Livre"

4. **Preencha** os dados

5. **Publique!** 🎉

---

## 🆘 PROBLEMAS COMUNS

### ❌ "Invalid redirect_uri"
**Solução:** URI no `.env` deve ser **exatamente igual** ao configurado no ML

### ❌ "Invalid client credentials"
**Solução:** Verifique se copiou Client ID e Secret corretamente (sem espaços)

### ❌ Webhook não funciona
**Solução:** Use ngrok ou domínio com HTTPS (localhost não funciona)

---

## 📞 PRECISA DE AJUDA?

**Guia completo:** `/docs/GUIA-CRIACAO-APLICACAO-ML.md`

**Tutorial ML:** https://developers.mercadolivre.com.br/pt_br/crie-uma-aplicacao-no-mercado-livre

---

## ✅ PRONTO!

Em 15-20 minutos você tem:
- ✅ Aplicação criada no ML
- ✅ Sistema configurado
- ✅ Primeira conexão feita
- ✅ Pronto para publicar produtos

🎊 **Parabéns!**
