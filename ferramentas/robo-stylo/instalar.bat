@echo off
chcp 65001 >nul
cd /d "%~dp0"
title Instalando o Robô Móveis Stylo
echo ==== Instalando o Robo de cadastro da Moveis Stylo ====
echo.

py -3 --version >nul 2>nul
if %errorlevel%==0 (set "PY=py -3" & goto temPython)
python --version >nul 2>nul
if %errorlevel%==0 (set "PY=python" & goto temPython)

echo Python nao encontrado. Instalando o Python 3.12...
winget install -e --id Python.Python.3.12 --accept-package-agreements --accept-source-agreements
echo.
echo Python instalado. FECHE esta janela e de dois cliques em instalar.bat de novo.
pause
exit /b

:temPython
echo Usando: %PY%
if not exist ".venv\Scripts\python.exe" %PY% -m venv .venv
if not exist ".venv\Scripts\python.exe" (
  echo Nao consegui criar o ambiente do robo. Reinstale o Python marcando "tcl/tk" e "pip".
  pause
  exit /b
)
".venv\Scripts\python.exe" -m pip install --upgrade pip
".venv\Scripts\python.exe" -m pip install -r requirements.txt
if errorlevel 1 (
  echo.
  echo Deu erro ao instalar as bibliotecas. Confira a internet e rode o instalar.bat de novo.
  pause
  exit /b
)
".venv\Scripts\python.exe" -c "import tkinter" 2>nul
if errorlevel 1 (
  echo.
  echo O Python foi instalado sem a parte de janelas ^(tcl/tk^).
  echo Rode o instalador do Python de novo, escolha "Modify" e marque "tcl/tk and IDLE".
  pause
  exit /b
)
echo.
echo Baixando o navegador reserva do robo (so e usado se o Edge e o Chrome nao abrirem)...
".venv\Scripts\python.exe" -m playwright install chromium

if not exist "produtos" mkdir "produtos"

echo.
echo Criando o atalho "Robo Moveis Stylo" na Area de Trabalho...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0criar-atalho.ps1"

echo.
echo ==== Pronto! Abra pelo atalho "Robo Moveis Stylo" na Area de Trabalho ====
echo     (ou de dois cliques em "Abrir robo.vbs" nesta pasta)
pause
