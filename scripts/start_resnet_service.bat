@echo off
REM ============================================================
REM  Start Python ResNet50 Service (CBIR) untuk SSIS
REM  Menjalankan Flask service yang memuat model ResNet50 sekali.
REM  Biarkan jendela ini tetap terbuka selama aplikasi dipakai.
REM ============================================================
cd /d "%~dp0.."
echo [SSIS] Memulai Python ResNet50 Service di http://127.0.0.1:5005 ...
echo [SSIS] Memuat model, mohon tunggu 30-90 detik pada saat pertama kali...
"venv-tf\Scripts\python.exe" "scripts\resnet_service.py"
pause