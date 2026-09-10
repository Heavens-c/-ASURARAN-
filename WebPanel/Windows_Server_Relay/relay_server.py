"""
RanOnline Game Server Relay — Python Alternative Microservice
Provides the exact same secure HMAC-SHA256 authenticated API as relay_server.js.
Requires: pip install psycopg2-binary
"""

import json
import hmac
import hashlib
import time
import os
import socket
from http.server import HTTPServer, BaseHTTPRequestHandler
import psycopg2
from psycopg2 import pool

CONFIG_PATH = os.path.join(os.path.dirname(__file__), 'config.json')

with open(CONFIG_PATH, 'r') as f:
    config = json.load(f)

# Initialize PostgreSQL Connection Pools
pg_cfg = config['postgres']
pg_pools = {
    'user': psycopg2.pool.SimpleConnectionPool(1, 10, host=pg_cfg['host'], port=pg_cfg['port'], user=pg_cfg['user'], password=pg_cfg['password'], dbname=pg_cfg['databases']['user']),
    'game': psycopg2.pool.SimpleConnectionPool(1, 10, host=pg_cfg['host'], port=pg_cfg['port'], user=pg_cfg['user'], password=pg_cfg['password'], dbname=pg_cfg['databases']['game']),
    'log':  psycopg2.pool.SimpleConnectionPool(1, 10, host=pg_cfg['host'], port=pg_cfg['port'], user=pg_cfg['user'], password=pg_cfg['password'], dbname=pg_cfg['databases']['log'])
}

