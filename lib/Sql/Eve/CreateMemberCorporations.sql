CREATE TABLE "{database}"."{table_prefix}eveMemberCorporations" (
    "allianceID"    BIGINT(20) UNSIGNED NOT NULL,
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "startDate"     DATETIME DEFAULT NULL,
    PRIMARY KEY ("corporationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
