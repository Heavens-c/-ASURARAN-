# -*- coding: utf-8 -*-
"""
RanOnline MSSQL to PostgreSQL Schema & Stored Procedure Migration Engine
Converts tables and stored procedures from SQL/*.sql into PostgreSQL 18.
"""

import os
import re
import subprocess
import sys

PG_BIN = r"C:\Program Files\PostgreSQL\18\bin\psql.exe"
PG_USER = "postgres"
PG_HOST = "127.0.0.1"
PG_PORT = "5432"
PG_PASS = "12345"

def run_psql(db_name, sql_content):
    env = os.environ.copy()
    env["PGPASSWORD"] = PG_PASS
    r = subprocess.run(
        [PG_BIN, "-U", PG_USER, "-h", PG_HOST, "-p", PG_PORT, "-d", db_name, "-w"],
        input=sql_content,
        env=env,
        capture_output=True,
        text=True,
        encoding="utf-8"
    )
    if r.returncode != 0:
        print(f"[{db_name}] PSQL Error (code {r.returncode}):\n{r.stderr}\n{r.stdout}")
        return False
    return True

def convert_type(t):
    t_clean = t.strip()
    if re.search(r'\[int\]\s+IDENTITY', t_clean, re.I):
        return "SERIAL"
    if re.search(r'\[bigint\]\s+IDENTITY', t_clean, re.I):
        return "BIGSERIAL"
    t_clean = re.sub(r'\[int\]', 'INT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[smallint\]', 'SMALLINT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[tinyint\]', 'SMALLINT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[bigint\]', 'BIGINT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[money\]', 'NUMERIC(19,4)', t_clean, flags=re.I)
    t_clean = re.sub(r'\[float\]', 'DOUBLE PRECISION', t_clean, flags=re.I)
    t_clean = re.sub(r'\[real\]', 'REAL', t_clean, flags=re.I)
    t_clean = re.sub(r'\[image\]', 'BYTEA', t_clean, flags=re.I)
    t_clean = re.sub(r'\[binary\]\(\d+\)', 'BYTEA', t_clean, flags=re.I)
    t_clean = re.sub(r'\[varbinary\]\(\w+\)', 'BYTEA', t_clean, flags=re.I)
    t_clean = re.sub(r'\[datetime\]', 'TIMESTAMP WITHOUT TIME ZONE', t_clean, flags=re.I)
    t_clean = re.sub(r'\[smalldatetime\]', 'TIMESTAMP WITHOUT TIME ZONE', t_clean, flags=re.I)
    t_clean = re.sub(r'\[varchar\]\(MAX\)', 'TEXT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[nvarchar\]\(MAX\)', 'TEXT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[text\]', 'TEXT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[ntext\]', 'TEXT', t_clean, flags=re.I)
    t_clean = re.sub(r'\[varchar\]\((\d+)\)', r'VARCHAR(\1)', t_clean, flags=re.I)
    t_clean = re.sub(r'\[nvarchar\]\((\d+)\)', r'VARCHAR(\1)', t_clean, flags=re.I)
    t_clean = re.sub(r'\[char\]\((\d+)\)', r'VARCHAR(\1)', t_clean, flags=re.I)
    t_clean = re.sub(r'\[nchar\]\((\d+)\)', r'VARCHAR(\1)', t_clean, flags=re.I)
    return t_clean

