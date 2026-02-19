@echo off
REM ============================================
REM Inicia o servidor Laravel para acesso LOCAL e via NGROK
REM Use: 1) Execute ESTE script primeiro
REM      2) Opcional: em outro terminal, execute setup-ngrok.bat
REM Acesso: http://127.0.0.1:8000 ou http://localhost:8000
REM ============================================

echo.
echo ========================================
echo   SERVIDOR LARAVEL - FlowManeger
echo ========================================
echo.
echo Porta: 8000
echo Acesso local: http://127.0.0.1:8000 ou http://localhost:8000
echo.
echo [INFO] Deixe esta janela aberta.
echo [INFO] Para usar ngrok, abra OUTRO terminal e execute: ngrok http 8000
echo.
echo Pressione Ctrl+C para parar o servidor
echo ========================================
echo.

REM Escuta em 0.0.0.0 para aceitar conexões locais e do ngrok
php artisan serve --host=0.0.0.0 --port=8000

pause
