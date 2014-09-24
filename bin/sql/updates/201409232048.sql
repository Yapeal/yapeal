SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi"
("sectionName", "apiName", "mask", "interval", "isActive")
VALUES
    ('account', 'YapealCorporationSheet', 0, 21600, 1),
    ('eve', 'YapealCorporationSheet', 0, 86400, 0)
ON DUPLICATE KEY UPDATE
    "sectionName" = VALUES("sectionName"),
    "apiName"     = VALUES("apiName"),
    "mask"        = VALUES("mask"),
    "interval"    = VALUES("interval"),
    "isActive"    = VALUES("isActive");
COMMIT;
