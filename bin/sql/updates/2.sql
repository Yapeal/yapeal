SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}corpFacilities" (
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "facilityID"       BIGINT(20) UNSIGNED NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED NOT NULL,
    "typeName"         CHAR(255)           NOT NULL,
    "solarSystemID"    BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName"  CHAR(255)           NOT NULL,
    "regionID"         BIGINT(20) UNSIGNED NOT NULL,
    "regionName"       CHAR(255)           NOT NULL,
    "starbaseModifier" DECIMAL(17, 2)      NOT NULL,
    "tax"              DECIMAL(17, 2)      NOT NULL,
    PRIMARY KEY ("ownerID", "facilityID")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi"
("sectionName", "apiName", "mask", "interval", "isActive")
VALUES
    ('corp', 'Facilities', 64, 900, 1)
ON DUPLICATE KEY UPDATE
    "sectionName" = VALUES("sectionName"),
    "apiName"     = VALUES("apiName"),
    "mask"        = VALUES("mask"),
    "interval"    = VALUES("interval"),
    "isActive"    = VALUES("isActive");
COMMIT;
