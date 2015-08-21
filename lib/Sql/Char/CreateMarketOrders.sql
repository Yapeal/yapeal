CREATE TABLE "{database}"."{table_prefix}charMarketOrders" (
    "ownerID"      BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"   SMALLINT(4) UNSIGNED NOT NULL,
    "bid"          TINYINT(1)           NOT NULL,
    "charID"       BIGINT(20) UNSIGNED  NOT NULL,
    "duration"     SMALLINT(3) UNSIGNED NOT NULL,
    "escrow"       DECIMAL(17,2)        NOT NULL,
    "issued"       DATETIME             NOT NULL,
    "minVolume"    BIGINT(20) UNSIGNED  NOT NULL,
    "orderID"      BIGINT(20) UNSIGNED  NOT NULL,
    "orderState"   TINYINT(2) UNSIGNED  NOT NULL,
    "price"        DECIMAL(17,2)        NOT NULL,
    "range"        SMALLINT(6)          NOT NULL,
    "stationID"    BIGINT(20) UNSIGNED DEFAULT NULL,
    "typeID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    "volEntered"   BIGINT(20) UNSIGNED  NOT NULL,
    "volRemaining" BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID","orderID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
