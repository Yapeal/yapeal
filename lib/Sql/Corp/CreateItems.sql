CREATE TABLE "{database}"."{table_prefix}corpItems" (
    "flag"         SMALLINT(5) UNSIGNED NOT NULL,
    "killID"       BIGINT(20) UNSIGNED  NOT NULL,
    "lft"          BIGINT(20) UNSIGNED  NOT NULL,
    "lvl"          TINYINT(2) UNSIGNED  NOT NULL,
    "rgt"          BIGINT(20) UNSIGNED  NOT NULL,
    "qtyDropped"   BIGINT(20) UNSIGNED  NOT NULL,
    "qtyDestroyed" BIGINT(20) UNSIGNED  NOT NULL,
    "singleton"    SMALLINT(5) UNSIGNED NOT NULL,
    "typeID"       BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("killID","lft")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
