CREATE TABLE "{database}"."{table_prefix}accountAPIKeyInfo" (
    "keyID"      BIGINT(20) UNSIGNED                       NOT NULL,
    "accessMask" BIGINT(20) UNSIGNED                       NOT NULL,
    "expires"    DATETIME                                  NOT NULL DEFAULT '2038-01-19 03:14:07',
    "type"       ENUM('Account','Character','Corporation') NOT NULL,
    PRIMARY KEY ("keyID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountAPIKeyInfo" ADD INDEX "accountAPIKeyInfo1"  ("type");
