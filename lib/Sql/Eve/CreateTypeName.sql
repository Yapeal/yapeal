CREATE TABLE "{database}"."{table_prefix}eveTypeName" (
    "typeID"   SMALLINT(5) UNSIGNED NOT NULL,
    "typeName" CHAR(255)            NOT NULL,
    PRIMARY KEY ("typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
