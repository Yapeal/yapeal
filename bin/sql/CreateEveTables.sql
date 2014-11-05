CREATE TABLE "{database}"."{table_prefix}eveAllianceList" (
    "allianceID"     BIGINT(20) UNSIGNED NOT NULL,
    "executorCorpID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "memberCount"    BIGINT(20) UNSIGNED DEFAULT NULL,
    "name"           CHAR(50)            DEFAULT NULL,
    "shortName"      CHAR(5)             DEFAULT NULL,
    "startDate"      DATETIME            DEFAULT NULL,
    PRIMARY KEY ("allianceID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCharacterInfo" (
    "characterID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterName"     CHAR(50)            NOT NULL,
    "race"              CHAR(8)             NOT NULL,
    "bloodline"         CHAR(24)            NOT NULL,
    "accountBalance"    DECIMAL(17, 2)      NOT NULL,
    "skillPoints"       BIGINT(20) UNSIGNED NOT NULL,
    "nextTrainingEnds"  DATETIME            NOT NULL,
    "shipName"          CHAR(255)
                        COLLATE utf8_unicode_ci DEFAULT '',
    "shipTypeID"        BIGINT(20) UNSIGNED     DEFAULT '0',
    "shipTypeName"      CHAR(50)                DEFAULT '',
    "corporationID"     BIGINT(20) UNSIGNED NOT NULL,
    "corporation"       CHAR(50)            NOT NULL,
    "corporationDate"   DATETIME            NOT NULL,
    "allianceID"        BIGINT(20) UNSIGNED     DEFAULT '0',
    "alliance"          CHAR(50)                DEFAULT '',
    "allianceDate"      DATETIME            NOT NULL,
    "lastKnownLocation" CHAR(255)           NOT NULL,
    "securityStatus"    CHAR(20)            NOT NULL,
    PRIMARY KEY ("characterID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCharactersKillsLastWeek" (
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50) DEFAULT NULL,
    "kills"         BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCharactersKillsTotal" (
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50) DEFAULT NULL,
    "kills"         BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCharactersKillsYesterday" (
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50) DEFAULT NULL,
    "kills"         BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsLastWeek" (
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50) DEFAULT NULL,
    "victoryPoints" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsTotal" (
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50) DEFAULT NULL,
    "victoryPoints" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsYesterday" (
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50) DEFAULT NULL,
    "victoryPoints" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveConquerableStationList" (
    "corporationID"   BIGINT(20) UNSIGNED DEFAULT NULL,
    "corporationName" CHAR(50)            DEFAULT NULL,
    "solarSystemID"   BIGINT(20) UNSIGNED DEFAULT NULL,
    "stationID"       BIGINT(20) UNSIGNED NOT NULL,
    "stationName"     CHAR(255)           DEFAULT NULL,
    "stationTypeID"   BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ("stationID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCorporationsKillsLastWeek" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "kills"           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("corporationID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCorporationsKillsTotal" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "kills"           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("corporationID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCorporationsKillsYesterday" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "kills"           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("corporationID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsLastWeek" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "victoryPoints"   BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("corporationID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsTotal" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "victoryPoints"   BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("corporationID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsYesterday" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "victoryPoints"   BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("corporationID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveEmploymentHistory" (
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "recordID"        BIGINT(20) UNSIGNED NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "startDate"       DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID", "recordID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
#ALTER TABLE "{database}"."{table_prefix}eveEmploymentHistory" ADD INDEX "eveEmploymentHistory1"  ("corporationID");
CREATE TABLE "{database}"."{table_prefix}eveFactions" (
    "factionID"              BIGINT(20) UNSIGNED NOT NULL,
    "factionName"            CHAR(50) DEFAULT NULL,
    "killsYesterday"         BIGINT(20) UNSIGNED NOT NULL,
    "killsLastWeek"          BIGINT(20) UNSIGNED NOT NULL,
    "killsTotal"             BIGINT(20) UNSIGNED NOT NULL,
    "pilots"                 BIGINT(20) UNSIGNED NOT NULL,
    "systemsControlled"      BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsYesterday" BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsLastWeek"  BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsTotal"     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFactionsKillsLastWeek" (
    "factionID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionName" CHAR(50) DEFAULT NULL,
    "kills"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFactionsKillsTotal" (
    "factionID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionName" CHAR(50) DEFAULT NULL,
    "kills"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFactionsKillsYesterday" (
    "factionID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionName" CHAR(50) DEFAULT NULL,
    "kills"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsLastWeek" (
    "factionID"     BIGINT(20) UNSIGNED NOT NULL,
    "factionName"   CHAR(50) DEFAULT NULL,
    "victoryPoints" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsTotal" (
    "factionID"     BIGINT(20) UNSIGNED NOT NULL,
    "factionName"   CHAR(50) DEFAULT NULL,
    "victoryPoints" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsYesterday" (
    "factionID"     BIGINT(20) UNSIGNED NOT NULL,
    "factionName"   CHAR(50) DEFAULT NULL,
    "victoryPoints" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFactionWars" (
    "factionID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionName" CHAR(50) DEFAULT NULL,
    "againstID"   BIGINT(20) UNSIGNED NOT NULL,
    "againstName" CHAR(50) DEFAULT NULL
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveFacWarStats" (
    "killsYesterday"         BIGINT(20) UNSIGNED NOT NULL,
    "killsLastWeek"          BIGINT(20) UNSIGNED NOT NULL,
    "killsTotal"             BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsYesterday" BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsLastWeek"  BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsTotal"     BIGINT(20) UNSIGNED NOT NULL
)
    ENGINE ={engine};
CREATE TABLE "{database}"."{table_prefix}eveMemberCorporations" (
    "allianceID"    BIGINT(20) UNSIGNED NOT NULL,
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "startDate"     DATETIME DEFAULT NULL,
    PRIMARY KEY ("corporationID")
)
    ENGINE ={engine};
CREATE TABLE "{database}"."{table_prefix}eveRefTypes" (
    "refTypeID"   SMALLINT(5) UNSIGNED NOT NULL,
    "refTypeName" VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY ("refTypeID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
CREATE TABLE "{database}"."{table_prefix}eveTypeName" (
    "typeID"   SMALLINT(5) UNSIGNED NOT NULL,
    "typeName" CHAR(255)            NOT NULL,
    PRIMARY KEY ("typeID")
)
    ENGINE ={engine}
    DEFAULT CHARSET =ascii;
