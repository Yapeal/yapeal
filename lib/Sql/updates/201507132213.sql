-- sql/updates/201507132213.sql
DROP TABLE IF EXISTS "{database}"."{table_prefix}corpOutpostList";
CREATE TABLE "{database}"."{table_prefix}corpOutpostList" (
    "dockingCostPerShipVolume" DECIMAL(17,2) UNSIGNED  NOT NULL,
    "officeRentalCost"         DECIMAL(17,2) UNSIGNED  NOT NULL,
    "ownerID"                  BIGINT(20) UNSIGNED     NOT NULL,
    "reprocessingEfficiency"   DECIMAL(17,16) UNSIGNED NOT NULL,
    "reprocessingStationTake"  DECIMAL(17,16) UNSIGNED NOT NULL,
    "solarSystemID"            BIGINT(20) UNSIGNED     NOT NULL,
    "standingOwnerID"          BIGINT(20) UNSIGNED     NOT NULL,
    "stationID"                BIGINT(20) UNSIGNED     NOT NULL,
    "stationName"              CHAR(255)               NOT NULL,
    "stationTypeID"            BIGINT(20) UNSIGNED     NOT NULL,
    "x"                        BIGINT(20)              NOT NULL,
    "y"                        BIGINT(20)              NOT NULL,
    "z"                        BIGINT(20)              NOT NULL,
    PRIMARY KEY ("ownerID","stationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
