-- sql/updates/201408081548.sql
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}utilDatabaseVersion" (
    "version" CHAR(12) NOT NULL,
    PRIMARY KEY ("version")
)
ENGINE ={ engine}
DEFAULT CHARSET =ascii;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408081548')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201408091304.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpWalletTransactions',
    'stationName',
    'CHAR(255) NULL DEFAULT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408091304')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201408111246.sql
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}corpFacilities" (
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "facilityID"       BIGINT(20) UNSIGNED NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED NOT NULL,
    "typeName"         CHAR(255)           NOT NULL,
    "solarSystemID"    BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName"  CHAR(255)           NOT NULL,
    "regionID"         BIGINT(20) UNSIGNED NOT NULL,
    "regionName"       CHAR(255)           NOT NULL,
    "starbaseModifier" DECIMAL(17,2)       NOT NULL,
    "tax"              DECIMAL(17,2)       NOT NULL,
    PRIMARY KEY ("ownerID","facilityID")
)
ENGINE ={ engine}
DEFAULT CHARSET =ascii;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi" ("sectionName","apiName","mask","interval","isActive")
VALUES
    ('corp','Facilities',64,900,1)
ON DUPLICATE KEY UPDATE
    "sectionName" = VALUES("sectionName"),
    "apiName"     = VALUES("apiName"),
    "mask"        = VALUES("mask"),
    "interval"    = VALUES("interval"),
    "isActive"    = VALUES("isActive");
COMMIT;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408111246')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201408131139.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}accountCharacters',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charAttackers',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charCalendarEventAttendees',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charCharacterSheet',
    'name',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charStandingsFromAgents',
    'fromName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charVictim',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpAttackers',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpCalendarEventAttendees',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpContainerLog',
    'actorName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpCorporationSheet',
    'stationName',
    'CHAR(255) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpStandingsFromAgents',
    'fromName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpVictim',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpWalletTransactions',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharacterInfo',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharactersKillsLastWeek',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharactersKillsTotal',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharactersKillsYesterday',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharactersVictoryPointsLastWeek',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharactersVictoryPointsTotal',
    'characterName',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharactersVictoryPointsYesterday',
    'characterName',
    'CHAR(50) NOT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408131139')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201408151310.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charWalletTransactions',
    'typeName',
    'CHAR(255) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpWalletTransactions',
    'typeName',
    'CHAR(255) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveTypeName',
    'typeName',
    'CHAR(255) NOT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408151310')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201408181020.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveEmploymentHistory',
    'corporationName',
    'CHAR(50) NULL DEFAULT NULL AFTER "corporationID"');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveTypeName',
    'typeName',
    'CHAR(255) NOT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408181020')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201408261547.sql
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi" ("sectionName","apiName","mask","interval","isActive")
VALUES
    ('char','Blueprints',2,86400,1),
    ('corp','Blueprints',2,86400,1)
ON DUPLICATE KEY UPDATE
    "sectionName" = VALUES("sectionName"),
    "apiName"     = VALUES("apiName"),
    "mask"        = VALUES("mask"),
    "interval"    = VALUES("interval"),
    "isActive"    = VALUES("isActive");
COMMIT;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408261547')
ON DUPLICATE KEY UPDATE
    "version" = VALUES("version");
COMMIT;
-- sql/updates/201408271619.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charCorporationTitles',
    'titleName',
    'CHAR(50) COLLATE utf8_unicode_ci NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charMailMessages',
    'title',
    'CHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charContracts',
    'title',
    'CHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408271619')
ON DUPLICATE KEY UPDATE
    "version" = VALUES("version");
COMMIT;
-- sql/updates/201408281719.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charIndustryJobs',
    'installerName',
    'CHAR(50) DEFAULT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpIndustryJobs',
    'installerName',
    'CHAR(50) DEFAULT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201408281719')
ON DUPLICATE KEY UPDATE
    "version" = VALUES("version");
COMMIT;
-- sql/updates/201408281814.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpMemberTracking',
    'locationID',
    'BIGINT(20) UNSIGNED DEFAULT 0');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpMemberTracking',
    'name',
    'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpMemberTracking',
    'shipTypeID',
    'BIGINT(20) DEFAULT 0');
