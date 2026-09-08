-- ============================================================================
-- RanOnline PostgreSQL Database Schema Migration Script
-- Databases: ranuser, rangame, ranlog, ranshop
-- Target: PostgreSQL 12+ (tested with PostgreSQL 18)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. DATABASE: ranuser
-- ----------------------------------------------------------------------------
\connect postgres;

CREATE DATABASE ranuser WITH OWNER = postgres ENCODING = 'UTF8';
CREATE DATABASE rangame WITH OWNER = postgres ENCODING = 'UTF8';
CREATE DATABASE ranlog  WITH OWNER = postgres ENCODING = 'UTF8';
CREATE DATABASE ranshop WITH OWNER = postgres ENCODING = 'UTF8';

-- ----------------------------------------------------------------------------
-- SCHEMA FOR: ranuser
-- ----------------------------------------------------------------------------
\connect ranuser;

CREATE TABLE IF NOT EXISTS UserInfo (
    UserNum SERIAL PRIMARY KEY,
    UserID VARCHAR(33) NOT NULL UNIQUE,
    UserPass VARCHAR(65) NOT NULL,
    UserPass2 VARCHAR(65) DEFAULT '',
    UserName VARCHAR(33) DEFAULT '',
    UserType INT DEFAULT 1,
    UserLoginState INT DEFAULT 0,
    UserAvailable INT DEFAULT 1,
    UserBlock INT DEFAULT 0,
    UserBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ChatBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserPoint INT DEFAULT 0,
    UserEmail VARCHAR(65) DEFAULT '',
    CreateDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    LastLoginDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    LastLoginIP VARCHAR(33) DEFAULT '',
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0,
    UserUID VARCHAR(65) DEFAULT '',
    ChaNum1 INT DEFAULT 0,
    ChaNum2 INT DEFAULT 0,
    ChaNum3 INT DEFAULT 0,
    ChaNum4 INT DEFAULT 0,
    ChaRemain INT DEFAULT 4,
    CardRemaining INT DEFAULT 0,
    SubPassword VARCHAR(65) DEFAULT '',
    SubPinCode VARCHAR(65) DEFAULT ''
);

