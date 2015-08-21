CREATE TABLE "{database}"."{table_prefix}charSkills" (
    "level"       TINYINT(1) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "skillpoints" BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "published"   TINYINT(1)          NOT NULL,
    PRIMARY KEY ("ownerID","typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
