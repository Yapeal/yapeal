CREATE TABLE "{database}"."{table_prefix}charCorporationTitles" (
    "ownerID"   BIGINT(20) UNSIGNED NOT NULL,
    "titleID"   BIGINT(20) UNSIGNED NOT NULL,
    "titleName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","titleID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
