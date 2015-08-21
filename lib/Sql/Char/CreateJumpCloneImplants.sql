CREATE TABLE "{database}"."{table_prefix}charJumpCloneImplants" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "typeName"    CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID","jumpCloneID","typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
