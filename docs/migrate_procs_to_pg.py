# -*- coding: utf-8 -*-
"""
RanOnline Stored Procedures to PostgreSQL PL/pgSQL Migration Script
Installs all required stored procedures / functions for RanOnline into PostgreSQL 18.
"""

import os
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

# -----------------------------------------------------------------------------
# RanUser Stored Procedures
# -----------------------------------------------------------------------------
RANUSER_PROCS = """
-- user_verify
CREATE OR REPLACE FUNCTION user_verify(
    p_userId VARCHAR,
    p_userPass VARCHAR,
    p_userIp VARCHAR,
    p_SvrGrpNum INT,
    p_SvrNum INT,
    p_proPass VARCHAR,
    p_proNum VARCHAR,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
DECLARE
    v_nUserNum INT := 0;
    v_nState INT := 0;
    v_nBlock INT := 0;
    v_BlockDate TIMESTAMP;
BEGIN
    SELECT "UserNum", "UserLoginState", "UserBlock", "UserBlockDate"
    INTO v_nUserNum, v_nState, v_nBlock, v_BlockDate
    FROM "UserInfo"
    WHERE "UserID" = p_userId AND "UserPass" = p_userPass AND "UserAvailable" = 1;

    IF NOT FOUND OR v_nUserNum IS NULL OR v_nUserNum = 0 THEN
        nReturn := 0;
        RETURN;
    END IF;

    IF v_nBlock = 1 AND v_BlockDate > CURRENT_TIMESTAMP THEN
        nReturn := 2; -- Blocked
        RETURN;
    END IF;

    IF v_nState = 1 THEN
        nReturn := 5; -- Already logged in
        RETURN;
    END IF;

    UPDATE "UserInfo"
    SET "UserLoginState" = 1,
        "LastLoginDate" = CURRENT_TIMESTAMP,
        "UserIP" = p_userIp,
        "SGNum" = p_SvrGrpNum,
        "SvrNum" = p_SvrNum
    WHERE "UserNum" = v_nUserNum;

    nReturn := 1;
END;
$fn$ LANGUAGE plpgsql;

-- Publisher aliases forwarding to user_verify
CREATE OR REPLACE FUNCTION daum_user_verify(p_userId VARCHAR, p_userPass VARCHAR, p_userIp VARCHAR, p_SvrGrpNum INT, p_SvrNum INT, p_proPass VARCHAR, p_proNum VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := user_verify(p_userId, p_userPass, p_userIp, p_SvrGrpNum, p_SvrNum, p_proPass, p_proNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION excite_user_verify(p_userId VARCHAR, p_userPass VARCHAR, p_userIp VARCHAR, p_SvrGrpNum INT, p_SvrNum INT, p_proPass VARCHAR, p_proNum VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := user_verify(p_userId, p_userPass, p_userIp, p_SvrGrpNum, p_SvrNum, p_proPass, p_proNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION japan_user_verify(p_userId VARCHAR, p_userPass VARCHAR, p_userIp VARCHAR, p_SvrGrpNum INT, p_SvrNum INT, p_proPass VARCHAR, p_proNum VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := user_verify(p_userId, p_userPass, p_userIp, p_SvrGrpNum, p_SvrNum, p_proPass, p_proNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION thai_user_verify(p_userId VARCHAR, p_userPass VARCHAR, p_userIp VARCHAR, p_SvrGrpNum INT, p_SvrNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := user_verify(p_userId, p_userPass, p_userIp, p_SvrGrpNum, p_SvrNum, '', ''); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION gsp_user_verify(p_userId VARCHAR, p_userIp VARCHAR, p_SvrGrpNum INT, p_SvrNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := user_verify(p_userId, '', p_userIp, p_SvrGrpNum, p_SvrNum, '', ''); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION terra_user_verify(p_userId VARCHAR, p_dummy1 VARCHAR, p_dummy2 VARCHAR, p_dummy3 VARCHAR, p_userIp VARCHAR, p_SvrGrpNum INT, p_SvrNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := user_verify(p_userId, '', p_userIp, p_SvrGrpNum, p_SvrNum, '', ''); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION gs_user_verify(p_userId VARCHAR, p_userPass VARCHAR, p_userIp VARCHAR, p_SvrGrpNum INT, p_SvrNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := user_verify(p_userId, p_userPass, p_userIp, p_SvrGrpNum, p_SvrNum, '', ''); END; $fn$ LANGUAGE plpgsql;

-- user_register
CREATE OR REPLACE FUNCTION user_register(
    p_userId VARCHAR,
    p_userPass1 VARCHAR,
    p_userPass2 VARCHAR,
    p_userSA VARCHAR,
    p_userMail VARCHAR,
    p_SvrGrpNum INT,
    p_SvrNum INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
DECLARE
    v_nUserNum INT := 0;
BEGIN
    SELECT "UserNum" INTO v_nUserNum FROM "UserInfo" WHERE "UserID" = p_userId;
    IF v_nUserNum > 0 THEN
        nReturn := 1; -- user exists
        RETURN;
    END IF;

    INSERT INTO "UserInfo" ("UserName", "UserID", "UserPass", "UserPass2", "UserSA", "UserEmail", "SGNum", "SvrNum", "UserType2", "UserType", "UserAvailable", "CreateDate", "LastLoginDate")
    VALUES (p_userId, p_userId, p_userPass1, p_userPass2, p_userSA, p_userMail, p_SvrGrpNum, p_SvrNum, 0, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

    nReturn := 0; -- success
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- user_changepass
CREATE OR REPLACE FUNCTION user_changepass(
    p_userId VARCHAR,
    p_userPass VARCHAR,
    p_userPass2 VARCHAR,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
DECLARE
    v_nUserNum INT := 0;
BEGIN
    SELECT "UserNum" INTO v_nUserNum FROM "UserInfo" WHERE "UserID" = p_userId AND "UserPass2" = p_userPass2;
    IF NOT FOUND OR v_nUserNum IS NULL OR v_nUserNum = 0 THEN
        nReturn := 1; -- fail
        RETURN;
    END IF;

    UPDATE "UserInfo" SET "UserPass" = p_userPass WHERE "UserID" = p_userId;
    nReturn := 2; -- success
END;
$fn$ LANGUAGE plpgsql;

-- user_logout
CREATE OR REPLACE FUNCTION user_logout(
    p_userId VARCHAR,
    p_usernum INT,
    p_gametime INT,
    p_chanum INT,
    p_svrgrp INT,
    p_svrnum INT,
    p_extra INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    UPDATE "UserInfo"
    SET "UserLoginState" = 0,
        "LastLoginDate" = CURRENT_TIMESTAMP,
        "PlayTime" = "PlayTime" + p_gametime
    WHERE "UserNum" = p_usernum;

    INSERT INTO "LogLogin" ("UserNum", "UserID", "LogInOut", "LogDate")
    VALUES (p_usernum, p_userId, 0, CURRENT_TIMESTAMP);

    INSERT INTO "LogGameTime" ("UserNum", "UserID", "GameTime", "ChaNum", "SGNum", "SvrNum", "LogDate")
    VALUES (p_usernum, p_userId, p_gametime, p_chanum, p_svrgrp, p_svrnum, CURRENT_TIMESTAMP);

    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- Simple logout handlers
CREATE OR REPLACE FUNCTION "UserLogoutSimple"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UserLogoutSimple2"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- user_gettype
CREATE OR REPLACE FUNCTION user_gettype(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    SELECT COALESCE("UserType", 0) INTO nReturn FROM "UserInfo" WHERE "UserNum" = p_nUserNum;
    IF NOT FOUND OR nReturn IS NULL THEN
        nReturn := 0;
    END IF;
END;
$fn$ LANGUAGE plpgsql;

-- user_cha_remain
CREATE OR REPLACE FUNCTION user_cha_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    SELECT COALESCE("ChaRemain", 0) INTO nReturn FROM "UserInfo" WHERE "UserNum" = p_nUserNum;
    IF NOT FOUND OR nReturn IS NULL THEN
        nReturn := 0;
    END IF;
END;
$fn$ LANGUAGE plpgsql;

-- user_cha_test_remain
CREATE OR REPLACE FUNCTION user_cha_test_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    SELECT COALESCE("ChaTestRemain", 0) INTO nReturn FROM "UserInfo" WHERE "UserNum" = p_nUserNum;
    IF NOT FOUND OR nReturn IS NULL THEN
        nReturn := 0;
    END IF;
END;
$fn$ LANGUAGE plpgsql;

-- UpdateChaNumIncrease / Decrease
CREATE OR REPLACE FUNCTION "UpdateChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "ChaRemain" = "ChaRemain" + 1 WHERE "UserNum" = p_nUserNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "ChaRemain" = "ChaRemain" - 1 WHERE "UserNum" = p_nUserNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateTestChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "ChaTestRemain" = "ChaTestRemain" + 1 WHERE "UserNum" = p_nUserNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateTestChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "ChaTestRemain" = "ChaTestRemain" - 1 WHERE "UserNum" = p_nUserNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- Publisher aliases for character count
CREATE OR REPLACE FUNCTION "Daum_UpdateChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumIncrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Daum_UpdateChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumDecrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Terra_UpdateChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumIncrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Terra_UpdateChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumDecrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Gsp_UpdateChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumIncrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Gsp_UpdateChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumDecrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_UpdateChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumIncrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_UpdateChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumDecrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_UpdateChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumIncrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_UpdateChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN nReturn := "UpdateChaNumDecrease"(p_nUserNum); END; $fn$ LANGUAGE plpgsql;

-- sp_UpdateMoneyPoint / sp_DeductMoneyPoint
CREATE OR REPLACE FUNCTION "sp_UpdateMoneyPoint"(p_nUserNum INT, p_szPoint VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_val INT := COALESCE(p_szPoint::INT, 0);
BEGIN
    UPDATE "UserInfo" SET "UserPoint" = "UserPoint" + v_val WHERE "UserNum" = p_nUserNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_DeductMoneyPoint"(p_nUserNum INT, p_szPoint VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_val INT := COALESCE(p_szPoint::INT, 0);
BEGIN
    UPDATE "UserInfo" SET "UserPoint" = "UserPoint" - v_val WHERE "UserNum" = p_nUserNum AND "UserPoint" >= v_val;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- log_serverstate
CREATE OR REPLACE FUNCTION log_serverstate(p_svrgrp INT, p_svrnum INT, p_usercount INT, p_serverstate INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "LogServerState" ("SGNum", "SvrNum", "UserCount", "ServerState", "LogDate")
    VALUES (p_svrgrp, p_svrnum, p_usercount, p_serverstate, CURRENT_TIMESTAMP);
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;
"""

