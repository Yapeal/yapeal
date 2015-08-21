CREATE TABLE "{database}"."{table_prefix}utilDatabaseVersion" (
    "version" CHAR(12) NOT NULL,
    PRIMARY KEY ("version")
)
ENGINE = { ENGINE}
COLLATE utf8_unicode_ci;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
    ('201507202210');
