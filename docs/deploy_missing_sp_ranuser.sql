-- ============================================================================
-- RanOnline: ALL MISSING PostgreSQL Stored Procedures
-- Part 1: RANUSER database (publisher aliases + core user)
-- ============================================================================

-- ============================================================================
-- CORE USER FUNCTIONS
-- ============================================================================

-- UpdateChaName - rename character (updates UserInfo ChaName reference)
CREATE OR REPLACE FUNCTION "UpdateChaName"(p_nUserNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    -- Update handled on game-side ChaInfo; this is a no-op stub for publisher routing
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;

-- user_forgotpass - password recovery via security answer
CREATE OR REPLACE FUNCTION user_forgotpass(p_userId VARCHAR, p_userSA VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE
    v_nUserNum INT := 0;
    v_sa VARCHAR;
BEGIN
    SELECT "UserNum", "UserSA" INTO v_nUserNum, v_sa
    FROM "UserInfo" WHERE "UserID" = p_userId;

    IF NOT FOUND OR v_nUserNum IS NULL OR v_nUserNum = 0 THEN
        nReturn := 0; -- not found
        RETURN;
    END IF;

    IF v_sa = p_userSA THEN
        nReturn := 1; -- success - client will get the password
    ELSE
        nReturn := 0; -- wrong answer
    END IF;
END;
$fn$ LANGUAGE plpgsql;

-- Thai_GetGameTime
CREATE OR REPLACE FUNCTION "Thai_GetGameTime"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$
BEGIN
    SELECT COALESCE("PlayTime", 0) INTO nReturn FROM "UserInfo" WHERE "UserNum" = p_nUserNum;
    IF NOT FOUND OR nReturn IS NULL THEN nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: user_gettype
-- ============================================================================
CREATE OR REPLACE FUNCTION daum_user_gettype(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_gettype(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION excite_user_gettype(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_gettype(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_user_gettype"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_gettype(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION terra_user_gettype(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_gettype(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION gsp_user_gettype(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_gettype(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_user_gettype"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_gettype(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: user_cha_remain
-- ============================================================================
CREATE OR REPLACE FUNCTION daum_user_cha_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION excite_user_cha_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_user_cha_remain"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION terra_user_cha_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION gsp_user_cha_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_user_cha_remain"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: user_cha_test_remain
-- ============================================================================
CREATE OR REPLACE FUNCTION daum_user_cha_test_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_test_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION excite_user_cha_test_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_test_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_user_cha_test_remain"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_test_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION terra_user_cha_test_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_test_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION gsp_user_cha_test_remain(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_cha_test_remain(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: user_logout
-- ============================================================================
CREATE OR REPLACE FUNCTION daum_user_logout(p_userId VARCHAR, p_usernum INT, p_gametime INT, p_chanum INT, p_svrgrp INT, p_svrnum INT, p_extra INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_logout(p_userId, p_usernum, p_gametime, p_chanum, p_svrgrp, p_svrnum, p_extra) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION excite_user_logout(p_userId VARCHAR, p_usernum INT, p_gametime INT, p_chanum INT, p_svrgrp INT, p_svrnum INT, p_extra INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_logout(p_userId, p_usernum, p_gametime, p_chanum, p_svrgrp, p_svrnum, p_extra) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_user_logout"(p_userId VARCHAR, p_usernum INT, p_gametime INT, p_chanum INT, p_svrgrp INT, p_svrnum INT, p_extra INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_logout(p_userId, p_usernum, p_gametime, p_chanum, p_svrgrp, p_svrnum, p_extra) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION terra_user_logout(p_userId VARCHAR, p_usernum INT, p_gametime INT, p_chanum INT, p_svrgrp INT, p_svrnum INT, p_extra INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_logout(p_userId, p_usernum, p_gametime, p_chanum, p_svrgrp, p_svrnum, p_extra) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION gsp_user_logout(p_userId VARCHAR, p_usernum INT, p_gametime INT, p_chanum INT, p_svrgrp INT, p_svrnum INT, p_extra INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_logout(p_userId, p_usernum, p_gametime, p_chanum, p_svrgrp, p_svrnum, p_extra) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_user_logout"(p_userId VARCHAR, p_usernum INT, p_gametime INT, p_chanum INT, p_svrgrp INT, p_svrnum INT, p_extra INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT user_logout(p_userId, p_usernum, p_gametime, p_chanum, p_svrgrp, p_svrnum, p_extra) INTO nReturn; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: UserLogoutSimple
-- ============================================================================
CREATE OR REPLACE FUNCTION "Daum_UserLogoutSimple"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Excite_UserLogoutSimple"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_UserLogoutSimple"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Terra_UserLogoutSimple"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Gsp_UserLogoutSimple"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_UserLogoutSimple"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: UserLogoutSimple2
-- ============================================================================
CREATE OR REPLACE FUNCTION "Daum_UserLogoutSimple2"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Excite_UserLogoutSimple2"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_UserLogoutSimple2"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Terra_UserLogoutSimple2"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Gsp_UserLogoutSimple2"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_UserLogoutSimple2"(p_usernum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN UPDATE "UserInfo" SET "UserLoginState" = 0 WHERE "UserNum" = p_usernum; nReturn := 0; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: UpdateChaName
-- ============================================================================
CREATE OR REPLACE FUNCTION "Daum_UpdateChaName"(p_nUserNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaName"(p_nUserNum, p_szChaName) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Excite_UpdateChaName"(p_nUserNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaName"(p_nUserNum, p_szChaName) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_UpdateChaName"(p_nUserNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaName"(p_nUserNum, p_szChaName) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Terra_UpdateChaName"(p_nUserNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaName"(p_nUserNum, p_szChaName) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Gsp_UpdateChaName"(p_nUserNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaName"(p_nUserNum, p_szChaName) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_UpdateChaName"(p_nUserNum INT, p_szChaName VARCHAR, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaName"(p_nUserNum, p_szChaName) INTO nReturn; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: UpdateTestChaNumIncrease / Decrease
-- ============================================================================
CREATE OR REPLACE FUNCTION "Daum_UpdateTestChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumIncrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Daum_UpdateTestChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumDecrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Excite_UpdateChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaNumIncrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Excite_UpdateChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateChaNumDecrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Excite_UpdateTestChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumIncrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Excite_UpdateTestChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumDecrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_UpdateTestChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumIncrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "GS_UpdateTestChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumDecrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Gsp_UpdateTestChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumIncrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Gsp_UpdateTestChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumDecrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_UpdateTestChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumIncrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Japan_UpdateTestChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumDecrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Terra_UpdateTestChaNumIncrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumIncrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION "Terra_UpdateTestChaNumDecrease"(p_nUserNum INT, INOUT nReturn INT DEFAULT 0) AS $fn$ BEGIN SELECT "UpdateTestChaNumDecrease"(p_nUserNum) INTO nReturn; END; $fn$ LANGUAGE plpgsql;

-- ============================================================================
-- PUBLISHER ALIAS: user_passcheck
-- ============================================================================
CREATE OR REPLACE FUNCTION daum_user_passcheck(p_userId VARCHAR, p_userPass VARCHAR, p_nCheckFlag INT, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE v_pass VARCHAR;
BEGIN
    SELECT "UserPass" INTO v_pass FROM "UserInfo" WHERE "UserID" = p_userId;
    IF NOT FOUND THEN nReturn := 0; RETURN; END IF;
    IF v_pass = p_userPass THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION terra_user_passcheck(p_userId VARCHAR, p_userPass VARCHAR, p_nCheckFlag INT, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE v_pass VARCHAR;
BEGIN
    SELECT "UserPass" INTO v_pass FROM "UserInfo" WHERE "UserID" = p_userId;
    IF NOT FOUND THEN nReturn := 0; RETURN; END IF;
    IF v_pass = p_userPass THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION excite_user_passcheck(p_uid VARCHAR, p_userId VARCHAR, p_userPass VARCHAR, p_nCheckFlag INT, INOUT nReturn INT DEFAULT 0) AS $fn$
DECLARE v_pass VARCHAR;
BEGIN
    SELECT "UserPass" INTO v_pass FROM "UserInfo" WHERE "UserID" = p_userId;
    IF NOT FOUND THEN nReturn := 0; RETURN; END IF;
    IF v_pass = p_userPass THEN nReturn := 1; ELSE nReturn := 0; END IF;
END;
$fn$ LANGUAGE plpgsql;
