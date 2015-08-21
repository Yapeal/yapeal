CREATE TABLE "{database}"."{table_prefix}corpLogo" (
    "ownerID"   BIGINT(20) UNSIGNED  NOT NULL,
    "color1"    SMALLINT(5) UNSIGNED NOT NULL,
    "color2"    SMALLINT(5) UNSIGNED NOT NULL,
    "color3"    SMALLINT(5) UNSIGNED NOT NULL,
    "graphicID" BIGINT(20) UNSIGNED  NOT NULL,
    "shape1"    SMALLINT(5) UNSIGNED NOT NULL,
    "shape2"    SMALLINT(5) UNSIGNED NOT NULL,
    "shape3"    SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","graphicID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
