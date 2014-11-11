@echo off

rem ############################################################################
rem # phi CLI for Windows
rem ############################################################################

if "%OS%"=="Windows_NT" @setlocal

if "%PHP_COMMAND%"=="" set PHP_COMMAND=php.exe
if "%PHI_HOME%"=="" set PHI_HOME=@PHI_HOME@

%PHP_COMMAND% -d html_errors=off "%PHI_HOME%\command\phi.php" %1

if "%OS%"=="Windows_NT" @endlocal
