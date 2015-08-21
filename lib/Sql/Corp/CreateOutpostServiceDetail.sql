CREATE TABLE "{database}"."{table_prefix}corpOutpostServiceDetail" (
    "ownerID"                 BIGINT(20) UNSIGNED   NOT NULL,
    "stationID"               BIGINT(20) UNSIGNED   NOT NULL,
    "discountPerGoodStanding" DECIMAL(5,2)          NOT NULL,
    "minStanding"             DECIMAL(5,2) UNSIGNED NOT NULL,
    "serviceName"             CHAR(50)              NOT NULL,
    "surchargePerBadStanding" DECIMAL(5,2)          NOT NULL,
    PRIMARY KEY ("ownerID","stationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
