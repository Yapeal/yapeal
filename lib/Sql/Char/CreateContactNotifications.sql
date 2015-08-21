CREATE TABLE "{database}"."{table_prefix}charContactNotifications" (
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED NOT NULL,
    "senderName"     CHAR(50)            NOT NULL,
    "sentDate"       DATETIME            NOT NULL,
    "messageData"    TEXT,
    PRIMARY KEY ("ownerID","notificationID","senderID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
