CREATE TABLE "{database}"."{table_prefix}eveFactionWars" (
    "factionID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionName" CHAR(50) DEFAULT NULL,
    "againstID"   BIGINT(20) UNSIGNED NOT NULL,
    "againstName" CHAR(50) DEFAULT NULL
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
