CREATE TABLE "{database}"."{table_prefix}charNotifications" (
    "ownerID"        BIGINT(20) UNSIGNED  NOT NULL,
    "notificationID" BIGINT(20) UNSIGNED  NOT NULL,
    "read"           TINYINT(1)           NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED  NOT NULL,
    "senderName"     CHAR(50) DEFAULT NULL,
    "sentDate"       DATETIME             NOT NULL,
    "typeID"         SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","notificationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
