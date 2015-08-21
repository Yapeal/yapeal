CREATE TABLE "{database}"."{table_prefix}corpContainerLog" (
    "ownerID"          BIGINT(20) UNSIGNED  NOT NULL,
    "action"           CHAR(24)             NOT NULL,
    "actorID"          BIGINT(20) UNSIGNED  NOT NULL,
    "actorName"        CHAR(50)             NOT NULL,
    "flag"             SMALLINT(5) UNSIGNED NOT NULL,
    "itemID"           BIGINT(20) UNSIGNED  NOT NULL,
    "itemTypeID"       BIGINT(20) UNSIGNED  NOT NULL,
    "locationID"       BIGINT(20) UNSIGNED  NOT NULL,
    "logTime"          DATETIME             NOT NULL,
    "newConfiguration" SMALLINT(4) UNSIGNED NOT NULL,
    "oldConfiguration" SMALLINT(4) UNSIGNED NOT NULL,
    "passwordType"     CHAR(12)             NOT NULL,
    "quantity"         BIGINT(20) UNSIGNED  NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID","itemID","logTime")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
