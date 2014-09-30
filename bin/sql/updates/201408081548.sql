CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}utilDatabaseVersion" (
    "version" CHAR(12) NOT NULL,
    PRIMARY KEY ("version")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
