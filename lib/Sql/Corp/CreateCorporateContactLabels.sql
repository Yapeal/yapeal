CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}corpCorporateContactLabels" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(255)           NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
