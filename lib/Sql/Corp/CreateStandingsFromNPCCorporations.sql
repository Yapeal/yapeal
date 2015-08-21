CREATE TABLE "{database}"."{table_prefix}corpStandingsFromNPCCorporations" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "fromID"   BIGINT(20) UNSIGNED NOT NULL,
    "fromName" CHAR(50)            NOT NULL,
    "standing" DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","fromID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
