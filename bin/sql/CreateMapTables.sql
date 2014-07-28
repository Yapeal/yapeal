SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
DROP TABLE IF EXISTS "{database}"."{table_prefix}mapFacWarSystems";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}mapFacWarSystems" (
    "contested"             ENUM('False', 'True') NOT NULL,
    "occupyingFactionID"    BIGINT(20) UNSIGNED DEFAULT NULL,
    "occupyingFactionName"  CHAR(24)            DEFAULT NULL,
    "owningFactionID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    "owningFactionName"     CHAR(24)            DEFAULT NULL,
    "solarSystemID"         BIGINT(20) UNSIGNED   NOT NULL,
    "solarSystemName"       CHAR(24)              NOT NULL,
    "victoryPoints"         BIGINT(20) UNSIGNED   NOT NULL,
    "victoryPointThreshold" BIGINT(20) UNSIGNED   NOT NULL,
    PRIMARY KEY ("solarSystemID")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS "{database}"."{table_prefix}mapJumps";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}mapJumps" (
    "shipJumps"     BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("solarSystemID")
)
    ENGINE =InnoDB;
DROP TABLE IF EXISTS "{database}"."{table_prefix}mapKills";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}mapKills" (
    "factionKills"  BIGINT(20) UNSIGNED NOT NULL,
    "podKills"      BIGINT(20) UNSIGNED NOT NULL,
    "shipKills"     BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("solarSystemID")
)
    ENGINE =InnoDB;
DROP TABLE IF EXISTS "{database}"."{table_prefix}mapSovereignty";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}mapSovereignty" (
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID"   BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(24)            NOT NULL,
    PRIMARY KEY ("solarSystemID")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
