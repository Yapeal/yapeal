CREATE TABLE "{database}"."{table_prefix}charResearch" (
    "ownerID"           BIGINT(20) UNSIGNED NOT NULL,
    "agentID"           BIGINT(20) UNSIGNED NOT NULL,
    "pointsPerDay"      DOUBLE              NOT NULL,
    "skillTypeID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    "remainderPoints"   DOUBLE              NOT NULL,
    "researchStartDate" DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID","agentID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
