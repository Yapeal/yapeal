CREATE TABLE "{database}"."{table_prefix}accountAccountStatus" (
    "keyID"        BIGINT(20) UNSIGNED NOT NULL,
    "createDate"   DATETIME            NOT NULL,
    "logonCount"   BIGINT(20) UNSIGNED NOT NULL,
    "logonMinutes" BIGINT(20) UNSIGNED NOT NULL,
    "paidUntil"    DATETIME            NOT NULL,
    PRIMARY KEY ("keyID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
