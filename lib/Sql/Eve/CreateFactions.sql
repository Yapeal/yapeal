CREATE TABLE "{database}"."{table_prefix}eveFactions" (
    "factionID"              BIGINT(20) UNSIGNED NOT NULL,
    "factionName"            CHAR(50) DEFAULT NULL,
    "killsYesterday"         BIGINT(20) UNSIGNED NOT NULL,
    "killsLastWeek"          BIGINT(20) UNSIGNED NOT NULL,
    "killsTotal"             BIGINT(20) UNSIGNED NOT NULL,
    "pilots"                 BIGINT(20) UNSIGNED NOT NULL,
    "systemsControlled"      BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsYesterday" BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsLastWeek"  BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsTotal"     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("factionID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
