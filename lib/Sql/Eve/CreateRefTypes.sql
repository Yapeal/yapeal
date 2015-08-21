CREATE TABLE "{database}"."{table_prefix}eveRefTypes" (
    "refTypeID"   SMALLINT(5) UNSIGNED NOT NULL,
    "refTypeName" VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY ("refTypeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
