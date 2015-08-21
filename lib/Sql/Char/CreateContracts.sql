CREATE TABLE "{database}"."{table_prefix}charContracts" (
    "acceptorID"     BIGINT(20) UNSIGNED    NOT NULL,
    "assigneeID"     BIGINT(20) UNSIGNED    NOT NULL,
    "availability"   CHAR(8)                NOT NULL,
    "buyout"         DECIMAL(17,2)          NOT NULL,
    "collateral"     DECIMAL(17,2)          NOT NULL,
    "contractID"     BIGINT(20) UNSIGNED    NOT NULL,
    "dateAccepted"   DATETIME  DEFAULT NULL,
    "dateCompleted"  DATETIME  DEFAULT NULL,
    "dateExpired"    DATETIME               NOT NULL,
    "dateIssued"     DATETIME               NOT NULL,
    "endStationID"   BIGINT(20) UNSIGNED    NOT NULL,
    "forCorp"        TINYINT(1)             NOT NULL,
    "issuerCorpID"   BIGINT(20) UNSIGNED    NOT NULL,
    "issuerID"       BIGINT(20) UNSIGNED    NOT NULL,
    "numDays"        SMALLINT(3) UNSIGNED   NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED    NOT NULL,
    "price"          DECIMAL(17,2)          NOT NULL,
    "reward"         DECIMAL(17,2)          NOT NULL,
    "startStationID" BIGINT(20) UNSIGNED    NOT NULL,
    "status"         CHAR(24)               NOT NULL,
    "title"          CHAR(255) DEFAULT NULL,
    "type"           CHAR(15)               NOT NULL,
    "volume"         DECIMAL(20,4) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","contractID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
