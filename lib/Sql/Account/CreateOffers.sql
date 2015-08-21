CREATE TABLE "{database}"."{table_prefix}accountOffers" (
    "from"        CHAR(20)            NOT NULL,
    "ISK"         DECIMAL(17,2)       NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    "offeredDate" DATETIME            NOT NULL,
    "offerID"     BIGINT(20) UNSIGNED NOT NULL,
    "to"          CHAR(20)            NOT NULL,
    PRIMARY KEY ("keyID","offerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
