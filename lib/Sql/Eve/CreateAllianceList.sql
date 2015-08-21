CREATE TABLE "{database}"."{table_prefix}eveAllianceList" (
    "allianceID"     BIGINT(20) UNSIGNED NOT NULL,
    "executorCorpID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "memberCount"    BIGINT(20) UNSIGNED DEFAULT NULL,
    "name"           CHAR(50)            DEFAULT NULL,
    "shortName"      CHAR(5)             DEFAULT NULL,
    "startDate"      DATETIME            DEFAULT NULL,
    PRIMARY KEY ("allianceID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
