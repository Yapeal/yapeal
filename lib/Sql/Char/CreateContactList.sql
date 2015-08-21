CREATE TABLE "{database}"."{table_prefix}charContactList" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(50)            NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "inWatchlist"   CHAR(5)             NOT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","contactID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