# -----------------------------------------------------------------------------
# RanGame1 Stored Procedures
# -----------------------------------------------------------------------------
RANGAME_PROCS = """
-- sp_delete_character
CREATE OR REPLACE FUNCTION sp_delete_character(p_ChaNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_nChaDeleted INT := 0;
    v_nGuNum INT := 0;
BEGIN
    SELECT "ChaDeleted" INTO v_nChaDeleted FROM "ChaInfo" WHERE "ChaNum" = p_ChaNum;
    IF v_nChaDeleted = 1 THEN
        nReturn := -1;
        RETURN;
    END IF;

    SELECT COALESCE("GuNum", 0) INTO v_nGuNum FROM "GuildInfo" WHERE "ChaNum" = p_ChaNum LIMIT 1;
    IF v_nGuNum <> 0 THEN
        nReturn := -2; -- Cannot delete character while being guild master
        RETURN;
    END IF;

    UPDATE "ChaInfo" SET "ChaDeleted" = 1, "ChaDeletedDate" = CURRENT_TIMESTAMP WHERE "ChaNum" = p_ChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- RenameCharacter
CREATE OR REPLACE FUNCTION "RenameCharacter"(p_nChaNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_exist INT := 0;
BEGIN
    SELECT "ChaNum" INTO v_exist FROM "ChaInfo" WHERE "ChaName" = p_szChaName;
    IF v_exist IS NOT NULL AND v_exist <> 0 THEN
        nReturn := -1; -- Name already in use
        RETURN;
    END IF;

    UPDATE "ChaInfo" SET "ChaName" = p_szChaName WHERE "ChaNum" = p_nChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- MakeUserInven
CREATE OR REPLACE FUNCTION "MakeUserInven"(p_nSGNum INT, p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "UserInven" ("SGNum", "UserNum", "UserMoney", "UserInven")
    VALUES (p_nSGNum, p_nUserNum, 0, ''::bytea);
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- sp_create_guild
CREATE OR REPLACE FUNCTION sp_create_guild(p_ChaNum INT, p_GuName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_nGuNum INT := 0;
BEGIN
    SELECT "GuNum" INTO v_nGuNum FROM "GuildInfo" WHERE "ChaNum" = p_ChaNum LIMIT 1;
    IF v_nGuNum IS NOT NULL AND v_nGuNum <> 0 THEN
        nReturn := -1;
        RETURN;
    END IF;

    INSERT INTO "GuildInfo" ("ChaNum", "GuName", "GuExpire")
    VALUES (p_ChaNum, p_GuName, CURRENT_TIMESTAMP)
    RETURNING "GuNum" INTO v_nGuNum;

    UPDATE "ChaInfo" SET "GuNum" = v_nGuNum WHERE "ChaNum" = p_ChaNum;
    nReturn := v_nGuNum;
EXCEPTION WHEN OTHERS THEN
    nReturn := -2;
END;
$fn$ LANGUAGE plpgsql;

-- sp_delete_guild
CREATE OR REPLACE FUNCTION sp_delete_guild(p_GuNum INT, p_ChaNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    DELETE FROM "GuildInfo" WHERE "GuNum" = p_GuNum AND "ChaNum" = p_ChaNum;
    IF FOUND THEN
        UPDATE "ChaInfo" SET "GuNum" = 0, "GuPosition" = 0 WHERE "GuNum" = p_GuNum;
        nReturn := 1;
    ELSE
        nReturn := 0;
    END IF;
END;
$fn$ LANGUAGE plpgsql;

-- sp_add_guild_member / sp_delete_guild_member
CREATE OR REPLACE FUNCTION sp_add_guild_member(p_GuNum INT, p_ChaNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "GuNum" = p_GuNum WHERE "ChaNum" = p_ChaNum;
    IF FOUND THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION sp_delete_guild_member(p_ChaNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "GuNum" = 0, "GuPosition" = 0, "ChaGuSecede" = CURRENT_TIMESTAMP WHERE "ChaNum" = p_ChaNum;
    IF FOUND THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- Vehicle procedures
CREATE OR REPLACE FUNCTION "sp_InsertVehicle"(p_szVehicleName VARCHAR, p_nVehicleChaNum INT, p_nVehicleType INT, p_nVehicleCardMID INT, p_nVehicleCardSID INT, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_id INT;
BEGIN
    INSERT INTO "VehicleInfo" ("VehicleName", "VehicleChaNum", "VehicleType", "VehicleCardMID", "VehicleCardSID", "VehiclePutOnItems", "VehicleColor", "VehicleDeletedDate")
    VALUES (p_szVehicleName, p_nVehicleChaNum, p_nVehicleType, p_nVehicleCardMID, p_nVehicleCardSID, ''::bytea, ''::bytea, CURRENT_TIMESTAMP)
    RETURNING "VehicleUniqueNum" INTO v_id;

    UPDATE "VehicleInfo" SET "VehicleNum" = v_id WHERE "VehicleUniqueNum" = v_id;
    nReturn := v_id;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdateVehicleBooster"(p_nVehicleNum INT, p_nVehicleChaNum INT, p_nVehicleBooster INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "VehicleInfo" SET "VehicleBooster" = p_nVehicleBooster WHERE "VehicleNum" = p_nVehicleNum AND "VehicleChaNum" = p_nVehicleChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdateVehicleBattery"(p_nVehicleNum INT, p_nVehicleChaNum INT, p_nVehicleBattery INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "VehicleInfo" SET "VehicleBattery" = p_nVehicleBattery WHERE "VehicleNum" = p_nVehicleNum AND "VehicleChaNum" = p_nVehicleChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_GetVehicleBattery"(p_nVehicleNum INT, p_nVehicleChaNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    SELECT COALESCE("VehicleBattery", 0) INTO nReturn FROM "VehicleInfo" WHERE "VehicleNum" = p_nVehicleNum AND "VehicleChaNum" = p_nVehicleChaNum AND "VehicleDeleted" = 0;
    IF NOT FOUND OR nReturn IS NULL THEN nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_DeleteVehicle"(p_nVehicleNum INT, p_nVehicleChaNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "VehicleInfo" SET "VehicleDeleted" = 1, "VehicleDeletedDate" = CURRENT_TIMESTAMP WHERE "VehicleNum" = p_nVehicleNum AND "VehicleChaNum" = p_nVehicleChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- Character update routines
CREATE OR REPLACE FUNCTION "UpdateChaOnline"(p_ChaNum INT, p_nOnline INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "ChaOnline" = p_nOnline WHERE "ChaNum" = p_ChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateAllCharacterOffline"(INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "ChaOnline" = 0;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaExp"(p_ChaNum INT, p_nExp NUMERIC(19,4), INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "ChaExp" = p_nExp WHERE "ChaNum" = p_ChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaFaceStyle"(p_ChaNum INT, p_nFaceStyle INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "ChaFace" = p_nFaceStyle WHERE "ChaNum" = p_ChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaHairStyle"(p_ChaNum INT, p_nHairStyle INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "ChaHair" = p_nHairStyle WHERE "ChaNum" = p_ChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaHairColor"(p_ChaNum INT, p_nHairColor INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "ChaHairColor" = p_nHairColor WHERE "ChaNum" = p_ChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaLastCallPos"(p_ChaNum INT, p_nMap INT, p_fX DOUBLE PRECISION, p_fY DOUBLE PRECISION, p_fZ DOUBLE PRECISION, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET "ChaSaveMap" = p_nMap, "ChaSavePosX" = p_fX, "ChaSavePosY" = p_fY, "ChaSavePosZ" = p_fZ WHERE "ChaNum" = p_ChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateUserMoney"(p_SGNum INT, p_UserNum INT, p_nMoney BIGINT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInven" SET "UserMoney" = p_nMoney WHERE "SGNum" = p_SGNum AND "UserNum" = p_UserNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;
"""

