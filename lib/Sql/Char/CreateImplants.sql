CREATE TABLE "{database}"."{table_prefix}charImplants" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "typeID"   BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID","typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
