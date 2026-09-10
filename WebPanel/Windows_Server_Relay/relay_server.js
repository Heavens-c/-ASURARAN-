/**
 * RanOnline Game Server Relay — Security Microservice
 * 
 * PURPOSE:
 * Secure, hardened bridge running on the Windows Game Server.
 * Exposes ONLY strict parameterized actions (e.g. insert_points, check_user, register_user, get_rankings).
 * Protects PostgreSQL from ever being exposed to the outside internet.
 */

const express = require('express');
const crypto = require('crypto');
const net = require('net');
const { Pool } = require('pg');
const helmet = require('helmet');
const fs = require('fs');
const path = require('path');

// Load Configuration
const configPath = path.join(__dirname, 'config.json');
if (!fs.existsSync(configPath)) {
    console.error('[FATAL] config.json not found! Please create it before starting.');
    process.exit(1);
}

const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

// Initialize PostgreSQL Connection Pools
const pgBase = {
    host: config.postgres.host,
    port: config.postgres.port,
    user: config.postgres.user,
    password: config.postgres.password,
    max: config.postgres.pool.max || 10,
    idleTimeoutMillis: config.postgres.pool.idleTimeoutMillis || 30000
};

const poolUser = new Pool({ ...pgBase, database: config.postgres.databases.user });
const poolGame = new Pool({ ...pgBase, database: config.postgres.databases.game });
const poolShop = new Pool({ ...pgBase, database: config.postgres.databases.shop });
const poolLog  = new Pool({ ...pgBase, database: config.postgres.databases.log });

// In-Memory Rankings Cache (60s TTL)
let rankingsCache = null;
let rankingsCacheTime = 0;

const app = express();
app.use(helmet());

// Preserve raw body for HMAC signature verification
app.use(express.json({
    verify: (req, res, buf) => {
        req.rawBody = buf;
    }
}));

// ── Middleware: IP Whitelist & HMAC-SHA256 Verification ────────
app.use((req, res, next) => {
    // 1. IP Whitelist Check
    const clientIp = req.headers['x-forwarded-for'] || req.socket.remoteAddress || '';
    const cleanIp = clientIp.replace('::ffff:', '');

    const isAllowedIp = config.server.allowed_ips.some(allowed => {
        return allowed === '*' || allowed === 'YOUR_NAMECHEAP_SERVER_IP' || allowed === cleanIp;
    });

    if (!isAllowedIp) {
        console.warn(`[BLOCKED] Unauthorized IP attempt: ${cleanIp}`);
        return res.status(403).json({ success: false, error: 'Forbidden: IP not authorized' });
    }

    // 2. HMAC-SHA256 Signature Verification
    const signature = req.headers['x-signature'];
    const timestamp = parseInt(req.headers['x-timestamp'], 10);

    if (!signature || !timestamp) {
        return res.status(401).json({ success: false, error: 'Missing security headers' });
    }

    // 3. Replay Attack Prevention (Tolerance window)
    const now = Math.floor(Date.now() / 1000);
    const tolerance = config.server.timestamp_tolerance_seconds || 300;
    if (Math.abs(now - timestamp) > tolerance) {
        return res.status(401).json({ success: false, error: 'Request timestamp expired' });
    }

    // 4. Verify cryptographic hash
    const expectedSig = crypto
        .createHmac('sha256', config.server.hmac_secret)
        .update(req.rawBody || '')
        .digest('hex');

    if (!crypto.timingSafeEqual(Buffer.from(signature, 'hex'), Buffer.from(expectedSig, 'hex'))) {
        console.warn(`[SECURITY] Invalid signature from ${cleanIp}`);
        return res.status(401).json({ success: false, error: 'Invalid HMAC signature' });
    }

    next();
});

