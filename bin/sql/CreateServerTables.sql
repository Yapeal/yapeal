DROP TABLE IF EXISTS "{database}"."{table_prefix}serverServerStatus";
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}serverServerStatus" (
    "onlinePlayers" BIGINT(20) UNSIGNED NOT NULL,
    "serverName"    CHAR(24)            NOT NULL,
    "serverOpen"    CHAR(5)             NOT NULL,
    PRIMARY KEY ("serverName")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
