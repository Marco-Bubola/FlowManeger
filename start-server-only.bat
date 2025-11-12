@echo off
REM Inicia apenas os servidores do FlowManager (comportamento padrão)
REM Usage: start-server-only.bat

echo.
echo ============================================================
echo FlowManeger - Modo Servidor Apenas (Padrão)
echo Pulando instalação de dependências e migrações...
echo ============================================================
echo.

node "%~dp0\start.cjs"
