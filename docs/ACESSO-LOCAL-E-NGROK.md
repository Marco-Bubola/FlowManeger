# Acesso local e via ngrok (127.0.0.1 + ngrok)

## Problema: ERR_CONNECTION_CLOSED em 127.0.0.1:8000

Se você consegue acessar a aplicação pelo ngrok mas **não** pelo endereço local (`http://127.0.0.1:8000` ou `http://localhost:8000`), as causas mais comuns são:

1. **Servidor Laravel não está rodando** quando você tenta acessar pelo navegador.
2. **Ordem de início**: o ngrok só repassa as requisições para a sua máquina; é preciso que o **Laravel esteja rodando primeiro** na porta 8000.
3. **Firewall ou antivírus** bloqueando conexões diretas na porta 8000.

## Solução: usar os dois (local + ngrok)

### Passo 1 – Iniciar o servidor Laravel (sempre primeiro)

Abra um terminal na pasta do projeto e inicie o servidor escutando em todas as interfaces:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Ou use o script:

```powershell
.\start-servidor.bat
```

**Deixe esse terminal aberto.** Enquanto o servidor estiver rodando, você pode usar:

- **Local:** `http://127.0.0.1:8000` ou `http://localhost:8000`
- **Ngrok:** depois de iniciar o túnel (passo 2), use a URL que o ngrok mostrar.

### Passo 2 – (Opcional) Iniciar o ngrok

Em **outro** terminal, na mesma pasta do projeto:

```powershell
ngrok http 8000
```

Ou:

```powershell
.\setup-ngrok.bat
```

O ngrok vai exibir uma URL pública (ex.: `https://xxxx.ngrok-free.app`) que redireciona para o seu `localhost:8000`.

### Resumo da ordem

1. Terminal 1: `php artisan serve --host=0.0.0.0 --port=8000` (ou `.\start-servidor.bat`).
2. Terminal 2 (opcional): `ngrok http 8000` (ou `.\setup-ngrok.bat`).

Com isso, você passa a acessar:

- Pelo navegador local: `http://127.0.0.1:8000` ou `http://localhost:8000`.
- Pela internet: URL HTTPS que o ngrok mostrar (ex.: `https://xxxx.ngrok-free.app`).

## Se ainda der ERR_CONNECTION_CLOSED no local

1. **Confirme se o servidor está rodando**  
   No terminal onde rodou `php artisan serve`, deve aparecer algo como:  
   `Server running on [http://0.0.0.0:8000]`.

2. **Teste com `localhost` em vez de `127.0.0.1`**  
   Acesse: `http://localhost:8000/login`.

3. **Verifique a porta 8000 no Windows**  
   No PowerShell:
   ```powershell
   netstat -ano | findstr :8000
   ```
   Deve haver uma linha com `LISTENING` na porta 8000.

4. **Firewall do Windows**  
   Se estiver bloqueando, permita o PHP ou crie uma regra para a porta 8000 em conexões de entrada em "localhost" / "rede privada".

5. **Antivírus**  
   Alguns antivírus bloqueiam servidores locais; teste temporariamente desativando ou adicionando exceção para a pasta do projeto e para `php.exe`.

## Por que `--host=0.0.0.0`?

- `php artisan serve` sem opções costuma escutar só em `127.0.0.1`.
- Em alguns ambientes Windows, usar `--host=0.0.0.0` faz o servidor aceitar conexões em todas as interfaces, o que pode evitar problemas de “conexão encerrada” tanto no acesso direto (127.0.0.1 / localhost) quanto quando o ngrok se conecta ao `localhost:8000`.

Assim você consegue usar **acesso local e ngrok** ao mesmo tempo, sem conflito.
