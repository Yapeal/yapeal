CREATE TABLE "{database}"."{table_prefix}charKillMails" (
    "killID"        BIGINT(20) UNSIGNED NOT NULL,
    "killTime"      DATETIME            NOT NULL,
    "moonID"        BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","killTime")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
