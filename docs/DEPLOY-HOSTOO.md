# 🚀 Deploy na Hostoo (Laravel + Vite)

Este guia prepara o FlowManager para produção em hospedagem compartilhada.

Domínio de produção deste projeto: **flowmaneger.com**

## 0) Estratégia recomendada (Git + CI)

Para este projeto, use deploy automatizado via GitHub:

1. Você faz `git push` para a branch principal.
2. O GitHub Actions executa build (`composer install`, `npm ci`, `npm run build`).
3. O workflow publica os arquivos na Hostoo via SFTP.

Motivo: o projeto ignora `public/build` no git, então os assets devem ser gerados no CI (ou localmente antes de enviar por SFTP manual).

## 1) Pré-requisitos na Hostoo

- PHP **8.2+**
- MySQL/MariaDB ativo
- Acesso ao Gerenciador de Arquivos ou SFTP
- SSL ativo no domínio

## 1.1) Pré-requisitos no GitHub

- Repositório no GitHub com branch principal (`main` ou `master`)
- GitHub Actions habilitado
- Secrets configurados no repositório:
	- `HOSTOO_HOST`
	- `HOSTOO_PORT` (normalmente `22`)
	- `HOSTOO_USER`
	- `HOSTOO_PASSWORD` (ou chave SSH, se preferir)
	- `HOSTOO_APP_PATH` (ex: `/home/usuario/flowmanager`)
	- `HOSTOO_PUBLIC_PATH` (ex: `/home/usuario/public_html`)
	- `FLUX_USERNAME`
	- `FLUX_LICENSE_KEY`

### O que são Secrets?

`Secrets` são variáveis sensíveis (senha, host, token, licença) armazenadas com criptografia no GitHub.
Eles **não ficam expostos no código** e são lidos apenas durante execução do workflow.

### Onde cadastrar os Secrets

No GitHub:

1. Abra o repositório
2. `Settings` → `Secrets and variables` → `Actions`
3. Clique em `New repository secret`
4. Crie cada item da lista abaixo

### Significado de cada Secret

- `HOSTOO_HOST`: hostname do servidor SFTP/SSH da Hostoo (ex: `srv123.hostoo.com`)
- `HOSTOO_PORT`: porta SSH/SFTP (geralmente `22`)
- `HOSTOO_USER`: usuário da hospedagem (cPanel/SSH)
- `HOSTOO_PASSWORD`: senha do usuário acima
- `HOSTOO_APP_PATH`: pasta da aplicação Laravel (fora de `public_html`)
- `HOSTOO_PUBLIC_PATH`: pasta pública do site (normalmente `public_html`)
- `FLUX_USERNAME`: usuário/licença da dependência Flux usada no Composer
- `FLUX_LICENSE_KEY`: chave/licença da dependência Flux

### Exemplo prático de valores (modelo)

```text
HOSTOO_HOST=srv123.hostoo.com
HOSTOO_PORT=22
HOSTOO_USER=meu_usuario
HOSTOO_PASSWORD=minha_senha_forte
HOSTOO_APP_PATH=/home/meu_usuario/flowmanager
HOSTOO_PUBLIC_PATH=/home/meu_usuario/public_html
FLUX_USERNAME=seu_usuario_flux
FLUX_LICENSE_KEY=sua_chave_flux
```

## 2) Estrutura recomendada

Em hospedagem compartilhada, o ideal é:

- Código da aplicação fora de `public_html` (ex: `/home/usuario/flowmanager`)
- Conteúdo da pasta `public/` publicado em `public_html`

## 2.1) Ajuste do domínio

- Configure `flowmaneger.com` para apontar para a hospedagem.
- Ative SSL e redirecionamento para HTTPS.
- Confirme que o acesso final abre em `https://flowmaneger.com`.

## 3) Build e preparação local

No projeto local:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Depois confirme que a pasta `public/build` foi gerada.

Use este passo para validação local. No deploy por GitHub Actions, o build deve ser executado no pipeline.

## 3.1) Fluxo Git (dia a dia)

```bash
git add .
git commit -m "seu ajuste"
git push origin main
```

Após o push, acompanhe o workflow em **Actions** no GitHub.

## 4) Upload

1. Envie o projeto para a pasta privada no servidor (fora da web root).
2. Publique os arquivos de `public/` dentro de `public_html`.
3. Ajuste o `index.php` de `public_html` para apontar para a pasta real do projeto, se necessário.

### Exemplo de ajuste em `public_html/index.php`

```php
require __DIR__.'/../flowmanager/vendor/autoload.php';
$app = require_once __DIR__.'/../flowmanager/bootstrap/app.php';
```

Adapte os caminhos conforme sua estrutura real na Hostoo.

## 5) Configuração do ambiente

No servidor, crie o `.env` com base em `.env.example` e use:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://flowmaneger.com
DB_CONNECTION=mysql
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=flowmaneger.com
MERCADOLIVRE_REDIRECT_URI=https://flowmaneger.com/mercadolivre/auth/callback
```

Também preencha no `.env`:

- `APP_KEY` (gerada com `php artisan key:generate`)
- credenciais de banco (`DB_*`)
- e-mail (`MAIL_*`)
- Mercado Livre (`MERCADOLIVRE_APP_ID` e `MERCADOLIVRE_SECRET_KEY`)

## 6) Comandos pós-upload

Execute no servidor, dentro da pasta do projeto:

```bash
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Comando útil extra após update grande:

```bash
php artisan optimize:clear
php artisan optimize
```

## 7) Agendador (cron)

Configure no painel da Hostoo um cron a cada minuto:

```bash
* * * * * php /home/usuario/flowmanager/artisan schedule:run >> /dev/null 2>&1
```

## 8) Filas (queue)

Como `QUEUE_CONNECTION=database`, você precisa de worker ativo. Em hospedagem compartilhada sem supervisor, use uma tarefa cron recorrente:

```bash
* * * * * php /home/usuario/flowmanager/artisan queue:work --stop-when-empty --tries=1 >> /dev/null 2>&1
```

Se a Hostoo limitar execução longa, mantenha `--stop-when-empty` para processamento por janelas.

## 8.1) Checklist GitHub Actions

Seu workflow de deploy deve conter, no mínimo:

1. Checkout do código
2. Setup de PHP 8.2+
3. `composer install --no-dev --optimize-autoloader`
4. Setup Node
5. `npm ci` e `npm run build`
6. Publicação SFTP de:
	- app para `HOSTOO_APP_PATH`
	- `public/*` para `HOSTOO_PUBLIC_PATH`
7. (Opcional) executar comandos artisan remotos via SSH

## 9) Checklist final

- [ ] Site abre em HTTPS
- [ ] Login/sessão funcionando
- [ ] Upload/imagens funcionando (`storage:link`)
- [ ] Migrations aplicadas sem erro
- [ ] Cron do scheduler ativo
- [ ] Queue processando jobs
- [ ] Fluxo Mercado Livre com `MERCADOLIVRE_REDIRECT_URI` de produção
- [ ] Deploy via GitHub Actions concluindo sem erro
- [ ] Último `git push` refletido no site em produção
