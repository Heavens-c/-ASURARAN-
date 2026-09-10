-- ============================================================================
-- RanOnline: ALL MISSING PostgreSQL Stored Procedures
-- Part 2: RANGAME1 database
-- ============================================================================

-- ============================================================================
-- FRIEND FUNCTIONS
-- ============================================================================

CREATE OR REPLACE FUNCTION "InsertChaFriend"(p_ChaP INT, p_ChaS INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "ChaFriend" ("ChaP", "ChaS", "ChaFlag")
    VALUES (p_ChaP, p_ChaS, 0);
    nReturn := 0;
EXCEPTION WHEN unique_violation THEN
    nReturn := -1;
WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "DeleteChaFriend"(p_ChaP INT, p_ChaS INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    DELETE FROM "ChaFriend" WHERE "ChaP" = p_ChaP AND "ChaS" = p_ChaS;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaFriend"(p_ChaP INT, p_ChaS INT, p_nFlag INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaFriend" SET "ChaFlag" = p_nFlag WHERE "ChaP" = p_ChaP AND "ChaS" = p_ChaS;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "UpdateChaFriendSms"(p_ChaNum INT, p_szPhoneNumber VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    -- SMS friend update stub - updates phone number association
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- ============================================================================
-- INVENTORY FUNCTIONS
-- ============================================================================

CREATE OR REPLACE FUNCTION "GetInvenCount"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    SELECT COUNT(*) INTO nReturn FROM "UserInven" WHERE "UserNum" = p_nUserNum;
    IF nReturn IS NULL THEN nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdateUserMoneyAdd"(p_nUserNum INT, p_lnMoney BIGINT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInven" SET "UserMoney" = "UserMoney" + p_lnMoney WHERE "UserNum" = p_nUserNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PET FUNCTIONS
-- ============================================================================

CREATE OR REPLACE FUNCTION "sp_InsertPet"(
    p_szPetName VARCHAR, p_nChaNum INT, p_nPetType INT,
    p_nPetMID INT, p_nPetSID INT,
    p_nPetCardMID INT, p_nPetCardSID INT,
    p_nPetStyle INT, p_nPetColor INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
DECLARE v_id INT;
BEGIN
    INSERT INTO "PetInfo" ("PetName", "PetChaNum", "PetType", "PetMID", "PetSID",
                           "PetCardMID", "PetCardSID", "PetStyle", "PetColor",
                           "PetFull", "PetDeleted", "PetDeletedDate")
    VALUES (p_szPetName, p_nChaNum, p_nPetType, p_nPetMID, p_nPetSID,
            p_nPetCardMID, p_nPetCardSID, p_nPetStyle, p_nPetColor,
            100, 0, CURRENT_TIMESTAMP)
    RETURNING "PetNum" INTO v_id;
    nReturn := v_id;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_DeletePet"(p_nChaNum INT, p_nPetNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetDeleted" = 1, "PetDeletedDate" = CURRENT_TIMESTAMP
    WHERE "PetNum" = p_nPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_RestorePet"(p_dwPetNum INT, p_nChaNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetDeleted" = 0
    WHERE "PetNum" = p_dwPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_RenamePet"(p_nChaNum INT, p_nPetNum INT, p_szPetName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetName" = p_szPetName
    WHERE "PetNum" = p_nPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdatePetChaNum"(p_nChaNum INT, p_nPetNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetChaNum" = p_nChaNum WHERE "PetNum" = p_nPetNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdatePetColor"(p_nChaNum INT, p_nPetNum INT, p_nColor INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetColor" = p_nColor
    WHERE "PetNum" = p_nPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdatePetStyle"(p_nChaNum INT, p_nPetNum INT, p_nStyle INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetStyle" = p_nStyle
    WHERE "PetNum" = p_nPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdatePetFull"(p_nChaNum INT, p_nPetNum INT, p_nPetFull INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetFull" = p_nPetFull
    WHERE "PetNum" = p_nPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_GetPetFull"(p_nChaNum INT, p_dwPetNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    SELECT COALESCE("PetFull", 0) INTO nReturn FROM "PetInfo"
    WHERE "PetNum" = p_dwPetNum AND "PetChaNum" = p_nChaNum AND "PetDeleted" = 0;
    IF NOT FOUND OR nReturn IS NULL THEN nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdatePetInven"(
    p_nChaNum INT, p_nPetNum INT,
    p_nType INT, p_nMID INT, p_nSID INT,
    p_nCMID INT, p_nCSID INT, p_nAvail INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    -- PetInven table tracks individual pet inventory slots
    INSERT INTO "PetInven" ("PetNum", "PetChaNum", "InvenType", "InvenMID", "InvenSID",
                            "InvenCMID", "InvenCSID", "InvenAvailable")
    VALUES (p_nPetNum, p_nChaNum, p_nType, p_nMID, p_nSID, p_nCMID, p_nCSID, p_nAvail)
    ON CONFLICT DO NOTHING;
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdatePetSkin"(
    p_nChaNum INT, p_dwPetNum INT,
    p_nSkinMID INT, p_nSkinSID INT,
    p_nSkinScale INT, p_nSkinTime INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    UPDATE "PetInfo" SET
        "PetSkinMID" = p_nSkinMID,
        "PetSkinSID" = p_nSkinSID,
        "PetSkinScale" = p_nSkinScale,
        "PetSkinTime" = p_nSkinTime
    WHERE "PetNum" = p_dwPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UpdatePetDualSkill"(p_nChaNum INT, p_nPetNum INT, p_bDualSkill INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "PetInfo" SET "PetDualSkill" = p_bDualSkill
    WHERE "PetNum" = p_nPetNum AND "PetChaNum" = p_nChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- ============================================================================
-- GUILD BATTLE & ALLIANCE FUNCTIONS
-- ============================================================================

CREATE OR REPLACE FUNCTION "InsertGuildAlliance"(p_dwClubP INT, p_dwClubS INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "GuildAlliance" ("GuNumP", "GuNumS") VALUES (p_dwClubP, p_dwClubS);
    nReturn := 0;
EXCEPTION WHEN unique_violation THEN
    nReturn := -1;
WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "DeleteGuildAlliance"(p_dwClubP INT, p_dwClubS INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    DELETE FROM "GuildAlliance" WHERE "GuNumP" = p_dwClubP AND "GuNumS" = p_dwClubS;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION sp_add_guild_region(p_dwRegionID INT, p_dwClub INT, p_fTax DOUBLE PRECISION, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "GuildRegion" ("RegionID", "GuNum", "RegionTax")
    VALUES (p_dwRegionID, p_dwClub, p_fTax::INT)
    ON CONFLICT ("RegionID") DO UPDATE SET "GuNum" = p_dwClub, "RegionTax" = p_fTax::INT;
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION sp_delete_guild_region(p_dwRegionID INT, p_dwClub INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    DELETE FROM "GuildRegion" WHERE "RegionID" = p_dwRegionID AND "GuNum" = p_dwClub;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION sp_update_guild_rank(p_dwClub INT, p_dwRank INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "GuildInfo" SET "GuRank" = p_dwRank WHERE "GuNum" = p_dwClub;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_RequestGuBattle"(p_dwClubP INT, p_dwClubS INT, p_dwEndTime INT, p_nAlliance INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "GuildBattle" ("GuNumP", "GuNumS", "EndTime", "Alliance", "BattleState", "BattleDate")
    VALUES (p_dwClubP, p_dwClubS, p_dwEndTime, p_nAlliance, 1, CURRENT_TIMESTAMP);
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_EndGuBattle"(p_dwClubP INT, p_dwClubS INT, p_nGuFlag INT, p_nKillNum INT, p_nDeathNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "GuildBattle" SET "BattleState" = 0, "GuFlag" = p_nGuFlag,
           "KillNum" = p_nKillNum, "DeathNum" = p_nDeathNum
    WHERE "GuNumP" = p_dwClubP AND "GuNumS" = p_dwClubS AND "BattleState" = 1;

    -- Update guild win/lose/draw stats
    IF p_nGuFlag = 1 THEN
        UPDATE "GuildInfo" SET "GuBattleWin" = "GuBattleWin" + 1 WHERE "GuNum" = p_dwClubP;
        UPDATE "GuildInfo" SET "GuBattleLose" = "GuBattleLose" + 1 WHERE "GuNum" = p_dwClubS;
    ELSIF p_nGuFlag = 2 THEN
        UPDATE "GuildInfo" SET "GuBattleLose" = "GuBattleLose" + 1 WHERE "GuNum" = p_dwClubP;
        UPDATE "GuildInfo" SET "GuBattleWin" = "GuBattleWin" + 1 WHERE "GuNum" = p_dwClubS;
    ELSE
        UPDATE "GuildInfo" SET "GuBattleDraw" = "GuBattleDraw" + 1 WHERE "GuNum" IN (p_dwClubP, p_dwClubS);
    END IF;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_EndGuAllianceBattle"(p_dwClubP INT, p_dwClubS INT, p_nGuFlag INT, p_nKillNum INT, p_nDeathNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "GuildBattle" SET "BattleState" = 0, "GuFlag" = p_nGuFlag,
           "KillNum" = p_nKillNum, "DeathNum" = p_nDeathNum
    WHERE "GuNumP" = p_dwClubP AND "GuNumS" = p_dwClubS AND "BattleState" = 1 AND "Alliance" = 1;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_SaveGuBattle"(p_dwClubP INT, p_dwClubS INT, p_nKillNum INT, p_nDeathNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "GuildBattle" SET "KillNum" = p_nKillNum, "DeathNum" = p_nDeathNum
    WHERE "GuNumP" = p_dwClubP AND "GuNumS" = p_dwClubS AND "BattleState" = 1;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_ResetAllianceBattle"(p_dwClub INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "GuildBattle" SET "BattleState" = 0
    WHERE ("GuNumP" = p_dwClub OR "GuNumS" = p_dwClub) AND "BattleState" = 1 AND "Alliance" = 1;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "InsertPartyMatch"(p_SGNum INT, p_SvrNum INT, p_WinCount INT, p_LostCount INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "LogPartyMatch" ("SGNum", "SvrNum", "WinCount", "LostCount", "LogDate")
    VALUES (p_SGNum, p_SvrNum, p_WinCount, p_LostCount, CURRENT_TIMESTAMP);
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

-- ============================================================================
-- EQUIPMENT / STORAGE / INVENTORY LOCK FUNCTIONS
-- ============================================================================

-- Equipment Lock
CREATE OR REPLACE FUNCTION "sp_InsertEquipmentPass"(p_dwChaNum INT, p_szName VARCHAR, p_szPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "ChaEquipmentPass" ("ChaNum", "ChaName", "ChaEPass", "Date")
    VALUES (p_dwChaNum, p_szName, p_szPin, CURRENT_TIMESTAMP);
    UPDATE "ChaInfo" SET "ChaEquipmentLockEnable" = 1, "ChaEquipmentLockStatus" = 1 WHERE "ChaNum" = p_dwChaNum;
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_CheckEquipmentPass"(p_dwChaNum INT, p_szName VARCHAR, p_szPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE v_pass VARCHAR;
BEGIN
    SELECT "ChaEPass" INTO v_pass FROM "ChaEquipmentPass" WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName ORDER BY "Num" DESC LIMIT 1;
    IF NOT FOUND THEN nReturn := -1; RETURN; END IF;
    IF v_pass = p_szPin THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_ChangeEquipmentPass"(p_dwChaNum INT, p_szName VARCHAR, p_szNewPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaEquipmentPass" SET "ChaEPass" = p_szNewPin, "Date" = CURRENT_TIMESTAMP
    WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_DeleteEquipmentPass"(p_dwChaNum INT, p_szName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    DELETE FROM "ChaEquipmentPass" WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName;
    UPDATE "ChaInfo" SET "ChaEquipmentLockEnable" = 0, "ChaEquipmentLockStatus" = 0 WHERE "ChaNum" = p_dwChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- Storage Lock
CREATE OR REPLACE FUNCTION "sp_InsertStoragePass"(p_dwChaNum INT, p_szName VARCHAR, p_szPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "ChaStoragePass" ("ChaNum", "ChaName", "ChaSPass", "Date")
    VALUES (p_dwChaNum, p_szName, p_szPin, CURRENT_TIMESTAMP);
    UPDATE "ChaInfo" SET "ChaStorageLockEnable" = 1, "ChaStorageLockStatus" = 1 WHERE "ChaNum" = p_dwChaNum;
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_CheckStoragePass"(p_dwChaNum INT, p_szName VARCHAR, p_szPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE v_pass VARCHAR;
BEGIN
    SELECT "ChaSPass" INTO v_pass FROM "ChaStoragePass" WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName ORDER BY "Num" DESC LIMIT 1;
    IF NOT FOUND THEN nReturn := -1; RETURN; END IF;
    IF v_pass = p_szPin THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_ChangeStoragePass"(p_dwChaNum INT, p_szName VARCHAR, p_szNewPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaStoragePass" SET "ChaSPass" = p_szNewPin, "Date" = CURRENT_TIMESTAMP
    WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_DeleteStoragePass"(p_dwChaNum INT, p_szName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    DELETE FROM "ChaStoragePass" WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName;
    UPDATE "ChaInfo" SET "ChaStorageLockEnable" = 0, "ChaStorageLockStatus" = 0 WHERE "ChaNum" = p_dwChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- Inventory Lock
CREATE OR REPLACE FUNCTION "sp_InsertInventoryPass"(p_dwChaNum INT, p_szName VARCHAR, p_szPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    INSERT INTO "ChaInventoryPass" ("ChaNum", "ChaName", "ChaIPass", "Date")
    VALUES (p_dwChaNum, p_szName, p_szPin, CURRENT_TIMESTAMP);
    UPDATE "ChaInfo" SET "ChaInventoryLockEnable" = 1, "ChaInventoryLockStatus" = 1 WHERE "ChaNum" = p_dwChaNum;
    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -1;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_CheckInventoryPass"(p_dwChaNum INT, p_szName VARCHAR, p_szPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE v_pass VARCHAR;
BEGIN
    SELECT "ChaIPass" INTO v_pass FROM "ChaInventoryPass" WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName ORDER BY "Num" DESC LIMIT 1;
    IF NOT FOUND THEN nReturn := -1; RETURN; END IF;
    IF v_pass = p_szPin THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_ChangeInventoryPass"(p_dwChaNum INT, p_szName VARCHAR, p_szNewPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "ChaInventoryPass" SET "ChaIPass" = p_szNewPin, "Date" = CURRENT_TIMESTAMP
    WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_DeleteInventoryPass"(p_dwChaNum INT, p_szName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    DELETE FROM "ChaInventoryPass" WHERE "ChaNum" = p_dwChaNum AND "ChaName" = p_szName;
    UPDATE "ChaInfo" SET "ChaInventoryLockEnable" = 0, "ChaInventoryLockStatus" = 0 WHERE "ChaNum" = p_dwChaNum;
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- ============================================================================
-- CHARACTER GENDER & WEB API FUNCTIONS
-- ============================================================================

CREATE OR REPLACE FUNCTION "sp_UpdateChaGender"(
    p_dwChaNum INT, p_nClass INT, p_nSex INT,
    p_nFace INT, p_nHair INT, p_nHairColor INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    UPDATE "ChaInfo" SET
        "ChaClass" = p_nClass, "ChaSex" = p_nSex,
        "ChaFace" = p_nFace, "ChaHair" = p_nHair, "ChaHairColor" = p_nHairColor
    WHERE "ChaNum" = p_dwChaNum;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_UserCheckPass"(p_nUsrNum INT, p_szPass2 VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE v_pass2 VARCHAR;
BEGIN
    SELECT "SubPassword" INTO v_pass2 FROM "UserInfo" WHERE "UserNum" = p_nUsrNum;
    IF NOT FOUND THEN nReturn := -1; RETURN; END IF;
    IF v_pass2 IS NULL OR v_pass2 = '' OR v_pass2 = p_szPass2 THEN
        nReturn := 1; -- pass OK or no sub-password set
    ELSE
        nReturn := 0; -- wrong sub-password
    END IF;
END;
$fn$ LANGUAGE plpgsql;

-- Note: sp_UserCheckPass references UserInfo which is in ranuser DB.
-- The C++ code calls it against the game DB, so we need it here too.
-- We'll create a local stub that always succeeds (the real check is done server-side).

CREATE OR REPLACE FUNCTION selchar_changepass(p_userId VARCHAR, p_oldPass VARCHAR, p_newPass VARCHAR, p_sa VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "UserPass" = p_newPass
    WHERE "UserID" = p_userId AND "UserPass" = p_oldPass AND "UserSA" = p_sa;
    IF FOUND THEN nReturn := 1; ELSE nReturn := 0; END IF;
EXCEPTION WHEN undefined_table THEN
    nReturn := 0; -- UserInfo not in this DB
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION selchar_changepin(p_userId VARCHAR, p_oldPin VARCHAR, p_newPin VARCHAR, p_sa VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "SubPinCode" = p_newPin
    WHERE "UserID" = p_userId AND "SubPinCode" = p_oldPin AND "UserSA" = p_sa;
    IF FOUND THEN nReturn := 1; ELSE nReturn := 0; END IF;
EXCEPTION WHEN undefined_table THEN
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION selchar_resetpin(p_userId VARCHAR, p_pass VARCHAR, p_newPin VARCHAR, p_sa VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    UPDATE "UserInfo" SET "SubPinCode" = p_newPin
    WHERE "UserID" = p_userId AND "UserPass" = p_pass AND "UserSA" = p_sa;
    IF FOUND THEN nReturn := 1; ELSE nReturn := 0; END IF;
EXCEPTION WHEN undefined_table THEN
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION selchar_topup(p_userId VARCHAR, p_cardCode VARCHAR, p_cardPin VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_usernum INT;
    v_points INT;
BEGIN
    -- Validate top-up card and add points
    SELECT "UserNum" INTO v_usernum FROM "UserInfo" WHERE "UserID" = p_userId;
    IF NOT FOUND THEN nReturn := 0; RETURN; END IF;
    -- Stub: top-up processing would validate card against TopUp table
    nReturn := 0;
EXCEPTION WHEN undefined_table THEN
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION selchar_changemail(p_userId VARCHAR, p_pass VARCHAR, p_sa VARCHAR, p_newMail VARCHAR, p_confirmMail VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    IF p_newMail <> p_confirmMail THEN nReturn := -1; RETURN; END IF;
    UPDATE "UserInfo" SET "UserEmail" = p_newMail
    WHERE "UserID" = p_userId AND "UserPass" = p_pass AND "UserSA" = p_sa;
    IF FOUND THEN nReturn := 1; ELSE nReturn := 0; END IF;
EXCEPTION WHEN undefined_table THEN
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION "sp_ReqGameTimeConvert"(p_userId VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    -- Game time conversion stub
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;
