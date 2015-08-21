CREATE TABLE "{database}"."{table_prefix}charAttributes" (
    "charisma"     TINYINT(2) UNSIGNED NOT NULL,
    "intelligence" TINYINT(2) UNSIGNED NOT NULL,
    "memory"       TINYINT(2) UNSIGNED NOT NULL,
    "ownerID"      BIGINT(20) UNSIGNED NOT NULL,
    "perception"   TINYINT(2) UNSIGNED NOT NULL,
    "willpower"    TINYINT(2) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
