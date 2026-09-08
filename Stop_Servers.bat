@echo off
setlocal enabledelayedexpansion
title Stop RanOnline Server Cluster

echo =======================================================
echo         Stopping RanOnline Server Cluster
echo =======================================================
echo.

echo [1/4] Stopping Field Server...
taskkill /IM ServerField.exe /F 2>nul
echo [2/4] Stopping Agent Server...
taskkill /IM ServerAgent.exe /F 2>nul
echo [3/4] Stopping Login Server...
taskkill /IM ServerLogin.exe /F 2>nul
echo [4/4] Stopping Session Server...
taskkill /IM ServerSession.exe /F 2>nul

echo.
echo =======================================================
echo    [OK] All RanOnline server daemons stopped.
echo =======================================================
echo.
pause
