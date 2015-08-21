CREATE TABLE "{database}"."{table_prefix}eveEmploymentHistory" (
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "recordID"        BIGINT(20) UNSIGNED NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "startDate"       DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID","recordID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
#ALTER TABLE "{database}"."{table_prefix}eveEmploymentHistory" ADD INDEX "eveEmploymentHistory1"  ("corporationID");
