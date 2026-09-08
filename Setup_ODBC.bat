@echo off
setlocal enabledelayedexpansion
title RanOnline PostgreSQL 32-Bit ODBC Auto-Setup

echo =======================================================
echo    RanOnline PostgreSQL 32-Bit ODBC Configuration
echo =======================================================
echo.

set "SCRIPT_DIR=%~dp0"
pushd "%SCRIPT_DIR%"

:: Locate 32-bit PowerShell
set "PS32=%SystemRoot%\SysWOW64\WindowsPowerShell\v1.0\powershell.exe"
if not exist "%PS32%" (
    set "PS32=%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe"
)

:: Check for driver files in Tik\psqlODBC32 or _Bin\Tool
set "DRIVER_PATH=%SCRIPT_DIR%Tik\psqlODBC32\podbc30a.dll"
if not exist "%DRIVER_PATH%" (
    set "DRIVER_PATH=%SCRIPT_DIR%_Bin\Tool\podbc30a.dll"
)

if not exist "%DRIVER_PATH%" (
    echo [ERROR] podbc30a.dll was not found!
    echo Looked in:
    echo   %SCRIPT_DIR%Tik\psqlODBC32\podbc30a.dll
    echo   %SCRIPT_DIR%_Bin\Tool\podbc30a.dll
    pause
    popd
    exit /b 1
)

echo [OK] Using 32-bit ODBC Driver at:
echo      %DRIVER_PATH%
echo.

:: Database Connection Parameters
set "PG_HOST=127.0.0.1"
set "PG_PORT=5432"
set "PG_USER=postgres"
set "PG_PASS=12345"

echo [1/3] Registering 32-bit PostgreSQL ANSI Driver in Registry...
"%PS32%" -NoProfile -ExecutionPolicy Bypass -Command ^
  "New-Item -Path 'HKCU:\Software\ODBC\ODBCINST.INI\PostgreSQL ANSI' -Force | Out-Null;" ^
  "Set-ItemProperty -Path 'HKCU:\Software\ODBC\ODBCINST.INI\PostgreSQL ANSI' -Name 'Driver' -Value '%DRIVER_PATH%'; " ^
  "Set-ItemProperty -Path 'HKCU:\Software\ODBC\ODBCINST.INI\PostgreSQL ANSI' -Name 'Setup' -Value '%DRIVER_PATH%'; " ^
  "New-Item -Path 'HKCU:\Software\ODBC\ODBCINST.INI\ODBC Drivers' -Force | Out-Null;" ^
  "Set-ItemProperty -Path 'HKCU:\Software\ODBC\ODBCINST.INI\ODBC Drivers' -Name 'PostgreSQL ANSI' -Value 'Installed';"

echo [2/3] Configuring DSNs: RanUser, RanGame1, RanLog, RanShop...
"%PS32%" -NoProfile -ExecutionPolicy Bypass -Command ^
  "$dsns = @(" ^
  "  @{ Name = 'RanUser';  Db = 'ranuser' }," ^
  "  @{ Name = 'RanGame1'; Db = 'rangame1' }," ^
  "  @{ Name = 'RanLog';   Db = 'ranlog' }," ^
  "  @{ Name = 'RanShop';  Db = 'ranshop' }" ^
  ");" ^
  "foreach ($d in $dsns) {" ^
  "  $p = 'HKCU:\Software\ODBC\ODBC.INI\' + $d.Name;" ^
  "  New-Item -Path $p -Force | Out-Null;" ^
  "  Set-ItemProperty -Path $p -Name 'Driver' -Value '%DRIVER_PATH%';" ^
  "  Set-ItemProperty -Path $p -Name 'Servername' -Value '%PG_HOST%';" ^
  "  Set-ItemProperty -Path $p -Name 'Port' -Value '%PG_PORT%';" ^
  "  Set-ItemProperty -Path $p -Name 'Database' -Value $d.Db;" ^
  "  Set-ItemProperty -Path $p -Name 'Username' -Value '%PG_USER%';" ^
  "  Set-ItemProperty -Path $p -Name 'Password' -Value '%PG_PASS%';" ^
  "  Set-ItemProperty -Path $p -Name 'ByteaAsLongVarBinary' -Value '1';" ^
  "  Set-ItemProperty -Path $p -Name 'BoolsAsChar' -Value '0';" ^
  "  Set-ItemProperty -Path $p -Name 'UseServerSidePrepare' -Value '1';" ^
  "  Set-ItemProperty -Path $p -Name 'Protocol' -Value '7.4';" ^
  "  Set-ItemProperty -Path $p -Name 'UpdatableCursors' -Value '1';" ^
  "  Set-ItemProperty -Path $p -Name 'ExtraSysArchitecture' -Value 'Standard';" ^
  "  Set-ItemProperty -Path $p -Name 'LowerCaseIdentifier' -Value '0';" ^
  "  Set-ItemProperty -Path 'HKCU:\Software\ODBC\ODBC.INI\ODBC Data Sources' -Name $d.Name -Value 'PostgreSQL ANSI';" ^
  "}"

echo [3/3] Verifying 32-bit ODBC Connection to All Databases...
"%PS32%" -NoProfile -ExecutionPolicy Bypass -Command ^
  "$dsns = @('RanUser', 'RanGame1', 'RanLog', 'RanShop');" ^
  "$allOk = $true;" ^
  "foreach ($dsn in $dsns) {" ^
  "  try {" ^
  "    $conn = New-Object System.Data.Odbc.OdbcConnection(\"DSN=$dsn;UID=%PG_USER%;PWD=%PG_PASS%\");" ^
  "    $conn.Open();" ^
  "    Write-Host \"   [SUCCESS] $dsn connected successfully.\" -ForegroundColor Green;" ^
  "    $conn.Close();" ^
  "  } catch {" ^
  "    Write-Host \"   [FAILED] $dsn connection failed: $($_.Exception.Message)\" -ForegroundColor Red;" ^
  "    $allOk = $false;" ^
  "  }" ^
  "}" ^
  "if (-not $allOk) { exit 1 }"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo =======================================================
    echo   [SUCCESS] All 4 PostgreSQL ODBC DSNs Configured!
    echo =======================================================
) else (
    echo.
    echo =======================================================
    echo   [WARNING] One or more DSNs failed connection test.
    echo   Please make sure PostgreSQL service is running.
    echo =======================================================
)

popd
echo.
pause
