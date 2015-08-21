CREATE TABLE "{database}"."{table_prefix}corpMemberMedals" (
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "medalID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    "issued"      DATETIME            NOT NULL,
    "issuerID"    BIGINT(20) UNSIGNED NOT NULL,
    "reason"      TEXT,
    "status"      CHAR(8)             NOT NULL,
    PRIMARY KEY ("ownerID","medalID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
