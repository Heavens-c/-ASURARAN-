# RanOnline Web Panel & Game Server Relay Suite

This directory contains the complete web panel and secure Windows relay system for your RanOnline server.

## Directory Structure

```text
WebPanel/
├── SharedHost_Namecheap/     <-- Production files to upload to Namecheap cPanel (public_html)
│   ├── api/
│   ├── assets/
│   │   ├── css/style.css     (Modern dark cyber/academy gaming design system)
│   │   └── js/main.js        (Interactive client scripts & validation)
│   ├── includes/
│   │   ├── auth.php          (Authentication, login, registration, session guards)
│   │   ├── config.php        (MySQL, Relay URL, HMAC key, rate limits)
│   │   ├── constants.php     (RanOnline 8 classes, schools, GM levels)
│   │   ├── db.php            (PDO prepared statements wrapper)
│   │   ├── footer.php        (Shared footer & scripts)
│   │   ├── game_relay.php    (HMAC-SHA256 authenticated relay client)
│   │   ├── header.php        (Responsive navigation & flash messages)
│   │   └── security.php      (CSRF tokens, rate limiter, XSS cleaner, headers)
│   ├── .htaccess             (Apache security hardening, block direct include access)
│   ├── database_setup.sql    (MySQL schema for Namecheap cPanel)
│   ├── dashboard.php         (Player control panel, character viewer, password change)
│   ├── download.php          (Client download mirrors & system specs)
│   ├── forgot_password.php   (Password reset flow with tokens)
│   ├── index.php             (Landing page, live server status, rates, news)
│   ├── login.php             (Secure login with brute-force rate limiting)
│   ├── logout.php            (Session destruction)
│   ├── rankings.php          (Live Top 50 Characters & Guilds)
│   ├── register.php          (Player registration with game sync)
│   ├── shop.php              (In-game Item Mall preview catalog)
│   ├── topup.php             (Point recharge portal: PIN voucher & payment checkout)
│   ├── webhook.php           (Automated IPN payment callback)
│   └── README_NAMECHEAP.md   (Step-by-step cPanel setup guide)
│
└── Windows_Server_Relay/     <-- Runs on Windows Game Server next to PostgreSQL
    ├── config.json           (PostgreSQL config, port, HMAC secret, allowed IPs)
    ├── package.json          (Node.js dependencies)
    ├── relay_server.js       (Production hardened Node.js microservice)
    ├── relay_server.py       (Python 3 alternative microservice)
    ├── start_relay.bat       (1-click launcher)
    ├── test_relay.ps1        (PowerShell automated HMAC test script)
    └── README_WINDOWS.md     (Firewall & Windows Server DevOps guide)
```

## Security Guarantees
1. **PostgreSQL Is Never Exposed**: PostgreSQL remains strictly bound to `127.0.0.1:5432` on the Windows Game Server.
2. **HMAC-SHA256 Signatures**: All communication between Namecheap and Windows uses cryptographically signed requests with anti-replay timestamps.
3. **Whitelisted IPs**: The Windows relay microservice strictly rejects any connection not coming from your Namecheap shared host IP.
4. **Authoritative Point Insertion**: Points are credited directly via atomic parameterized SQL statements without any risk of SQL injection or script kiddie tampering.
