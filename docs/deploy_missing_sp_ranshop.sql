-- ============================================================================
-- RanOnline: ALL MISSING PostgreSQL Stored Procedures
-- Part 4: RANSHOP database
-- ============================================================================

-- sp_ItemMallBuy - purchase from item mall
CREATE OR REPLACE FUNCTION "sp_ItemMallBuy"(
    p_nUserNum INT, p_nChaNum INT, p_nItemNum INT, p_szPurKey VARCHAR,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
DECLARE
    v_stock INT;
    v_avail INT;
    v_price INT;
BEGIN
    SELECT "ItemStock", "ItemAvailable", "ItemPrice"
    INTO v_stock, v_avail, v_price
    FROM "ShopItemMap" WHERE "ItemNum" = p_nItemNum;

    IF NOT FOUND THEN
        nReturn := -1; -- Item not found
        RETURN;
    END IF;

    IF v_avail <> 1 THEN
        nReturn := -2; -- Item not available
        RETURN;
    END IF;

    IF v_stock = 0 THEN
        nReturn := -3; -- Out of stock
        RETURN;
    END IF;

    -- Create purchase record
    INSERT INTO "ShopPurchase" ("UserNum", "ChaNum", "ItemNum", "PurKey", "PurDate", "PurState")
    VALUES (p_nUserNum, p_nChaNum, p_nItemNum, p_szPurKey, CURRENT_TIMESTAMP, 0);

    -- Decrement stock if not unlimited (-1 = unlimited)
    IF v_stock > 0 THEN
        UPDATE "ShopItemMap" SET "ItemStock" = "ItemStock" - 1 WHERE "ItemNum" = p_nItemNum;
    END IF;

    nReturn := 0;
EXCEPTION WHEN OTHERS THEN
    nReturn := -4;
END;
$fn$ LANGUAGE plpgsql;

-- sp_purchase_change_state - update purchase status
CREATE OR REPLACE FUNCTION sp_purchase_change_state(
    p_szPurKey VARCHAR, p_nFlag INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    UPDATE "ShopPurchase" SET "PurState" = p_nFlag WHERE "PurKey" = p_szPurKey;
    IF FOUND THEN nReturn := 0; ELSE nReturn := -1; END IF;
END;
$fn$ LANGUAGE plpgsql;

-- sp_GenerateTopUpCard - generate top-up card
CREATE OR REPLACE FUNCTION "sp_GenerateTopUpCard"(
    p_nCardType INT, p_nPoints INT, p_nQuantity INT, p_nBatchID INT,
    INOUT nReturn INT DEFAULT 0
) AS $fn$
BEGIN
    -- Top-up card generation stub
    nReturn := 0;
END;
$fn$ LANGUAGE plpgsql;
