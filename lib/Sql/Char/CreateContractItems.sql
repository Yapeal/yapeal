CREATE TABLE "{database}"."{table_prefix}charContractItems" (
    "contractID"  BIGINT(20) UNSIGNED NOT NULL,
    "included"    TINYINT(1) UNSIGNED NOT NULL,
    "quantity"    BIGINT(20) UNSIGNED NOT NULL,
    "rawQuantity" TINYINT(1),
    "recordID"    BIGINT(20) UNSIGNED NOT NULL,
    "singleton"   TINYINT(1) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("contractID","recordID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
