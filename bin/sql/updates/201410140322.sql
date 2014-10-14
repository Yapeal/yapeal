START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilEveApi"
("sectionName", "apiName", "mask", "interval", "isActive")
VALUES
    ('eve', 'CharacterInfo', 0, 3600, 0)
ON DUPLICATE KEY UPDATE
    "sectionName" = VALUES("sectionName"),
    "apiName"     = VALUES("apiName"),
    "mask"        = VALUES("mask"),
    "interval"    = VALUES("interval"),
    "isActive"    = VALUES("isActive");
COMMIT;
