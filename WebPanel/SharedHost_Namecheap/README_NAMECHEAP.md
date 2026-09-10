# Namecheap Shared Hosting — Deployment Guide

This directory contains the production files for your **RanOnline Web Panel**, specifically architected to run on standard **Namecheap Shared Hosting (cPanel)**.

---

## Architecture Overview

```text
[Internet / Players]
         │
         ▼
[Namecheap Shared Host (cPanel)] ──(HMAC-SHA256 Signed JSON)──> [Windows Game Server:8088]
  - PHP 8.1 / 8.2                                                - Relay Microservice
  - MySQL Database (Web Accounts, Orders, Logs)                  - PostgreSQL (ranuser, rangame, ranshop)
  - .htaccess Protected                                          - RanOnline Server Daemons
```

---

## Step 1: Upload Files to Namecheap cPanel

1. Log in to your **Namecheap cPanel**.
2. Open **File Manager** and navigate to `public_html/` (or your subdomain folder e.g. `panel.yourdomain.com`).
3. Upload all files and folders inside `WebPanel/SharedHost_Namecheap/` into `public_html/`.
4. Ensure `.htaccess` is uploaded and visible (enable "Show Hidden Files (dotfiles)" in File Manager settings).

---

## Step 2: Create MySQL Database in cPanel

1. In cPanel, click **MySQL® Databases**.
2. Create a new database: e.g. `yourcpanel_ranweb`.
3. Create a new user: e.g. `yourcpanel_webuser` with a strong password.
4. Add user to the database with **ALL PRIVILEGES**.
5. In cPanel, open **phpMyAdmin**.
6. Select your newly created database and click the **Import** tab.
7. Choose the file `database_setup.sql` and click **Go**.

---

## Step 3: Configure `includes/config.php`

Edit `includes/config.php` via cPanel File Manager Code Editor:

```php
// Database Credentials
define('DB_HOST',          'localhost');
define('DB_NAME',          'yourcpanel_ranweb');
define('DB_USER',          'yourcpanel_webuser');
define('DB_PASS',          'YourStrongPasswordHere');

// Windows Game Server Relay Settings
// Replace with the public IP of your Windows Game Server:
define('GAME_RELAY_URL',    'http://YOUR_WINDOWS_SERVER_PUBLIC_IP:8088/api');

// Must match the secret key in WebPanel/Windows_Server_Relay/config.json:
define('GAME_RELAY_SECRET', 'CHANGE_THIS_HMAC_SECRET_KEY_MINIMUM_64_CHARACTERS_1234567890abcdef');
```

---

## Step 4: Enable Free SSL on Namecheap

1. In cPanel, go to **SSL/TLS Status**.
2. Select your domain and click **Run AutoSSL**.
3. AutoSSL will install a free Let's Encrypt certificate within minutes.

---

## Security Verification

- Try navigating directly to `https://yourdomain.com/includes/config.php`.
  - Result: **HTTP 403 Forbidden** (Blocked by `.htaccess`).
- Try viewing `.env` or `.sql` files.
  - Result: **HTTP 403 Forbidden**.
- Database connection to the Windows Game Server:
  - **Zero PostgreSQL ports are opened to the public.**
  - All communication is routed through the authenticated Windows Relay microservice.

---

## Emergency Kill Switch / Server Maintenance Mode

1. Log in with an account having **UserType >= 19** (e.g. Master GM).
2. Click the red **Admin** button in the top navigation or navigate to `/admin/`.
3. In the **Emergency Kill Switch** card:
   - Click **ENGAGE KILL SWITCH (Maintenance)** to immediately redirect all public traffic to the custom `maintenance.php` splash screen.
   - Edit the **Maintenance Title**, **Message**, **Estimated Completion Time**, and **Discord Link** directly from the admin panel — the changes appear instantly without touching code.
   - Administrators with active sessions bypass the splash screen automatically so you can continue testing and managing the site while public players see the maintenance notice.
   - Click **DEACTIVATE KILL SWITCH (Go Live)** when maintenance is complete to restore the live front page.
