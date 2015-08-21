CREATE TABLE "{database}"."{table_prefix}charWalletTransactions" (
    "ownerID"              BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"           SMALLINT(4) UNSIGNED NOT NULL,
    "clientID"             BIGINT(20) UNSIGNED           DEFAULT NULL,
    "clientName"           CHAR(50)                      DEFAULT NULL,
    "clientTypeID"         BIGINT(20) UNSIGNED           DEFAULT NULL,
    "journalTransactionID" BIGINT(20) UNSIGNED  NOT NULL,
    "price"                DECIMAL(17,2)        NOT NULL,
    "quantity"             BIGINT(20) UNSIGNED  NOT NULL,
    "stationID"            BIGINT(20) UNSIGNED           DEFAULT NULL,
    "stationName"          CHAR(255)                     DEFAULT NULL,
    "transactionDateTime"  DATETIME             NOT NULL,
    "transactionFor"       CHAR(12)             NOT NULL DEFAULT 'corporation',
    "transactionID"        BIGINT(20) UNSIGNED  NOT NULL,
    "transactionType"      CHAR(4)              NOT NULL DEFAULT 'sell',
    "typeID"               BIGINT(20) UNSIGNED  NOT NULL,
    "typeName"             CHAR(255)            NOT NULL,
    PRIMARY KEY ("ownerID","transactionID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
