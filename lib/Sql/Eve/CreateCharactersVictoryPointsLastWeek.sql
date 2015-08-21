CREATE TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsLastWeek" (
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50) DEFAULT NULL,
    "victoryPoints" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