// ── Action: Insert Game Points (Authoritative Transaction) ──────
app.post('/api/insert_points', async (req, res) => {
    const { username, points, order_id } = req.body.data || {};

    if (!username || typeof points !== 'number' || points <= 0) {
        return res.status(400).json({ success: false, error: 'Invalid username or points' });
    }

    const client = await poolUser.connect();
    try {
        await client.query('BEGIN');

        // Check if user exists
        const userRes = await client.query('SELECT UserNum, UserPoint FROM UserInfo WHERE UserID = $1 FOR UPDATE', [username]);
        if (userRes.rows.length === 0) {
            await client.query('ROLLBACK');
            return res.status(404).json({ success: false, error: 'User does not exist in game database' });
        }

        const userNum = userRes.rows[0].usernum;
        const currentPoints = userRes.rows[0].userpoint || 0;

        // Increment points
        const updateRes = await client.query(
            'UPDATE UserInfo SET UserPoint = UserPoint + $1 WHERE UserNum = $2 RETURNING UserPoint',
            [points, userNum]
        );
        const newPoints = updateRes.rows[0].userpoint;

        await client.query('COMMIT');

        // Record in ranlog.LogMoney asynchronously
        poolLog.query(
            'INSERT INTO LogMoney (UserNum, ChaNum, MoneyDiff, MoneyTotal, ActionType, Detail) VALUES ($1, 0, $2, $3, 10, $4)',
            [userNum, points, newPoints, `Web TopUp Ref: ${order_id || 'N/A'}`]
        ).catch(err => console.error('[LOG ERROR]', err.message));

        console.log(`[TOPUP SUCCESS] User: ${username} (UserNum: ${userNum}) +${points} Points. New Total: ${newPoints}`);

        res.json({
            success: true,
            usernum: userNum,
            username: username,
            credited_points: points,
            new_total_points: newPoints
        });

    } catch (err) {
        await client.query('ROLLBACK');
        console.error('[INSERT POINTS ERROR]', err);
        res.status(500).json({ success: false, error: 'Database transaction failed: ' + err.message });
    } finally {
        client.release();
    }
});

// ── Action: Check User Profile & Characters ────────────────────
app.post('/api/check_user', async (req, res) => {
    const { username } = req.body.data || {};

    if (!username) {
        return res.status(400).json({ success: false, error: 'Missing username' });
    }

    try {
        const userRes = await poolUser.query(
            'SELECT UserNum, UserID, UserPoint, UserType, UserAvailable, UserBlock, CreateDate, LastLoginDate FROM UserInfo WHERE UserID = $1',
            [username]
        );

        if (userRes.rows.length === 0) {
            return res.json({ success: true, exists: false });
        }

        const userRow = userRes.rows[0];

        // Fetch user's characters from rangame
        const chaRes = await poolGame.query(
            'SELECT ChaNum, ChaName, ChaClass, ChaSchool, ChaLevel, ChaPKScore, ChaMoney FROM ChaInfo WHERE UserNum = $1 AND ChaDeleted = 0',
            [userRow.usernum]
        );

        res.json({
            success: true,
            exists: true,
            user: {
                usernum: userRow.usernum,
                userid: userRow.userid,
                userpoint: userRow.userpoint,
                useravailable: userRow.useravailable,
                userblock: userRow.userblock
            },
            characters: chaRes.rows
        });

    } catch (err) {
        console.error('[CHECK USER ERROR]', err);
        res.status(500).json({ success: false, error: 'Database query failed' });
    }
});