# -----------------------------------------------------------------------------
# RanLog Stored Procedures
# -----------------------------------------------------------------------------
RANLOG_PROCS = """
CREATE OR REPLACE FUNCTION "InsertLogHackProgram"(p_UserNum INT, p_ChaNum INT, p_szProgName VARCHAR, p_szIP VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "LogHackProgram" ("UserNum", "ChaNum", "ProgramName", "UserIP", "LogDate")
    VALUES (p_UserNum, p_ChaNum, p_szProgName, p_szIP, CURRENT_TIMESTAMP);
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION log_serverstate(p_svrgrp INT, p_svrnum INT, p_usercount INT, p_serverstate INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "LogServerState" ("SGNum", "SvrNum", "UserCount", "ServerState", "LogDate")
    VALUES (p_svrgrp, p_svrnum, p_usercount, p_serverstate, CURRENT_TIMESTAMP);
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_LogAction_Insert"(p_UserNum INT, p_ChaNum INT, p_nAction INT, p_szDetail VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "LogAction" ("UserNum", "ChaNum", "ActionType", "ActionDetail", "LogDate")
    VALUES (p_UserNum, p_ChaNum, p_nAction, p_szDetail, CURRENT_TIMESTAMP);
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;
"""

def main():
    print("Deploying Stored Procedures to PostgreSQL 18...")

    print("-> ranuser procedures...")
    if not run_psql("ranuser", RANUSER_PROCS):
        sys.exit(1)

    print("-> rangame1 procedures...")
    if not run_psql("rangame1", RANGAME_PROCS):
        sys.exit(1)

    print("-> ranlog procedures...")
    if not run_psql("ranlog", RANLOG_PROCS):
        sys.exit(1)

    print("\nAll core stored procedures successfully deployed!")

if __name__ == "__main__":
    main()
