@echo off
REM ============================================================
REM  Start Laravel Queue Worker untuk SSIS
REM  Memproses job indexing vektor (ResNet50) di background.
REM  Biarkan jendela ini tetap terbuka.
REM ============================================================
cd /d "%~dp0.."
echo [SSIS] Memulai queue worker...
php artisan queue:work --tries=3 --timeout=300
pause