// ── Action: Register Game Account ──────────────────────────────
app.post('/api/register_user', async (req, res) => {
    const { username, password, email, pincode } = req.body.data || {};

    if (!username || !password) {
        return res.status(400).json({ success: false, error: 'Missing username or password' });
    }

    try {
        // Check duplicate
        const dupCheck = await poolUser.query('SELECT UserNum FROM UserInfo WHERE UserID = $1', [username]);
        if (dupCheck.rows.length > 0) {
            return res.status(400).json({ success: false, error: 'Username already taken' });
        }

        // RanOnline server expects MD5 password hash in UserPass column
        const md5Pass = crypto.createHash('md5').update(password).digest('hex');

        const insertRes = await poolUser.query(`
            INSERT INTO UserInfo (UserID, UserPass, UserEmail, SubPinCode, UserPoint, UserAvailable, CreateDate)
            VALUES ($1, $2, $3, $4, 0, 1, CURRENT_TIMESTAMP)
            RETURNING UserNum
        `, [username, md5Pass, email || '', pincode || '']);

        console.log(`[NEW GAME ACCOUNT] Created UserID: ${username}, UserNum: ${insertRes.rows[0].usernum}`);

        res.json({
            success: true,
            usernum: insertRes.rows[0].usernum,
            message: 'Game account provisioned successfully'
        });

    } catch (err) {
        console.error('[REGISTER ERROR]', err);
        res.status(500).json({ success: false, error: 'Failed to provision game account: ' + err.message });
    }
});

// ── Action: Fetch Rankings (Cached) ────────────────────────────
app.post('/api/get_rankings', async (req, res) => {
    const limit = Math.min(parseInt(req.body.data?.limit || 50, 10), 100);
    const now = Math.floor(Date.now() / 1000);

    // Return cached if within 60s
    if (rankingsCache && (now - rankingsCacheTime < 60)) {
        return res.json({ success: true, ...rankingsCache, cached: true });
    }

    try {
        const charRes = await poolGame.query(`
            SELECT ChaName, ChaClass, ChaSchool, ChaLevel, ChaExp, ChaPKScore, ChaGuName
            FROM ChaInfo
            WHERE ChaDeleted = 0
            ORDER BY ChaLevel DESC, ChaExp DESC, ChaPKScore DESC
            LIMIT $1
        `, [limit]);

        const guildRes = await poolGame.query(`
            SELECT GuName, GuMaster, GuRank, GuBattleWin, GuBattleLose
            FROM GuildInfo
            ORDER BY GuRank DESC, GuBattleWin DESC
            LIMIT 20
        `);

        rankingsCache = {
            characters: charRes.rows,
            guilds: guildRes.rows
        };
        rankingsCacheTime = now;

        res.json({ success: true, ...rankingsCache, cached: false });

    } catch (err) {
        console.error('[RANKINGS ERROR]', err);
        res.status(500).json({ success: false, error: 'Could not fetch rankings' });
    }
});

// ── Action: Check Server Status ────────────────────────────────
app.post('/api/get_server_status', async (req, res) => {
    // Helper to probe local port
    const checkPort = (port, host = '127.0.0.1') => {
        return new Promise(resolve => {
            const socket = new net.Socket();
            socket.setTimeout(800);
            socket.on('connect', () => {
                socket.destroy();
                resolve(true);
            });
            socket.on('timeout', () => {
                socket.destroy();
                resolve(false);
            });
            socket.on('error', () => {
                socket.destroy();
                resolve(false);
            });
            socket.connect(port, host);
        });
    };

    try {
        const loginOnline = await checkPort(config.game_daemons.login_server.port);
        const fieldOnline = await checkPort(config.game_daemons.field_server.port);

        let onlinePlayers = 0;
        try {
            const countRes = await poolGame.query('SELECT COUNT(*) FROM ChaInfo WHERE ChaOnline = 1 AND ChaDeleted = 0');
            onlinePlayers = parseInt(countRes.rows[0].count, 10);
        } catch (e) {}

        res.json({
            success: true,
            online: loginOnline || fieldOnline,
            services: {
                login_server: loginOnline,
                field_server: fieldOnline
            },
            online_players: onlinePlayers
        });

    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// ── Start HTTP Server ──────────────────────────────────────────
const PORT = config.server.port || 8088;
const HOST = config.server.host || '0.0.0.0';

app.listen(PORT, HOST, () => {
    console.log('====================================================');
    console.log(`[RELAY ACTIVE] RanOnline Windows Relay listening on ${HOST}:${PORT}`);
    console.log(`[SECURITY] Whitelisted IPs: ${JSON.stringify(config.server.allowed_ips)}`);
    console.log('====================================================');
});
