CREATE TABLE "{database}"."{table_prefix}corpAccountBalance" (
    "ownerID"    BIGINT(20) UNSIGNED  NOT NULL,
    "accountID"  BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey" SMALLINT(5) UNSIGNED NOT NULL,
    "balance"    DECIMAL(17,2)        NOT NULL,
    PRIMARY KEY ("ownerID","accountKey")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
