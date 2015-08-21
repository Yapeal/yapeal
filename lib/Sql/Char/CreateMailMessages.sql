CREATE TABLE "{database}"."{table_prefix}charMailMessages" (
    "ownerID"            BIGINT(20) UNSIGNED NOT NULL,
    "messageID"          BIGINT(20) UNSIGNED NOT NULL,
    "senderID"           BIGINT(20) UNSIGNED NOT NULL,
    "senderName"         CHAR(50)            DEFAULT NULL,
    "sentDate"           DATETIME            NOT NULL,
    "title"              CHAR(255)           DEFAULT NULL,
    "toCharacterIDs"     TEXT,
    "toCorpOrAllianceID" BIGINT(20) UNSIGNED DEFAULT '0',
    "toListID"           TEXT,
    "senderTypeID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ("ownerID","messageID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
