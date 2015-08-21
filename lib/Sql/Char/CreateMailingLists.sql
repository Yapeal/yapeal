CREATE TABLE "{database}"."{table_prefix}charMailingLists" (
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "displayName" CHAR(50)            NOT NULL,
    "listID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","listID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
