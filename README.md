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

```powershell
# instalar pkg globalmente
npm install -g pkg

# usar o wrapper .cmd caso o PowerShell bloqueie scripts (.ps1)
& "$env:APPDATA\\npm\\pkg.cmd" start.js --targets node18-win-x64 --output FlowManager.exe
```

Observações:

- Em sistemas com execução de scripts PowerShell desabilitada, use `pkg.cmd` diretamente como no exemplo acima.
- O executável empacota o Node.js runtime e o script `start.js`. Ele ainda chama comandos externos como `composer` e `php`, por isso é necessário ter PHP e Composer instalados globalmente.

## Problemas comuns

- "pkg: O termo 'pkg' não é reconhecido": instale com `npm install -g pkg` ou use `pkg.cmd` no `AppData\\npm`.
- "Execução de scripts desabilitada": execute `Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser` no PowerShell como administrador, ou use os arquivos `.cmd` diretamente.

Bom trabalho! Se quiser, posso:

## Exportacao CSV / XLSX (Laravel-Excel)

Para habilitar a exportacao de relatórios em CSV/XLSX usamos o pacote `maatwebsite/excel`.

Instalacao:

```powershell
cd C:\projetos\FlowManeger
composer require maatwebsite/excel
```

Uso rapido:

- Rota autenticada para exportar vendas: `/reports/vendas/export?format=csv&client_id=1&from=2025-01-01&to=2025-10-01`
- Parametro `format` pode ser `csv` ou `xlsx`.
- Exemplo no controller: `ReportExportController@exportVendas`.

Se o pacote nao estiver instalado, ha fallback para gerar um CSV simples no storage e forcar o download.

- Incluir o `start.js` no repositório (já existe)
- Gerar o executável na sua máquina agora (já tentei e gerei; verifique `C:\\projetos\\FlowManeger`)
- Atualizar o script para suportar flags (p.ex. pular migrations)
# FlowManeger

