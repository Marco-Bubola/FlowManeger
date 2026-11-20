@echo off
rem Abre a janela nova do robo (sem tela preta)
cd /d "%~dp0"
start "" wscript "%~dp0Abrir robo.vbs" %*
