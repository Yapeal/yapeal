CREATE TABLE "{database}"."{table_prefix}charSkillQueue" (
    "endSP"         BIGINT(20) UNSIGNED NOT NULL,
    "endTime"       DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "level"         TINYINT(1) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "queuePosition" TINYINT(2) UNSIGNED NOT NULL,
    "startSP"       BIGINT(20) UNSIGNED NOT NULL,
    "startTime"     DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "typeID"        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","queuePosition")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
