@echo off
setlocal enabledelayedexpansion
title RanOnline Server Cluster Launcher

echo =======================================================
echo         Starting RanOnline Server Cluster
echo =======================================================
echo.

set "SCRIPT_DIR=%~dp0"
set "TOOL_DIR=%SCRIPT_DIR%_Bin\Tool"
if not exist "%TOOL_DIR%\ServerSession.exe" (
    set "TOOL_DIR=%SCRIPT_DIR%"
)

if not exist "%TOOL_DIR%\ServerSession.exe" (
    echo [ERROR] ServerSession.exe not found in:
    echo   %TOOL_DIR%
    pause
    exit /b 1
)

cd /d "%TOOL_DIR%"

echo [1/4] Starting Session Server (Port 5001)...
start "ServerSession" "%TOOL_DIR%\ServerSession.exe" start
timeout /t 3 /nobreak > nul

echo [2/4] Starting Login Server (Port 5101)...
start "ServerLogin" "%TOOL_DIR%\ServerLogin.exe" start
timeout /t 3 /nobreak > nul

echo [3/4] Starting Agent Server (Port 5201)...
start "ServerAgent" "%TOOL_DIR%\ServerAgent.exe" start
timeout /t 4 /nobreak > nul

echo [4/4] Starting Field Server (Port 5301)...
start "ServerField" "%TOOL_DIR%\ServerField.exe" start
timeout /t 3 /nobreak > nul

echo.
echo =======================================================
echo    [SUCCESS] RanOnline Server Daemons Launched!
echo.
echo    - ServerSession : Port 5001 (IPC Coordinator)
echo    - ServerLogin   : Port 5101 (Client Authentication)
echo    - ServerAgent   : Port 5201 (Lobby & Social Router)
echo    - ServerField   : Port 5301 (World / Zone Engine)
echo =======================================================
echo.
pause
