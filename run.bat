@echo off
REM Run FlowManager.exe with optional arguments
REM Usage: run.bat [--dry-run] [--build] [--skip-migrate]

"%~dp0\FlowManager.exe" %*
