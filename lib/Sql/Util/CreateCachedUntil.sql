CREATE TABLE "{database}"."{table_prefix}utilCachedUntil" (
    "apiName"     CHAR(32)            NOT NULL,
    "expires"     DATETIME            NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "sectionName" CHAR(8)             NOT NULL,
    PRIMARY KEY ("apiName","ownerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
