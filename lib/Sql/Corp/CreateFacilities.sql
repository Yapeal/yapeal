CREATE TABLE "{database}"."{table_prefix}corpFacilities" (
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "facilityID"       BIGINT(20) UNSIGNED NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED NOT NULL,
    "typeName"         CHAR(255)           NOT NULL,
    "solarSystemID"    BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName"  CHAR(255)           NOT NULL,
    "regionID"         BIGINT(20) UNSIGNED NOT NULL,
    "regionName"       CHAR(255)           NOT NULL,
    "starbaseModifier" DECIMAL(17,2)       NOT NULL,
    "tax"              DECIMAL(17,2)       NOT NULL,
    PRIMARY KEY ("ownerID","facilityID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
