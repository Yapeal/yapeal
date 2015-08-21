CREATE TABLE "{database}"."{table_prefix}eveErrorList" (
    "errorCode" SMALLINT(4) UNSIGNED NOT NULL,
    "errorText" TEXT,
    PRIMARY KEY ("errorCode")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
