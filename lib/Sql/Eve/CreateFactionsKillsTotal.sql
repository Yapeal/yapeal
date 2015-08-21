CREATE TABLE "{database}"."{table_prefix}eveFactionsKillsTotal" (
    "factionID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionName" CHAR(50) DEFAULT NULL,
    "kills"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
