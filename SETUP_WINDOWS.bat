@echo off
setlocal
pushd "%~dp0UoV_Campus_Connect" || exit /b 1
set "GROUP17_PHP=C:\xampp\php\php.exe"
if not exist "%GROUP17_PHP%" (
    echo PHP was not found at C:\xampp\php\php.exe.
    echo Edit GROUP17_PHP in this file to match your XAMPP installation.
    popd
    pause
    exit /b 1
)
"%GROUP17_PHP%" scripts\setup.php --demo
set "GROUP17_RESULT=%ERRORLEVEL%"
if "%GROUP17_RESULT%"=="0" (
    echo.
    echo Open http://localhost/Group-17_Project/
    echo If you renamed the folder, use its new name in the URL.
)
popd
pause
exit /b %GROUP17_RESULT%
