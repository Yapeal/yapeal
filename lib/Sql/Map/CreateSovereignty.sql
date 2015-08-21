CREATE TABLE "{database}"."{table_prefix}mapSovereignty" (
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID"   BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("solarSystemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
