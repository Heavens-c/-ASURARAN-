# RanOnline Windows Game Server Relay Daemon — DevOps Guide

## Security Architecture

```text
[ Namecheap Shared Host (cPanel) ]
         │
         │  HMAC-SHA256 Signed JSON
         │  X-Signature + X-Timestamp (Anti-Replay)
         ▼
[ Windows Firewall ] ─── Only allows incoming traffic from Namecheap IP on Port 8088
         │
         ▼
[ relay_server.js / relay_server.py ] ─── Validates Signature & Whitelist
         │
         │  Localhost Only (127.0.0.1:5432)
         ▼
[ PostgreSQL: ranuser, rangame, ranshop, ranlog ]
```

### Why this is 100% Secure Against Script Kiddies & Exploits:
1. **PostgreSQL is Never Exposed**: Port `5432` binds strictly to `127.0.0.1`. No outside attacker can brute-force or SQL-inject your database directly.
2. **Cryptographic Authentication**: Every request requires a 64-character HMAC-SHA256 signature calculated from the raw payload and a shared secret key. Without the key, any packet sent to port 8088 is immediately rejected with HTTP 401.
3. **Strict Whitelist**: Windows Firewall and the relay daemon reject any IP that isn't your Namecheap server's IP address.
4. **Zero Shell Commands**: The relay contains zero `child_process`, `exec`, or `eval` calls. Only strictly typed, parameterized SQL queries are executed.

---

## Step 1: Configure `config.json`

Open `config.json` and adjust:
- `server.hmac_secret`: Must match `GAME_RELAY_SECRET` in `WebPanel/SharedHost_Namecheap/includes/config.php`.
- `server.allowed_ips`: Add your Namecheap server's public IP address (found in your Namecheap cPanel right-hand sidebar).
- `postgres.password`: Your local PostgreSQL `postgres` user password.

---

## Step 2: Configure Windows Firewall

Run PowerShell as Administrator to allow incoming traffic on port 8088 **only from Namecheap**:

```powershell
# Replace 198.54.114.10 with your actual Namecheap server IP
New-NetFirewallRule -DisplayName "RanOnline Web Relay" `
    -Direction Inbound `
    -LocalPort 8088 `
    -Protocol TCP `
    -Action Allow `
    -RemoteAddress "YOUR_NAMECHEAP_SERVER_IP"
```

---

## Step 3: Run the Relay Daemon

### Option A: Quick Run (Batch Launcher)
Double-click `start_relay.bat`. It will automatically install `node_modules` and start the server.

### Option B: Node.js (Production with PM2)
If you have Node.js and PM2 installed:
```powershell
npm install
npm install -g pm2
pm2 start relay_server.js --name "ran-relay"
pm2 save
pm2 startup
```

### Option C: Python 3
If your server has Python 3 installed:
```powershell
pip install psycopg2-binary
python relay_server.py
```

---

## Step 4: Verify the Relay

Run the testing script from PowerShell:
```powershell
powershell -ExecutionPolicy Bypass -File .\test_relay.ps1
```

You should see all green tests confirming status and user verification.
