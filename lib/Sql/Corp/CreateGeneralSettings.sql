CREATE TABLE "{database}"."{table_prefix}corpGeneralSettings" (
    "ownerID"                 BIGINT(20) UNSIGNED  NOT NULL,
    "itemID"                  BIGINT(20) UNSIGNED  NOT NULL,
    "allowAllianceMembers"    TINYINT(1)           NOT NULL,
    "allowCorporationMembers" TINYINT(1)           NOT NULL,
    "deployFlags"             SMALLINT(5) UNSIGNED NOT NULL,
    "usageFlags"              SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
