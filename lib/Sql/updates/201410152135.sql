-- sql/updates/201410140322.sql
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi" ("sectionName","apiName","mask","interval","isActive")
VALUES
    ('eve','CharacterInfo',0,3600,0)
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
    ('201410140322')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201410151303.sql
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi" ("sectionName","apiName","mask","interval","isActive")
VALUES
    ('char','Blueprints',2,43200,1),
    ('corp','Blueprints',2,43200,1)
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
    ('201410151303')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201410151445.sql
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}accountMultiCharacterTraining" (
    "trainingEnd" DATETIME            NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID","trainingEnd")
)
ENGINE ={ engine}
DEFAULT CHARSET =ascii;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201410151445')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
-- sql/updates/201410152135.sql
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}charIndustryJobs',
    'successfulRuns',
    'BIGINT(20) UNSIGNED DEFAULT 0 AFTER "status"');
CALL "{database}"."AddOrModifyColumn"(
    '{database}',
    '{table_prefix}corpIndustryJobs',
    'successfulRuns',
    'BIGINT(20) UNSIGNED DEFAULT 0 AFTER "status"');
