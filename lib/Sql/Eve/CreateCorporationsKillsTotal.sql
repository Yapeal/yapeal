CREATE TABLE "{database}"."{table_prefix}eveCorporationsKillsTotal" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "kills"           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("corporationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
