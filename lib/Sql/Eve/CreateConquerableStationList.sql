CREATE TABLE "{database}"."{table_prefix}eveConquerableStationList" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)            NOT NULL,
    "solarSystemID"   BIGINT(20) UNSIGNED NOT NULL,
    "stationID"       BIGINT(20) UNSIGNED NOT NULL,
    "stationName"     CHAR(255)           NOT NULL,
    "stationTypeID"   BIGINT(20) UNSIGNED NOT NULL,
    "x"               BIGINT(20)          NOT NULL,
    "y"               BIGINT(20)          NOT NULL,
    "z"               BIGINT(20)          NOT NULL,
    PRIMARY KEY ("stationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
