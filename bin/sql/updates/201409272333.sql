-- sql/updates/201409051857.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}eveCharacterInfo',
    'shipName',
    'CHAR(255) COLLATE utf8_unicode_ci DEFAULT \'\'');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201409051857')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409062320.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpAccountBalance',
    'accountKey',
    'SMALLINT(5) UNSIGNED NOT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201409062320')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409102158.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charContactList',
    'inWatchlist',
    'CHAR(5) NOT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201409102158')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409102212.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charWalletJournal',
    'argName1',
    'CHAR(255) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charWalletJournal',
    'ownerName1',
    'CHAR(50) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charWalletJournal',
    'ownerName2',
    'CHAR(50) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpWalletJournal',
    'argName1',
    'CHAR(255) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpWalletJournal',
    'ownerName1',
    'CHAR(50) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpWalletJournal',
    'ownerName2',
    'CHAR(50) COLLATE utf8_unicode_ci');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201409102212')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409111421.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpCorporationSheet',
    'factionName',
    'CHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL AFTER "factionID"');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201409111421')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409131955.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpCorporationSheet',
    'ceoName',
    'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "ceoID"');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201409131955')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409232048.sql
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi" ("sectionName","apiName","mask","interval","isActive")
VALUES
    ('account','YapealCorporationSheet',0,21600,1),
    ('eve','YapealCorporationSheet',0,86400,0)
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
    ('201409232048')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409240835.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charContracts',
    'volume',
    'DECIMAL(18,4) UNSIGNED NOT NULL');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpContracts',
    'volume',
    'DECIMAL(18,4) UNSIGNED NOT NULL');
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201409240835')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201409272333.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charResearch',
    'pointsPerDay',
    'DOUBLE NOT NULL');
