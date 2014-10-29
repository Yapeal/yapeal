DROP TABLE IF EXISTS "{database}"."{table_prefix}charAttributeEnhancers";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}charImplants" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "typeID"   BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "typeID")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}charJumpCloneImplants" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "typeName"    CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "jumpCloneID", "typeID")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}charJumpClones" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "locationID"  BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "cloneName"   CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID", "jumpCloneID")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'cloneJumpDate',
                                      'DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:01\' AFTER "characterID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'cloneTypeID',
                                      'BIGINT(20) UNSIGNED NOT NULL AFTER "cloneSkillPoints"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'DoB',
                                      'DATETIME NOT NULL AFTER "corporationName"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'freeRespecs',
                                      'INT(4) UNSIGNED NOT NULL DEFAULT 0 AFTER "factionName"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'freeSkillPoints',
                                      'BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER "freeRespecs"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'homeStationID',
                                      'BIGINT(20) UNSIGNED NOT NULL AFTER "gender"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'jumpActivation',
                                      'DATETIME NOT NULL AFTER "homeStationID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'jumpFatigue',
                                      'DATETIME NOT NULL AFTER "jumpActivation"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'jumpLastUpdate',
                                      'DATETIME NOT NULL AFTER "jumpFatigue"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'lastRespecDate',
                                      'DATETIME NOT NULL AFTER "jumpLastUpdate"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'lastTimedRespec',
                                      'DATETIME NOT NULL AFTER "lastRespecDate"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'remoteStationDate',
                                      'DATETIME NOT NULL AFTER "race"');
