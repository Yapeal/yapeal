CREATE TABLE "{database}"."{table_prefix}accountCharacters" (
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)            NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)            NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50)            NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50)            NOT NULL,
    PRIMARY KEY ("characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountCharacters" ADD INDEX "accountCharacters1"  ("corporationID");
