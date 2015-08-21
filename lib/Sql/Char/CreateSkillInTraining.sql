CREATE TABLE "{database}"."{table_prefix}charSkillInTraining" (
    "currentTQTime"         DATETIME                     DEFAULT NULL,
    "offset"                TINYINT(2)          NOT NULL,
    "ownerID"               BIGINT(20) UNSIGNED NOT NULL,
    "skillInTraining"       TINYINT(1) UNSIGNED NOT NULL,
    "trainingDestinationSP" BIGINT(20) UNSIGNED NOT NULL,
    "trainingEndTime"       DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "trainingStartSP"       BIGINT(20) UNSIGNED NOT NULL,
    "trainingStartTime"     DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "trainingToLevel"       TINYINT(1) UNSIGNED NOT NULL,
    "trainingTypeID"        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
