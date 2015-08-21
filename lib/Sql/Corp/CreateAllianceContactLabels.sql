CREATE TABLE "{database}"."{table_prefix}corpAllianceContactLabels" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(255)           NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
