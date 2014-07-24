SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
DROP TABLE IF EXISTS "{database}"."{table_prefix}apiCallGroups";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}apiCallGroups" (
    "description" TEXT                NOT NULL,
    "groupID"     BIGINT(20) UNSIGNED NOT NULL,
    "name"        CHAR(24)            NOT NULL,
    PRIMARY KEY ("groupID")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
DROP TABLE IF EXISTS "{database}"."{table_prefix}apiCalls";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}apiCalls" (
    "accessMask"  BIGINT(20) UNSIGNED              NOT NULL,
    "description" TEXT                             NOT NULL,
    "groupID"     BIGINT(20) UNSIGNED              NOT NULL,
    "name"        CHAR(24)                         NOT NULL,
    "type"        ENUM('Character', 'Corporation') NOT NULL,
    PRIMARY KEY ("accessMask", "type")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
