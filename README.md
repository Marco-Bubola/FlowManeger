# FlowManeger

Uma breve documentação para iniciar o projeto localmente e como usar o executável gerado (`FlowManager.exe`).

## Iniciar o projeto (passos manuais)

1. Instale dependências PHP e Node:

```powershell
composer install
npm install
```

2. Copie e configure o arquivo de ambiente (se necessário):

```powershell
copy .env.example .env
# editar .env conforme necessário
```

3. Gere a chave da aplicação e rode migrações/seeders:

```powershell
php artisan key:generate
php artisan migrate --seed
```

4. Inicie assets e servidor:

```powershell
npm run dev
php artisan serve
```

## Executável automático

Um executável foi gerado a partir do script `start.js`: `FlowManager.exe`.

Para rodar no Windows, basta executar o arquivo `FlowManager.exe` na pasta do projeto. Ele executará os passos principais automaticamente (instalação de dependências, geração de chave, migrações, compilação e start). Caso o executável não esteja no PATH, execute-o diretamente na pasta do projeto.

Se preferir gerar o executável você mesmo, use `pkg` (Node.js). Passos:

## FlowManeger

Bem-vindo ao repositório FlowManeger — uma ferramenta baseada em Laravel para gerenciar finanças, vendas e exportação de relatórios. Este README traz as instruções essenciais para preparar, executar e empacotar o projeto no Windows.

Autor: Marco Bubola

Principais pontos
- Aplicação Laravel (PHP) com frontend bundling via Node (Vite).
- Exportação de relatórios em CSV/XLSX (package: maatwebsite/excel).
- Script auxiliar `start.js` e um executável opcional `FlowManager.exe` para automatizar tarefas de setup no Windows.

Requisitos
- PHP 8.x compatível com o projeto
- Composer
- Node.js 18+ e npm
- Banco de dados suportado pelo Laravel (ex: MySQL, SQLite, Postgres)

Instalação (modo local)
1. Clone o repositório e entre na pasta do projeto.
2. Instale dependências PHP e Node:

```powershell
composer install
npm ci
```

3. Copie o arquivo de ambiente e ajuste as variáveis (DB, MAIL, etc):

```powershell
copy .env.example .env
# editar .env conforme necessário
```

4. Gere a chave da aplicação, rode migrações e seeders (quando aplicável):

```powershell
php artisan key:generate
php artisan migrate --seed
```

5. Compile assets e inicie o servidor de desenvolvimento:

```powershell
npm run dev
php artisan serve --host=127.0.0.1 --port=8000
```

Executável no Windows (FlowManager.exe)
Existe um wrapper (`start.js`) que foi usado para gerar um executável chamado `FlowManager.exe`. O executável pode automatizar a instalação de dependências, geração de chave, migrações e start do ambiente no Windows.

Para executar o binário:

```powershell
# execute diretamente na pasta do projeto
.\FlowManager.exe
```

Gerar o executável você mesmo (opcional)
Se preferir gerar localmente o `.exe`, use o `pkg` (Node.js). No PowerShell pode ser necessário usar o `pkg.cmd` instalado no `%APPDATA%/npm`:

```powershell
npm install -g pkg
& "$env:APPDATA\npm\pkg.cmd" start.js --targets node18-win-x64 --output FlowManager.exe
```

Observações
- O executável empacota o runtime Node, mas continua chamando comandos externos como `composer` e `php` — certifique-se de tê-los instalados globalmente.
- Em sistemas com execução de scripts PowerShell restrita, prefira usar os `*.cmd` do npm (por exemplo, `pkg.cmd`).

Exportação CSV / XLSX
O projeto utiliza (ou tem suporte para) `maatwebsite/excel` para gerar relatórios em CSV/XLSX.

Instalação do pacote (se necessário):

```powershell
composer require maatwebsite/excel
```

Exemplo de rota de exportação (ajuste conforme rotas do seu projeto):

GET /reports/vendas/export?format=csv&client_id=1&from=2025-01-01&to=2025-10-01

Contribuindo
- Abra uma issue descrevendo a sugestão ou bug.
- Para pequenas correções, crie um pull request contra a branch `main`.

# FlowManeger

Bem-vindo ao repositório FlowManeger — uma aplicação Laravel para gerenciar finanças, vendas e relatórios.

Autor: Marco Bubola

## Visão geral
- Backend: Laravel (PHP)
- Frontend: Vite + assets gerados via Node.js
- Exportação de relatórios: suporte a CSV/XLSX (maatwebsite/excel)
- Script auxiliar: `start.js` (há um `FlowManager.exe` gerado opcionalmente para Windows)

## Requisitos
- PHP 8.x
- Composer
- Node.js 18+ e npm
- Banco de dados compatível com Laravel (MySQL, SQLite, Postgres etc.)

## Quick start (Windows)
1. Abra o PowerShell na pasta do projeto.
2. Instale dependências:

```powershell
composer install
npm ci
```

3. Configure o ambiente:

```powershell
copy .env.example .env
# editar .env (DB, MAIL, etc.)
```

4. Gere a chave e rode migrations/seeders:

```powershell
php artisan key:generate
php artisan migrate --seed
```

5. Compile assets e inicie o servidor de desenvolvimento:

```powershell
npm run dev
php artisan serve --host=127.0.0.1 --port=8000
```

## Executável (opcional)
Um executável (`FlowManager.exe`) foi criado a partir de `start.js` para facilitar o setup no Windows. Ele automatiza passos como instalação de dependências e migrações, mas ainda depende de comandos externos (`php`, `composer`).

Para executar:

```powershell
.\FlowManager.exe
```

Para gerar o `.exe` localmente (opcional):

```powershell
npm install -g pkg
& "$env:APPDATA\npm\pkg.cmd" start.js --targets node18-win-x64 --output FlowManager.exe
```

Observações:
- O `.exe` embute o runtime Node.js, porém requer PHP/Composer instalados para executar comandos Laravel.
- Se o PowerShell bloquear execução de scripts, use os wrappers `*.cmd` do npm (ex.: `pkg.cmd`).

## Exportação CSV / XLSX
O projeto tem integração (ou suporte) para `maatwebsite/excel`.

Instalação:

```powershell
composer require maatwebsite/excel
```

Exemplo de rota de exportação (ajuste conforme sua implementação):

GET /reports/vendas/export?format=csv&client_id=1&from=2025-01-01&to=2025-10-01

## Contribuindo
- Abra uma issue para bugs e sugestões.
- Fork + pull request para correções e novas funcionalidades.

## Contato
- Marco Bubola — mantenedor

## Licença
- Consulte o arquivo `LICENSE` neste repositório.

---

Se quiser, adapto este README com instruções específicas do seu ambiente, exemplos de rotas reais do projeto ou scripts úteis para desenvolvimento.

