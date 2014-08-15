SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}utilDatabaseVersion" (
    "version" CHAR(4) NOT NULL,
    PRIMARY KEY ("version")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('0000')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
