CREATE TABLE "{database}"."{table_prefix}eveCharacterInfo" (
    "accountBalance"    DECIMAL(17,2)       NOT NULL,
    "alliance"          CHAR(50)            DEFAULT '',
    "allianceDate"      DATETIME            NOT NULL,
    "allianceID"        BIGINT(20) UNSIGNED DEFAULT 0,
    "ancestry"          CHAR(24)            NOT NULL,
    "ancestryID"        BIGINT(20) UNSIGNED NOT NULL,
    "bloodline"         CHAR(24)            NOT NULL,
    "bloodlineID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterName"     CHAR(50)            NOT NULL,
    "corporation"       CHAR(50)            NOT NULL,
    "corporationDate"   DATETIME            NOT NULL,
    "corporationID"     BIGINT(20) UNSIGNED NOT NULL,
    "lastKnownLocation" CHAR(255)           NOT NULL,
    "nextTrainingEnds"  DATETIME            NOT NULL,
    "race"              CHAR(8)             NOT NULL,
    "securityStatus"    CHAR(20)            NOT NULL,
    "shipName"          CHAR(255)           DEFAULT '',
    "shipTypeID"        BIGINT(20) UNSIGNED DEFAULT 0,
    "shipTypeName"      CHAR(50)            DEFAULT '',
    "skillPoints"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
