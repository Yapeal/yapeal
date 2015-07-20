-- Sql/updates/201507202210.sql
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'ancestryID',
                                      'BIGINT(20) NOT NULL AFTER "ancestry"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'bloodLineID',
                                      'BIGINT(20) NOT NULL AFTER "bloodLine"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharacterInfo',
                                      'ancestry',
                                      'CHAR(24) NOT NULL AFTER "allianceID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharacterInfo',
                                      'ancestryID',
                                      'BIGINT(20) NOT NULL AFTER "ancestry"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharacterInfo',
                                      'bloodline',
                                      'CHAR(24) NOT NULL AFTER "ancestryID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharacterInfo',
                                      'bloodlineID',
                                      'BIGINT(20) NOT NULL AFTER "bloodline"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charAllianceContactList',
                                      'labelMask',
                                      'BIGINT(20) NOT NULL AFTER "contactTypeID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpAllianceContactList',
                                      'labelMask',
                                      'BIGINT(20) NOT NULL AFTER "contactTypeID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charContactList',
                                      'labelMask',
                                      'BIGINT(20) NOT NULL AFTER "inWatchlist"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCorporateContactList',
                                      'labelMask',
                                      'BIGINT(20) NOT NULL AFTER "contactTypeID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporateContactList',
                                      'labelMask',
                                      'BIGINT(20) NOT NULL AFTER "contactTypeID"');
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}charAllianceContactLabels" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(255)           NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}charContactLabels" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(255)           NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}charCorporateContactLabels" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(255)           NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}corpAllianceContactLabels" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(255)           NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}corpCorporateContactLabels" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(255)           NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
