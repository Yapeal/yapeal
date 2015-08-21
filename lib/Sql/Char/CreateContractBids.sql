CREATE TABLE "{database}"."{table_prefix}charContractBids" (
    "amount"     DECIMAL(17,2)       NOT NULL,
    "bidID"      BIGINT(20) UNSIGNED NOT NULL,
    "bidderID"   BIGINT(20) UNSIGNED NOT NULL,
    "contractID" BIGINT(20) UNSIGNED NOT NULL,
    "dateBid"    DATETIME            NOT NULL,
    PRIMARY KEY ("contractID","bidID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
