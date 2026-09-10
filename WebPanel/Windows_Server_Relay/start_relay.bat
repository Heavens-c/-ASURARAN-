@echo off
title RanOnline Game Server Relay Daemon
cd /d "%~dp0"

echo ==========================================================
echo       RanOnline Windows Game Server Relay Launcher
echo ==========================================================
echo.

where node >nul 2>nul
if %ERRORLEVEL% equ 0 (
    echo [OK] Node.js detected. Checking dependencies...
    if not exist "node_modules\" (
        echo [INFO] Installing required node packages...
        call npm install --omit=dev
    )
    echo [RUN] Starting Node.js Relay Server...
    node relay_server.js
    goto :end
)

where python >nul 2>nul
if %ERRORLEVEL% equ 0 (
    echo [OK] Python detected. Starting Python Relay Server...
    python relay_server.py
    goto :end
)

echo [ERROR] Neither Node.js nor Python was found on this system!
echo Please install Node.js (LTS) or Python 3.x to run the relay.
pause

:end
