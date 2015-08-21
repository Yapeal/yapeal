CREATE TABLE "{database}"."{table_prefix}charJumpClones" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "locationID"  BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "cloneName"   CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","jumpCloneID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
