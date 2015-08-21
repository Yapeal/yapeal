CREATE TABLE "{database}"."{table_prefix}charBlueprints" (
    "ownerID"            BIGINT(20) UNSIGNED NOT NULL,
    "itemID"             BIGINT(20) UNSIGNED NOT NULL,
    "locationID"         BIGINT(20) UNSIGNED NOT NULL,
    "typeID"             BIGINT(20) UNSIGNED NOT NULL,
    "typeName"           CHAR(255)           NOT NULL,
    "flagID"             BIGINT(20) UNSIGNED NOT NULL,
    "quantity"           BIGINT(20)          NOT NULL,
    "timeEfficiency"     TINYINT(3) UNSIGNED NOT NULL,
    "materialEfficiency" TINYINT(3) UNSIGNED NOT NULL,
    "runs"               BIGINT(20)          NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
