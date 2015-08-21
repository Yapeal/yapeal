CREATE TABLE "{database}"."{table_prefix}corpMedals" (
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "created"     DATETIME            NOT NULL,
    "creatorID"   BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT,
    "medalID"     BIGINT(20) UNSIGNED NOT NULL,
    "title"       VARCHAR(255)        NOT NULL,
    PRIMARY KEY ("ownerID","medalID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