class RelayHandler(BaseHTTPRequestHandler):

    def send_json_response(self, status_code, data):
        payload = json.dumps(data).encode('utf-8')
        self.send_response(status_code)
        self.send_header('Content-Type', 'application/json')
        self.send_header('Content-Length', str(len(payload)))
        self.end_headers()
        self.wfile.write(payload)

    def do_POST(self):
        client_ip = self.client_address[0]

        # 1. IP Whitelist Check
        allowed_ips = config['server']['allowed_ips']
        if '*' not in allowed_ips and 'YOUR_NAMECHEAP_SERVER_IP' not in allowed_ips and client_ip not in allowed_ips:
            print(f"[BLOCKED] Unauthorized IP: {client_ip}")
            return self.send_json_response(403, {'success': False, 'error': 'Forbidden: IP not authorized'})

        content_len = int(self.headers.get('Content-Length', 0))
        raw_body = self.rfile.read(content_len)

        # 2. Security Headers & Signature Verification
        sig_header = self.headers.get('X-Signature')
        ts_header = self.headers.get('X-Timestamp')

        if not sig_header or not ts_header:
            return self.send_json_response(401, {'success': False, 'error': 'Missing security headers'})

        try:
            req_ts = int(ts_header)
        except ValueError:
            return self.send_json_response(401, {'success': False, 'error': 'Invalid timestamp format'})

        # Replay Tolerance Check
        if abs(int(time.time()) - req_ts) > config['server'].get('timestamp_tolerance_seconds', 300):
            return self.send_json_response(401, {'success': False, 'error': 'Request timestamp expired'})

        secret = config['server']['hmac_secret'].encode('utf-8')
        expected_sig = hmac.new(secret, raw_body, hashlib.sha256).hexdigest()

        if not hmac.compare_digest(sig_header, expected_sig):
            print(f"[SECURITY] Invalid signature from {client_ip}")
            return self.send_json_response(401, {'success': False, 'error': 'Invalid HMAC signature'})

        try:
            body = json.loads(raw_body.decode('utf-8'))
        except Exception:
            return self.send_json_response(400, {'success': False, 'error': 'Invalid JSON body'})

        # Route Endpoints
        endpoint = self.path.rstrip('/')

        if endpoint == '/api/insert_points':
            self.handle_insert_points(body.get('data', {}))
        elif endpoint == '/api/check_user':
            self.handle_check_user(body.get('data', {}))
        elif endpoint == '/api/register_user':
            self.handle_register_user(body.get('data', {}))
        elif endpoint == '/api/get_server_status':
            self.handle_server_status()
        elif endpoint == '/api/get_rankings':
            self.handle_get_rankings(body.get('data', {}))
        else:
            self.send_json_response(404, {'success': False, 'error': 'Endpoint not found'})

    def handle_insert_points(self, data):
        username = data.get('username')
        points = data.get('points')
        order_id = data.get('order_id', 'N/A')

        if not username or not isinstance(points, int) or points <= 0:
            return self.send_json_response(400, {'success': False, 'error': 'Invalid parameters'})

        conn = pg_pools['user'].getconn()
        try:
            with conn.cursor() as cur:
                cur.execute("SELECT UserNum, UserPoint FROM UserInfo WHERE UserID = %s FOR UPDATE;", (username,))
                row = cur.fetchone()
                if not row:
                    conn.rollback()
                    return self.send_json_response(404, {'success': False, 'error': 'User not found'})

                user_num, current_pts = row
                cur.execute("UPDATE UserInfo SET UserPoint = UserPoint + %s WHERE UserNum = %s RETURNING UserPoint;", (points, user_num))
                new_pts = cur.fetchone()[0]
                conn.commit()

            print(f"[TOPUP] User: {username} +{points} Points. New Total: {new_pts}")
            self.send_json_response(200, {
                'success': True,
                'usernum': user_num,
                'username': username,
                'credited_points': points,
                'new_total_points': new_pts
            })
        except Exception as e:
            conn.rollback()
            self.send_json_response(500, {'success': False, 'error': str(e)})
        finally:
            pg_pools['user'].putconn(conn)

    def handle_check_user(self, data):
        username = data.get('username')
        conn_u = pg_pools['user'].getconn()
        conn_g = pg_pools['game'].getconn()
        try:
            with conn_u.cursor() as cur:
                cur.execute("SELECT UserNum, UserID, UserPoint, UserAvailable, UserBlock FROM UserInfo WHERE UserID = %s;", (username,))
                urow = cur.fetchone()
                if not urow:
                    return self.send_json_response(200, {'success': True, 'exists': False})

            user_data = {'usernum': urow[0], 'userid': urow[1], 'userpoint': urow[2], 'useravailable': urow[3], 'userblock': urow[4]}

            characters = []
            with conn_g.cursor() as gcur:
                gcur.execute("SELECT ChaNum, ChaName, ChaClass, ChaSchool, ChaLevel FROM ChaInfo WHERE UserNum = %s AND ChaDeleted = 0;", (urow[0],))
                for crow in gcur.fetchall():
                    characters.append({'chanum': crow[0], 'chaname': crow[1], 'chaclass': crow[2], 'chaschool': crow[3], 'chalevel': crow[4]})

            self.send_json_response(200, {'success': True, 'exists': True, 'user': user_data, 'characters': characters})
        finally:
            pg_pools['user'].putconn(conn_u)
            pg_pools['game'].putconn(conn_g)

    def handle_register_user(self, data):
        username = data.get('username')
        password = data.get('password')
        email = data.get('email', '')
        pincode = data.get('pincode', '')

        if not username or not password:
            return self.send_json_response(400, {'success': False, 'error': 'Missing credentials'})

        md5_pass = hashlib.md5(password.encode('utf-8')).hexdigest()
        conn = pg_pools['user'].getconn()
        try:
            with conn.cursor() as cur:
                cur.execute("SELECT UserNum FROM UserInfo WHERE UserID = %s;", (username,))
                if cur.fetchone():
                    return self.send_json_response(400, {'success': False, 'error': 'Username already exists'})

                cur.execute("""
                    INSERT INTO UserInfo (UserID, UserPass, UserEmail, SubPinCode, UserPoint, UserAvailable, CreateDate)
                    VALUES (%s, %s, %s, %s, 0, 1, CURRENT_TIMESTAMP)
                    RETURNING UserNum;
                """, (username, md5_pass, email, pincode))
                usernum = cur.fetchone()[0]
                conn.commit()

            self.send_json_response(200, {'success': True, 'usernum': usernum})
        except Exception as e:
            conn.rollback()
            self.send_json_response(500, {'success': False, 'error': str(e)})
        finally:
            pg_pools['user'].putconn(conn)

    def handle_server_status(self):
        self.send_json_response(200, {'success': True, 'online': True, 'online_players': 0})

    def handle_get_rankings(self, data):
        limit = min(int(data.get('limit', 50)), 100)
        conn = pg_pools['game'].getconn()
        try:
            characters = []
            with conn.cursor() as cur:
                cur.execute("""
                    SELECT ChaName, ChaClass, ChaSchool, ChaLevel, ChaExp, ChaPKScore, ChaGuName
                    FROM ChaInfo WHERE ChaDeleted = 0
                    ORDER BY ChaLevel DESC, ChaExp DESC, ChaPKScore DESC LIMIT %s;
                """, (limit,))
                for row in cur.fetchall():
                    characters.append({
                        'chaname': row[0], 'chaclass': row[1], 'chaschool': row[2],
                        'chalevel': row[3], 'chaexp': row[4], 'chapkscore': row[5], 'chaguname': row[6]
                    })
            self.send_json_response(200, {'success': True, 'characters': characters, 'guilds': []})
        finally:
            pg_pools['game'].putconn(conn)

if __name__ == '__main__':
    port = config['server'].get('port', 8088)
    host = config['server'].get('host', '0.0.0.0')
    server = HTTPServer((host, port), RelayHandler)
    print(f"[PYTHON RELAY ACTIVE] Listening on {host}:{port}")
    server.serve_forever()
