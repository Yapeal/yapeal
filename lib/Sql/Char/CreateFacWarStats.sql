CREATE TABLE "{database}"."{table_prefix}charFacWarStats" (
    "ownerID"                BIGINT(20) UNSIGNED NOT NULL,
    "factionID"              BIGINT(20) UNSIGNED NOT NULL,
    "factionName"            CHAR(50)            NOT NULL,
    "enlisted"               DATETIME            NOT NULL,
    "currentRank"            BIGINT(20) UNSIGNED NOT NULL,
    "highestRank"            BIGINT(20) UNSIGNED NOT NULL,
    "killsYesterday"         BIGINT(20) UNSIGNED NOT NULL,
    "killsLastWeek"          BIGINT(20) UNSIGNED NOT NULL,
    "killsTotal"             BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsYesterday" BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsLastWeek"  BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsTotal"     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charFacWarStats" ADD INDEX "charFacWarStats1"  ("factionID");
