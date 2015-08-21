CREATE TABLE "{database}"."{table_prefix}charVictim" (
    "killID"          BIGINT(20) UNSIGNED NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50) DEFAULT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)            NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "damageTaken"     BIGINT(20) UNSIGNED NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50) DEFAULT NULL,
    "shipTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
