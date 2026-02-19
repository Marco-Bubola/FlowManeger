@echo off
REM ============================================
REM SCRIPT: Setup ngrok para Mercado Livre
REM Descrição: Inicia túnel HTTPS para desenvolvimento
REM ============================================

echo.
echo ========================================
echo   MERCADO LIVRE - SETUP NGROK
echo ========================================
echo.

REM Verificar se ngrok está instalado
where ngrok >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERRO] ngrok não encontrado!
    echo.
    echo Por favor, instale o ngrok:
    echo   1. Acesse: https://ngrok.com/download
    echo   2. Baixe e extraia o ngrok.exe
    echo   3. Adicione ao PATH ou coloque na pasta do projeto
    echo.
    echo Ou instale via Chocolatey:
    echo   choco install ngrok
    echo.
    pause
    exit /b 1
)

echo [OK] ngrok encontrado!
echo.

REM Verificar configuração do token
echo Verificando token do ngrok...
ngrok config check >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [AVISO] ngrok não está configurado com token!
    echo.
    echo Para usar ngrok:
    echo   1. Crie conta em: https://dashboard.ngrok.com/signup
    echo   2. Copie seu token em: https://dashboard.ngrok.com/get-started/your-authtoken
    echo   3. Execute: ngrok config add-authtoken SEU_TOKEN_AQUI
    echo.
    set /p CONTINUE="Deseja continuar mesmo assim? (S/N): "
    if /i not "%CONTINUE%"=="S" exit /b 1
)

echo.
echo ========================================
echo   INICIANDO TUNEL HTTPS
echo ========================================
echo.
echo Porta Laravel: 8000
echo Protocolo: HTTPS
echo.
echo [INFO] Deixe esta janela aberta!
echo [INFO] Copie a URL HTTPS que aparecer abaixo
echo [INFO] Use esta URL no Mercado Livre Developer
echo.
echo Exemplo:
echo   https://abc123.ngrok.io/mercadolivre/auth/callback
echo.
echo Pressione Ctrl+C para parar o túnel
echo.
echo ========================================
echo.

REM Iniciar ngrok
ngrok http 8000 --log=stdout

echo.
echo Túnel encerrado.
pause
