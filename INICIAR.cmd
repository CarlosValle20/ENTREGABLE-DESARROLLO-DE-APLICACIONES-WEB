@echo off
cd /d "%~dp0"

if exist "C:\xampp\php\php.exe" (
    set "PATH=C:\xampp\php;%PATH%"
)

where php >nul 2>&1
if errorlevel 1 (
    echo PHP no esta disponible. Revisa la instalacion de XAMPP o el PATH.
    pause
    exit /b 1
)

echo MySQL debe estar iniciado en XAMPP.
echo Abre http://127.0.0.1:8000/login
php artisan serve --host=127.0.0.1 --port=8000
pause