CREATE TABLE IF NOT EXISTS DaumUserInfo (
    UserNum INT PRIMARY KEY,
    ChatBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserBlock INT DEFAULT 0,
    UserBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserLoginState INT DEFAULT 0,
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS TerraUserInfo (
    UserNum INT PRIMARY KEY,
    ChatBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserBlock INT DEFAULT 0,
    UserBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserLoginState INT DEFAULT 0,
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS GspUserInfo (
    UserNum INT PRIMARY KEY,
    ChatBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserBlock INT DEFAULT 0,
    UserBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserLoginState INT DEFAULT 0,
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS GSUserInfo (
    UserNum INT PRIMARY KEY,
    ChatBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserBlock INT DEFAULT 0,
    UserBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserLoginState INT DEFAULT 0,
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS JapanUserInfo (
    UserNum INT PRIMARY KEY,
    ChatBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserBlock INT DEFAULT 0,
    UserBlockDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserLoginState INT DEFAULT 0,
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS UserTemp (
    UserID VARCHAR(33) PRIMARY KEY,
    UserIP VARCHAR(33),
    LoginDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- user_verify procedure/function compatible with ODBC {call user_verify(..., ?)}
CREATE OR REPLACE FUNCTION user_verify(
    p_userid VARCHAR,
    p_userpass VARCHAR,
    p_userip VARCHAR,
    p_svrgrp INT,
    p_svrnum INT,
    p_randpass VARCHAR,
    p_randnum VARCHAR,
    OUT p_result INT
)
LANGUAGE plpgsql
AS $$
DECLARE
    v_usernum INT;
    v_userpass VARCHAR(65);
    v_userblock INT;
    v_blockdate TIMESTAMP;
BEGIN
    p_result := 0; -- Default: Success

    SELECT UserNum, UserPass, UserBlock, UserBlockDate 
    INTO v_usernum, v_userpass, v_userblock, v_blockdate
    FROM UserInfo 
    WHERE UserID = p_userid;

    IF NOT FOUND THEN
        p_result := 2; -- DB_NOT_FOUND
        RETURN;
    END IF;

    IF v_userblock = 1 AND v_blockdate > CURRENT_TIMESTAMP THEN
        p_result := 3; -- DB_BLOCKED
        RETURN;
    END IF;

    IF v_userpass <> p_userpass THEN
        p_result := 1; -- DB_WRONG_PASS
        RETURN;
    END IF;

    UPDATE UserInfo 
    SET UserLoginState = 1,
        LastLoginDate = CURRENT_TIMESTAMP,
        LastLoginIP = p_userip,
        SGNum = p_svrgrp,
        SvrNum = p_svrnum
    WHERE UserNum = v_usernum;

    p_result := 0; -- DB_OK
END;
$$;

-- ----------------------------------------------------------------------------
-- SCHEMA FOR: rangame
-- ----------------------------------------------------------------------------
\connect rangame;

CREATE TABLE IF NOT EXISTS ChaInfo (
    ChaNum SERIAL PRIMARY KEY,
    UserNum INT NOT NULL,
    SGNum INT DEFAULT 0,
    ChaName VARCHAR(33) NOT NULL UNIQUE,
    ChaTribe INT DEFAULT 0,
    ChaClass INT DEFAULT 0,
    ChaSchool INT DEFAULT 0,
    ChaHair INT DEFAULT 0,
    ChaFace INT DEFAULT 0,
    ChaLiving INT DEFAULT 0,
    ChaBright INT DEFAULT 0,
    ChaLevel INT DEFAULT 1,
    ChaMoney BIGINT DEFAULT 0,
    ChaDex INT DEFAULT 10,
    ChaIntel INT DEFAULT 10,
    ChaStrong INT DEFAULT 10,
    ChaPower INT DEFAULT 10,
    ChaSpirit INT DEFAULT 10,
    ChaStrength INT DEFAULT 10,
    ChaStRemain INT DEFAULT 0,
    ChaAttackP INT DEFAULT 0,
    ChaDefenseP INT DEFAULT 0,
    ChaFightA INT DEFAULT 0,
    ChaShootA INT DEFAULT 0,
    ChaExp BIGINT DEFAULT 0,
    ChaSkillPoint INT DEFAULT 0,
    ChaHP INT DEFAULT 100,
    ChaMP INT DEFAULT 100,
    ChaSP INT DEFAULT 100,
    ChaPK INT DEFAULT 0,
    ChaStartMap INT DEFAULT 0,
    ChaStartGate INT DEFAULT 0,
    ChaPosX REAL DEFAULT 0.0,
    ChaPosY REAL DEFAULT 0.0,
    ChaPosZ REAL DEFAULT 0.0,
    ChaSaveMap INT DEFAULT 0,
    ChaSavePosX REAL DEFAULT 0.0,
    ChaSavePosY REAL DEFAULT 0.0,
    ChaSavePosZ REAL DEFAULT 0.0,
    ChaReturnMap INT DEFAULT 0,
    ChaReturnPosX REAL DEFAULT 0.0,
    ChaReturnPosY REAL DEFAULT 0.0,
    ChaReturnPosZ REAL DEFAULT 0.0,
    ChaGuName VARCHAR(33) DEFAULT '',
    GuNum INT DEFAULT 0,
    ChaGuSecede TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ChaHairColor INT DEFAULT 0,
    ChaSex INT DEFAULT 0,
    ChaReExp BIGINT DEFAULT 0,
    ChaSpMID INT DEFAULT 0,
    ChaSpSID INT DEFAULT 0,
    ChaScaleRange REAL DEFAULT 1.0,
    ChaCP INT DEFAULT 0,
    ChaContributionPoint BIGINT DEFAULT 0,
    ChaActivityPoint INT DEFAULT 0,
    ChaBadge VARCHAR(65) DEFAULT '',
    ChaPKScore INT DEFAULT 0,
    ChaPKDeath INT DEFAULT 0,
    ChaEquipmentLockEnable INT DEFAULT 0,
    ChaEquipmentLockStatus INT DEFAULT 0,
    ChaStorageLockEnable INT DEFAULT 0,
    ChaStorageLockStatus INT DEFAULT 0,
    ChaInventoryLockEnable INT DEFAULT 0,
    ChaInventoryLockStatus INT DEFAULT 0,
    ChaCWKill INT DEFAULT 0,
    ChaCWDeath INT DEFAULT 0,
    ChaDeleted INT DEFAULT 0,
    ChaOnline INT DEFAULT 0,
    ChaSkills BYTEA DEFAULT E'',
    ChaSkillSlot BYTEA DEFAULT E'',
    ChaActionSlot BYTEA DEFAULT E'',
    ChaPutOnItems BYTEA DEFAULT E'',
    ChaInven BYTEA DEFAULT E'',
    ChaQuest BYTEA DEFAULT E'',
    ChaItemFood BYTEA DEFAULT E'',
    ChaActivity BYTEA DEFAULT E'',
    ChaCodex BYTEA DEFAULT E''
);

CREATE TABLE IF NOT EXISTS ChaNameInfo (
    ChaNum INT PRIMARY KEY,
    ChaName VARCHAR(33) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS GuildInfo (
    GuNum SERIAL PRIMARY KEY,
    GuName VARCHAR(33) NOT NULL UNIQUE,
    ChaNum INT DEFAULT 0,
    GuMaster VARCHAR(33) DEFAULT '',
    GuNotice VARCHAR(256) DEFAULT '',
    GuRank INT DEFAULT 0,
    GuMarkVer INT DEFAULT 0,
    GuBattleWin INT DEFAULT 0,
    GuBattleLose INT DEFAULT 0,
    GuBattleDraw INT DEFAULT 0,
    GuDeputy INT DEFAULT 0,
    GuMoney BIGINT DEFAULT 0,
    GuIncomeMoney BIGINT DEFAULT 0,
    GuStorage BYTEA DEFAULT E'',
    GuEmblem BYTEA DEFAULT E''
);

CREATE TABLE IF NOT EXISTS GuildRegion (
    RegionID INT PRIMARY KEY,
    GuNum INT DEFAULT 0,
    RegionTax INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS GuildAlliance (
    GuNumP INT NOT NULL,
    GuNumS INT NOT NULL,
    PRIMARY KEY(GuNumP, GuNumS)
);

CREATE TABLE IF NOT EXISTS PetInfo (
    PetNum SERIAL PRIMARY KEY,
    PetChaNum INT DEFAULT 0,
    PetName VARCHAR(33) DEFAULT '',
    PetMID INT DEFAULT 0,
    PetSID INT DEFAULT 0,
    PetType INT DEFAULT 0,
    PetDeleted INT DEFAULT 0,
    PetData BYTEA DEFAULT E''
);

CREATE TABLE IF NOT EXISTS VehicleInfo (
    VehicleNum SERIAL PRIMARY KEY,
    VehicleChaNum INT DEFAULT 0,
    VehicleName VARCHAR(33) DEFAULT '',
    VehicleCardMID INT DEFAULT 0,
    VehicleCardSID INT DEFAULT 0,
    VehicleType INT DEFAULT 0,
    VehicleDeleted INT DEFAULT 0,
    VehicleData BYTEA DEFAULT E''
);

CREATE TABLE IF NOT EXISTS UserInven (
    UserNum INT PRIMARY KEY,
    UserInven BYTEA DEFAULT E'',
    UserMoney BIGINT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS ChaEquipmentPass (
    Num SERIAL PRIMARY KEY,
    ChaNum INT DEFAULT 0,
    ChaName VARCHAR(33) DEFAULT '',
    ChaEPass VARCHAR(65) DEFAULT '',
    Date TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE VIEW viewChaFriend AS
SELECT 
    f.ChaP,
    f.ChaS,
    c.ChaName,
    f.ChaFlag
FROM (
    SELECT 0 AS ChaP, 0 AS ChaS, 0 AS ChaFlag
) f
LEFT JOIN ChaInfo c ON c.ChaNum = f.ChaS;

-- ----------------------------------------------------------------------------
-- SCHEMA FOR: ranlog
-- ----------------------------------------------------------------------------
\connect ranlog;

CREATE TABLE IF NOT EXISTS LogItem (
    LogNum SERIAL PRIMARY KEY,
    LogDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserNum INT DEFAULT 0,
    ChaNum INT DEFAULT 0,
    ItemMID INT DEFAULT 0,
    ItemSID INT DEFAULT 0,
    ActionType INT DEFAULT 0,
    Detail VARCHAR(256) DEFAULT ''
);

CREATE TABLE IF NOT EXISTS LogItemMax (
    MaxNum SERIAL PRIMARY KEY,
    NIDMain INT DEFAULT 0,
    NIDSub INT DEFAULT 0,
    MakeType INT DEFAULT 0,
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0,
    FldNum INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS LogMoney (
    LogNum SERIAL PRIMARY KEY,
    LogDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserNum INT DEFAULT 0,
    ChaNum INT DEFAULT 0,
    MoneyDiff BIGINT DEFAULT 0,
    MoneyTotal BIGINT DEFAULT 0,
    ActionType INT DEFAULT 0,
    Detail VARCHAR(256) DEFAULT ''
);

CREATE TABLE IF NOT EXISTS LogAction (
    LogNum SERIAL PRIMARY KEY,
    LogDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UserNum INT DEFAULT 0,
    ChaNum INT DEFAULT 0,
    ActionType INT DEFAULT 0,
    Detail VARCHAR(256) DEFAULT ''
);

CREATE TABLE IF NOT EXISTS LogPartyMatch (
    LogNum SERIAL PRIMARY KEY,
    LogDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    SGNum INT DEFAULT 0,
    SvrNum INT DEFAULT 0,
    WinCount INT DEFAULT 0,
    LostCount INT DEFAULT 0
);

-- ----------------------------------------------------------------------------
-- SCHEMA FOR: ranshop
-- ----------------------------------------------------------------------------
\connect ranshop;

CREATE TABLE IF NOT EXISTS ShopItem (
    ItemNum SERIAL PRIMARY KEY,
    ItemMID INT DEFAULT 0,
    ItemSID INT DEFAULT 0,
    ItemPrice INT DEFAULT 0,
    ItemStock INT DEFAULT 0,
    ItemAvailable INT DEFAULT 1
);

CREATE TABLE IF NOT EXISTS ShopPurchase (
    PurNum SERIAL PRIMARY KEY,
    UserNum INT DEFAULT 0,
    ChaNum INT DEFAULT 0,
    ItemNum INT DEFAULT 0,
    PurDate TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PurStatus INT DEFAULT 0
);
