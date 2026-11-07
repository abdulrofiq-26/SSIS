@echo off
setlocal
cd /d "%~dp0"

echo [SSIS] Starting all development services in this terminal.
echo [SSIS] Python startup loads ResNet50 and may take 30-90 seconds.
echo [SSIS] Press Ctrl+C to stop the services.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\start-ssis.ps1"
set "EXIT_CODE=%ERRORLEVEL%"
endlocal & exit /b %EXIT_CODE%
