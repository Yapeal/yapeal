CREATE TABLE "{database}"."{table_prefix}mapJumps" (
    "shipJumps"     BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("solarSystemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
