CREATE TABLE "{database}"."{table_prefix}corpCombatSettings" (
    "ownerID"                 BIGINT(20) UNSIGNED   NOT NULL,
    "itemID"                  BIGINT(20) UNSIGNED   NOT NULL,
    "onAggressionEnabled"     TINYINT(1)            NOT NULL,
    "onCorporationWarEnabled" TINYINT(1)            NOT NULL,
    "onStandingDropStanding"  DECIMAL(5,2) UNSIGNED NOT NULL,
    "onStatusDropEnabled"     TINYINT(1)            NOT NULL,
    "onStatusDropStanding"    DECIMAL(5,2) UNSIGNED NOT NULL,
    "useStandingsFromOwnerID" BIGINT(20) UNSIGNED   NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
