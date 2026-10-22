@echo off
rem Starts the three parts of the demo: the database, the real SuiteCRM, and the AI Dev Copilot.
cd /d %~dp0
start "MariaDB" /min runtime\mariadb\bin\mysqld.exe --datadir=%~dp0runtime\mariadb-data --port=3307 --bind-address=127.0.0.1 --console
start "SuiteCRM" /min runtime\php\php.exe -c runtime\php\php.ini -S 127.0.0.1:8080 -t runtime\suitecrm
timeout /t 8 >nul
start "AI Dev Copilot" node server.js
echo.
echo  AI Dev Copilot : http://localhost:3000
echo  SuiteCRM       : http://localhost:8080   (admin / Admin#2026)
