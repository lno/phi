@echo off

rem ############################################################################
rem # phi console for Windows
rem ############################################################################

if "%OS%"=="Windows_NT" @setlocal

if "%PHP_COMMAND%"=="" set PHP_COMMAND=php.exe
%PHP_COMMAND% %~p0phic.php %*

if "%OS%"=="Windows_NT" @endlocal
