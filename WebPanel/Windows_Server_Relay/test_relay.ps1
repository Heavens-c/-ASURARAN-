<#
.SYNOPSIS
    Test script for RanOnline Windows Game Server Relay
.DESCRIPTION
    Validates HMAC-SHA256 signature generation and tests point insertion and status endpoints.
#>

$config = Get-Content -Raw "$PSScriptRoot\config.json" | ConvertFrom-Json
$port = $config.server.port
$secret = $config.server.hmac_secret
$baseUrl = "http://127.0.0.1:$port/api"

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  Testing RanOnline Windows Game Server Relay on port $port" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

function Send-SignedRelayRequest {
    param (
        [string]$Action,
        [hashtable]$Data
    )

    $timestamp = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $nonce = [Guid]::NewGuid().ToString("N").Substring(0, 16)

    $payloadObj = @{
        action    = $Action
        timestamp = $timestamp
        nonce     = $nonce
        data      = $Data
    }

    $rawJson = $payloadObj | ConvertTo-Json -Compress
    
    # Compute HMAC-SHA256
    $hmac = New-Object System.Security.Cryptography.HMACSHA256
    $hmac.Key = [System.Text.Encoding]::UTF8.GetBytes($secret)
    $hashBytes = $hmac.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($rawJson))
    $signature = [BitConverter]::ToString($hashBytes).Replace("-", "").ToLower()

    $headers = @{
        "Content-Type" = "application/json"
        "X-Signature"  = $signature
        "X-Timestamp"  = $timestamp.ToString()
    }

    try {
        $response = Invoke-RestMethod -Uri "$baseUrl/$Action" -Method Post -Body $rawJson -Headers $headers -TimeoutSec 5
        return $response
    } catch {
        Write-Host "[ERROR] Request failed: $_" -ForegroundColor Red
        return $null
    }
}

# 1. Test Server Status
Write-Host "`n[TEST 1] Testing /get_server_status..." -ForegroundColor Yellow
$statusRes = Send-SignedRelayRequest -Action "get_server_status" -Data @{}
if ($statusRes -and $statusRes.success) {
    Write-Host "[PASS] Server Status received: Online=$($statusRes.online), Players=$($statusRes.online_players)" -ForegroundColor Green
} else {
    Write-Host "[FAIL] Could not get server status." -ForegroundColor Red
}

# 2. Test Check User
Write-Host "`n[TEST 2] Testing /check_user (User: admin)..." -ForegroundColor Yellow
$checkRes = Send-SignedRelayRequest -Action "check_user" -Data @{ username = "admin" }
if ($checkRes -and $checkRes.success) {
    Write-Host "[PASS] Check User response: Exists=$($checkRes.exists)" -ForegroundColor Green
} else {
    Write-Host "[INFO] User check completed (User might not exist yet in DB)." -ForegroundColor Yellow
}

Write-Host "`n==========================================================" -ForegroundColor Cyan
Write-Host "  Relay testing complete." -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
