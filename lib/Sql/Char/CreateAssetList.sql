CREATE TABLE "{database}"."{table_prefix}charAssetList" (
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "flag"        SMALLINT(5) UNSIGNED NOT NULL,
    "itemID"      BIGINT(20) UNSIGNED  NOT NULL,
    "lft"         BIGINT(20) UNSIGNED  NOT NULL,
    "locationID"  BIGINT(20) UNSIGNED  NOT NULL,
    "lvl"         TINYINT(2) UNSIGNED  NOT NULL,
    "quantity"    BIGINT(20) UNSIGNED  NOT NULL,
    "rawQuantity" BIGINT(20) DEFAULT NULL,
    "rgt"         BIGINT(20) UNSIGNED  NOT NULL,
    "singleton"   TINYINT(1)           NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAssetList" ADD INDEX "charAssetList1"  ("lft");
ALTER TABLE "{database}"."{table_prefix}charAssetList" ADD INDEX "charAssetList2"  ("locationID");