def parse_tables(sql_file_path):
    with open(sql_file_path, "r", encoding="utf-16", errors="ignore") as f:
        content = f.read()

    # Collect defaults from ALTER TABLE
    defaults_map = {}
    default_matches = re.findall(
        r'ALTER\s+TABLE\s+\[dbo\]\.\[(\w+)\]\s+ADD\s+CONSTRAINT.*?DEFAULT\s+\((.*?)\)\s+FOR\s+\[(\w+)\]',
        content,
        re.IGNORECASE | re.DOTALL
    )
    for tbl, dval, col in default_matches:
        dval_clean = dval.strip().strip("()")
        if dval_clean.lower().startswith("getdate"):
            pg_def = "CURRENT_TIMESTAMP"
        elif "datepart(year" in dval_clean.lower():
            pg_def = "EXTRACT(YEAR FROM CURRENT_TIMESTAMP)::INT"
        elif "datepart(month" in dval_clean.lower():
            pg_def = "EXTRACT(MONTH FROM CURRENT_TIMESTAMP)::INT"
        elif "datepart(day" in dval_clean.lower():
            pg_def = "EXTRACT(DAY FROM CURRENT_TIMESTAMP)::INT"
        elif "datepart(hour" in dval_clean.lower():
            pg_def = "EXTRACT(HOUR FROM CURRENT_TIMESTAMP)::INT"
        elif dval_clean.startswith("'"):
            pg_def = dval_clean
        elif dval_clean.isdigit() or (dval_clean.startswith("-") and dval_clean[1:].isdigit()):
            pg_def = dval_clean
        else:
            pg_def = dval_clean
        defaults_map[(tbl.lower(), col.lower())] = pg_def

    # Extract CREATE TABLE statements
    table_pattern = re.compile(
        r'CREATE\s+TABLE\s+\[dbo\]\.\[(\w+)\]\s*\((.*?)\)\s+ON\s+\[PRIMARY\]',
        re.IGNORECASE | re.DOTALL
    )

    pg_tables = []
    for match in table_pattern.finditer(content):
        tbl_name = match.group(1)
        body = match.group(2)

        lines = body.splitlines()
        col_defs = []
        pk_cols = []

        in_pk = False
        for raw_line in lines:
            line = raw_line.strip()
            if not line:
                continue

            # Check for PRIMARY KEY CONSTRAINT
            if "PRIMARY KEY" in line.upper():
                in_pk = True
                continue
            if in_pk:
                col_match = re.search(r'\[(\w+)\]\s+ASC', line)
                if col_match:
                    pk_cols.append(col_match.group(1))
                if ")" in line:
                    in_pk = False
                continue

            # Skip constraint/index remnants
            if re.match(r'^\[\w+\]\s+(ASC|DESC)\b', line, re.I):
                continue
            if re.match(r'^(CONSTRAINT|WITH\s*\()\b', line, re.I):
                continue

            # Column definition
            col_match = re.match(r'\[(\w+)\]\s+(.*)', line)
            if col_match:
                col_name = col_match.group(1)
                rest = col_match.group(2).rstrip(",")

                is_identity = bool(re.search(r'IDENTITY', rest, re.I))
                is_not_null = bool(re.search(r'NOT\s+NULL', rest, re.I))

                pg_type = convert_type(rest)
                # Strip nullability from pg_type if embedded
                pg_type = re.sub(r'\b(NOT\s+NULL|NULL)\b', '', pg_type, flags=re.I).strip()

                def_val = defaults_map.get((tbl_name.lower(), col_name.lower()))

                parts = [f'"{col_name}"']
                if is_identity:
                    parts.append("SERIAL")
                else:
                    parts.append(pg_type)

                if def_val is not None and not is_identity:
                    parts.append(f"DEFAULT {def_val}")

                if is_not_null and not is_identity:
                    parts.append("NOT NULL")

                col_defs.append("    " + " ".join(parts))

        if pk_cols:
            pk_str = ", ".join(f'"{c}"' for c in pk_cols)
            col_defs.append(f"    PRIMARY KEY ({pk_str})")

        pg_ddl = f'CREATE TABLE IF NOT EXISTS "{tbl_name}" (\n' + ",\n".join(col_defs) + "\n);\n"
        pg_tables.append(pg_ddl)

    return pg_tables

def main():
    print("Beginning RanOnline MSSQL -> PostgreSQL Conversion...")

    # 1. RanUser
    print("\n--- Processing RanUser ---")
    user_tables = parse_tables("SQL/RanUser.sql")
    user_ddl = "\n".join(user_tables)
    print(f"Generated {len(user_tables)} tables for ranuser.")
    if not run_psql("ranuser", user_ddl):
        sys.exit(1)
    print("ranuser tables created successfully.")

    # 2. RanGame1
    print("\n--- Processing RanGame1 ---")
    game_tables = parse_tables("SQL/RanGame1.sql")
    game_ddl = "\n".join(game_tables)
    print(f"Generated {len(game_tables)} tables for rangame1.")
    if not run_psql("rangame1", game_ddl):
        sys.exit(1)
    print("rangame1 tables created successfully.")

    # 3. RanLog
    print("\n--- Processing RanLog ---")
    log_tables = parse_tables("SQL/RanLog.sql")
    log_ddl = "\n".join(log_tables)
    print(f"Generated {len(log_tables)} tables for ranlog.")
    if not run_psql("ranlog", log_ddl):
        sys.exit(1)
    print("ranlog tables created successfully.")

    # 4. RanShop
    print("\n--- Processing RanShop ---")
    shop_tables = parse_tables("SQL/RanShop.sql")
    shop_ddl = "\n".join(shop_tables)
    print(f"Generated {len(shop_tables)} tables for ranshop.")
    if not run_psql("ranshop", shop_ddl):
        sys.exit(1)
    print("ranshop tables created successfully.")

    print("\nAll database tables successfully migrated!")

if __name__ == "__main__":
    main()
