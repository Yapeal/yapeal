CREATE TABLE "{database}"."{table_prefix}corpFuel" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "itemID"   BIGINT(20) UNSIGNED NOT NULL,
    "typeID"   BIGINT(20) UNSIGNED NOT NULL,
    "quantity" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","itemID","typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
