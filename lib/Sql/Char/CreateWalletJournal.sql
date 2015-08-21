CREATE TABLE "{database}"."{table_prefix}charWalletJournal" (
    "ownerID"       BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"    SMALLINT(4) UNSIGNED NOT NULL,
    "amount"        DECIMAL(17,2)        NOT NULL,
    "argID1"        BIGINT(20) UNSIGNED DEFAULT NULL,
    "argName1"      CHAR(255)           DEFAULT NULL,
    "balance"       DECIMAL(17,2)        NOT NULL,
    "date"          DATETIME             NOT NULL,
    "ownerID1"      BIGINT(20) UNSIGNED DEFAULT NULL,
    "ownerID2"      BIGINT(20) UNSIGNED DEFAULT NULL,
    "ownerName1"    CHAR(50)            DEFAULT NULL,
    "ownerName2"    CHAR(50)            DEFAULT NULL,
    "reason"        TEXT,
    "refID"         BIGINT(20) UNSIGNED  NOT NULL,
    "refTypeID"     SMALLINT(5) UNSIGNED NOT NULL,
    "taxAmount"     DECIMAL(17,2)        NOT NULL,
    "taxReceiverID" BIGINT(20) UNSIGNED DEFAULT '0',
    "owner1TypeID"  BIGINT(20) UNSIGNED DEFAULT NULL,
    "owner2TypeID"  BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ("ownerID","refID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
