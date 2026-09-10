# RanOnline — PostgreSQL Installation & Setup Guide

> **Target**: PostgreSQL 18 (64-bit) on Windows 10/11  
> **Time**: ~10 minutes

---

## Step 1: Download PostgreSQL

1. Go to: **https://www.postgresql.org/download/windows/**
2. Click **"Download the installer"** (by EDB)
3. Select **PostgreSQL 18** → **Windows x86-64**
4. Save the installer (e.g. `postgresql-18.x-windows-x64.exe`)

---

## Step 2: Run the Installer

1. **Right-click** the installer → **Run as Administrator**
2. Click **Next** on the welcome screen

### Installation Directory
- Keep default: `C:\Program Files\PostgreSQL\18`
- Click **Next**

### Select Components
Make sure **ALL** are checked:
- ✅ PostgreSQL Server
- ✅ pgAdmin 4
- ✅ Stack Builder
- ✅ Command Line Tools

Click **Next**

### Data Directory
- Keep default: `C:\Program Files\PostgreSQL\18\data`
- Click **Next**

### Set Password
- Enter password: **`12345`**
- Confirm password: **`12345`**
- Click **Next**

> ⚠️ **IMPORTANT**: This password MUST match the password in the server `.cfg` files.  
> The default configs use `12345`. If you change this, update ALL files in `_Bin\Tool\cfg\`.

### Port
- Keep default: **`5432`**
- Click **Next**

### Locale
- Keep default: **`[Default locale]`**
- Click **Next**

### Pre-Installation Summary
- Review and click **Next**
- Wait for installation to complete (~2-5 minutes)

### Finish
- **UNCHECK** "Launch Stack Builder at exit" (not needed)
- Click **Finish**

---

## Step 3: Verify PostgreSQL is Running

Open **Command Prompt** or **PowerShell** and run:

```cmd
"C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -c "SELECT version();"
```

Enter password `12345` when prompted. You should see output like:
```
PostgreSQL 18.x on x86_64-pc-windows ...
```

If it says "connection refused", start the service:
```cmd
net start postgresql-x64-18
```

---

## Step 4: Run the Database Setup

After PostgreSQL is installed and running:

1. Navigate to `_Bin\Tool\`
2. **Right-click** `Setup_PostgreSQL.bat` → **Run as Administrator**
3. The script will automatically:
   - Create 4 databases: `ranuser`, `rangame1`, `ranlog`, `ranshop`
   - Deploy all tables, views, and stored procedures
   - Create a default admin account (`admin` / `admin`)
4. Wait for it to finish — you should see `[SUCCESS]` at the end

---

## Step 5: Setup ODBC (Required for Servers)

1. **Right-click** `Setup_ODBC.bat` → **Run as Administrator**
2. This registers the 32-bit PostgreSQL ODBC driver and creates 4 DSN entries
3. Wait for all 4 connection tests to show `[SUCCESS]`

---

## Step 6: Start the Servers

1. **Double-click** `Start_Servers.bat`
2. The servers launch in order: Session → Login → Agent → Field
3. Wait for all 4 to start (~15 seconds total)

---

## Quick Reference — Full Setup Order

```
┌─────────────────────────────────────────────────────┐
│  1. Install PostgreSQL 18           (this guide)    │
│  2. Run Setup_PostgreSQL.bat        (create DBs)    │
│  3. Run Setup_ODBC.bat              (ODBC drivers)  │
│  4. Run Start_Servers.bat           (launch game)   │
│  5. Run GameClient.exe or Emulator  (connect)       │
└─────────────────────────────────────────────────────┘
```

---

## Troubleshooting

### "PostgreSQL not found"
- Make sure you installed the **64-bit** version
- Check if `C:\Program Files\PostgreSQL\18\bin\psql.exe` exists
- If installed elsewhere, add the `bin\` folder to your system PATH

### "Connection refused" or "Could not connect"
- Open **Services** (Win+R → `services.msc`)
- Find **postgresql-x64-18**
- Right-click → **Start**
- Set Startup Type to **Automatic** for future reboots

### "Password authentication failed"
- The password in the installer MUST match the `.cfg` files
- Default password in all configs: `12345`
- To reset: open pgAdmin4 → right-click PostgreSQL 18 → Properties → Change password

### "Database already exists"
- This is normal — the setup script skips existing databases
- To do a clean install, drop databases first in pgAdmin4 or psql

### Server won't start / ODBC error
- Make sure `Setup_ODBC.bat` completed with all `[SUCCESS]`
- Check that PostgreSQL service is running
- Check `_Bin\Tool\Logs\` for server log files

---

## Default Credentials Summary

| Item | Value |
|---|---|
| PostgreSQL User | `postgres` |
| PostgreSQL Password | `12345` |
| PostgreSQL Host | `127.0.0.1` |
| PostgreSQL Port | `5432` |
| Game Admin Login | `admin` |
| Game Admin Password | `admin` |
| Game Admin Type | `9` (GM Level) |

---

## Database Overview

| Database | Purpose | Key Tables |
|---|---|---|
| `ranuser` | Account management | `UserInfo`, `BlockAddress`, `UserTemp` |
| `rangame1` | Character & game world data | `ChaInfo`, `GuildInfo`, `PetInfo`, `VehicleInfo` |
| `ranlog` | Transaction & action logs | `LogItem`, `LogMoney`, `LogAction`, `LogItemExchange` |
| `ranshop` | Cash shop / item mall | `ShopItem`, `ShopItemMap`, `ShopPurchase` |
