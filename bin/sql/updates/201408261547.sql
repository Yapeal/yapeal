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
