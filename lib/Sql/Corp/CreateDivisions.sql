CREATE TABLE "{database}"."{table_prefix}corpDivisions" (
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"  SMALLINT(4) UNSIGNED NOT NULL,
    "description" VARCHAR(255)         NOT NULL,
    PRIMARY KEY ("ownerID","accountKey")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
