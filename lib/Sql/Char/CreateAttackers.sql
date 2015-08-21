CREATE TABLE "{database}"."{table_prefix}charAttackers" (
    "killID"          BIGINT(20) UNSIGNED NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50)                     DEFAULT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)                     DEFAULT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)                     DEFAULT NULL,
    "damageDone"      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50)                     DEFAULT NULL,
    "finalBlow"       TINYINT(1)          NOT NULL,
    "securityStatus"  DOUBLE              NOT NULL,
    "shipTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    "weaponTypeID"    BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
