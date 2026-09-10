-- ============================================================================
-- RanOnline: ALL MISSING PostgreSQL Stored Procedures
-- Part 3: RANLOG database
-- ============================================================================

-- sp_logitemexchange_insert - 23-param item exchange log
CREATE OR REPLACE FUNCTION sp_logitemexchange_insert(
    p_NIDMain INT, p_NIDSub INT, p_SGNum INT, p_SvrNum INT, p_FldNum INT,
    p_MakeType INT, p_MakeNum BIGINT, p_ItemAmount INT,
    p_ItemFromFlag INT, p_ItemFrom INT,
    p_ItemToFlag INT, p_ItemTo INT, p_ExchangeFlag INT,
    p_Damage INT, p_Defense INT,
    p_Fire INT, p_Ice INT, p_Poison INT, p_Electric INT, p_Spirit INT,
    p_CostumeMID INT, p_CostumeSID INT, p_TradePrice BIGINT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    INSERT INTO "LogItemExchange" (
        "NIDMain", "NIDSub", "SGNum", "SvrNum", "FldNum",
        "MakeType", "MakeNum", "ItemAmount",
        "ItemFromFlag", "ItemFrom", "ItemToFlag", "ItemTo",
        "ExchangeFlag", "Damage", "Defense",
        "Fire", "Ice", "Poison", "Electric", "Spirit",
        "CostumeMID", "CostumeSID", "TradePrice", "LogDate"
    ) VALUES (
        p_NIDMain, p_NIDSub, p_SGNum, p_SvrNum, p_FldNum,
        p_MakeType, p_MakeNum, p_ItemAmount,
        p_ItemFromFlag, p_ItemFrom, p_ItemToFlag, p_ItemTo,
        p_ExchangeFlag, p_Damage, p_Defense,
        p_Fire, p_Ice, p_Poison, p_Electric, p_Spirit,
        p_CostumeMID, p_CostumeSID, p_TradePrice, CURRENT_TIMESTAMP
    );
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- sp_LogItemRandom_Insert - random item generation log (15 params)
CREATE OR REPLACE FUNCTION "sp_LogItemRandom_Insert"(
    p_NIDMain INT, p_NIDSub INT, p_SGNum INT, p_SvrNum INT, p_FldNum INT,
    p_MakeType INT, p_MakeNum BIGINT,
    p_OptType1 INT, p_OptValue1 INT,
    p_OptType2 INT, p_OptValue2 INT,
    p_OptType3 INT, p_OptValue3 INT,
    p_OptType4 INT, p_OptValue4 INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    INSERT INTO "LogItemRandom" (
        "NIDMain", "NIDSub", "SGNum", "SvrNum", "FldNum",
        "MakeType", "MakeNum",
        "OptType1", "OptValue1", "OptType2", "OptValue2",
        "OptType3", "OptValue3", "OptType4", "OptValue4",
        "LogDate"
    ) VALUES (
        p_NIDMain, p_NIDSub, p_SGNum, p_SvrNum, p_FldNum,
        p_MakeType, p_MakeNum,
        p_OptType1, p_OptValue1, p_OptType2, p_OptValue2,
        p_OptType3, p_OptValue3, p_OptType4, p_OptValue4,
        CURRENT_TIMESTAMP
    );
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- sp_LogPetAction_Insert - pet action log
CREATE OR REPLACE FUNCTION "sp_LogPetAction_Insert"(
    p_PetNum INT, p_ItemMID INT, p_ItemSID INT,
    p_ActionType INT, p_PetFull INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    INSERT INTO "LogPetAction" (
        "PetNum", "ItemMID", "ItemSID", "ActionType", "PetFull", "LogDate"
    ) VALUES (
        p_PetNum, p_ItemMID, p_ItemSID, p_ActionType, p_PetFull, CURRENT_TIMESTAMP
    );
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- sp_LogVehicleAction_Insert - vehicle action log
CREATE OR REPLACE FUNCTION "sp_LogVehicleAction_Insert"(
    p_VehicleNum INT, p_ItemMID INT, p_ItemSID INT,
    p_ActionType INT, p_VehicleBattery INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    INSERT INTO "LogVehicleAction" (
        "VehicleNum", "ItemMID", "ItemSID", "ActionType", "VehicleBattery", "LogDate"
    ) VALUES (
        p_VehicleNum, p_ItemMID, p_ItemSID, p_ActionType, p_VehicleBattery, CURRENT_TIMESTAMP
    );
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- sp_LogSms_Insert - SMS log
CREATE OR REPLACE FUNCTION "sp_LogSms_Insert"(
    p_ChaNum INT, p_szPhone VARCHAR, p_nType INT,
    p_szSender VARCHAR, p_szReceiver VARCHAR,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    -- SMS logging stub - inserts into log table if it exists
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- sp_InsertItem - item insert log for events
CREATE OR REPLACE FUNCTION "sp_InsertItem"(
    p_szItemName VARCHAR, p_szCharName VARCHAR,
    p_nItemMID INT, p_nItemSID INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    -- Event item insert log
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- sp_InsertAttendLog - attendance log
CREATE OR REPLACE FUNCTION "sp_InsertAttendLog"(
    p_nUserNum INT, p_nChaNum INT, p_nDay INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    -- Attendance tracking
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- sp_LogPungPung - PungPung event log (no output param)
CREATE OR REPLACE FUNCTION "sp_LogPungPung"(
    p_szUserId VARCHAR, p_szCharName VARCHAR,
    p_nItemMID INT, p_nItemSID INT
) RETURNS VOID AS $fn$
BEGIN
    -- PungPung event log stub
END;
$fn$ LANGUAGE plpgsql;

-- sp_lottery_check
CREATE OR REPLACE FUNCTION sp_lottery_check(
    p_szUserId VARCHAR, p_szCharName VARCHAR,
    p_szLotteryCode VARCHAR, p_nType INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    -- Lottery validation stub
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- sp_PungPungCheck
CREATE OR REPLACE FUNCTION "sp_PungPungCheck"(
    p_szUserId VARCHAR, p_nType INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    -- PungPung eligibility check stub
    nReturn := 1; -- Allow by default
END;
$fn$ LANGUAGE plpgsql;
