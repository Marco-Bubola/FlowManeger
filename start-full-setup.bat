@echo off
REM Executa instalação completa do FlowManager (dependências + migrações + servidores)
REM Usage: start-full-setup.bat

echo.
echo ============================================================
echo FlowManeger - Instalação Completa
echo Instalando dependências, executando migrações e iniciando servidores...
echo ============================================================
echo.

node "%~dp0\start.cjs" --full-setup
