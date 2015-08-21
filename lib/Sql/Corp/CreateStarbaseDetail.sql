CREATE TABLE "{database}"."{table_prefix}corpStarbaseDetail" (
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "itemID"          BIGINT(20) UNSIGNED NOT NULL,
    "onlineTimestamp" DATETIME            NOT NULL,
    "state"           TINYINT(2) UNSIGNED NOT NULL,
    "stateTimestamp"  DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
