SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi" ("sectionName", "apiName", "mask", "interval", "isActive")
VALUES
    ('char', 'Blueprints', 2, 86400, 1),
    ('corp', 'Blueprints', 2, 86400, 1)
ON DUPLICATE KEY UPDATE
    "sectionName" = VALUES("sectionName"),
    "apiName"     = VALUES("apiName"),
    "mask"        = VALUES("mask"),
    "interval"    = VALUES("interval"),
    "isActive"    = VALUES("isActive");
COMMIT;
