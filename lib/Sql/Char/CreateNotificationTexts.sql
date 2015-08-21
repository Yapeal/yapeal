CREATE TABLE "{database}"."{table_prefix}charNotificationTexts" (
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "text"           TEXT,
    PRIMARY KEY ("ownerID","notificationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
