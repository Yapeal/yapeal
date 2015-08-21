CREATE TABLE "{database}"."{table_prefix}corpCorporationSheet" (
    "allianceID"      BIGINT(20) UNSIGNED   NOT NULL DEFAULT 0,
    "allianceName"    CHAR(50)                       DEFAULT NULL,
    "ceoID"           BIGINT(20) UNSIGNED   NOT NULL,
    "ceoName"         CHAR(50)              NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED   NOT NULL,
    "corporationName" CHAR(50)              NOT NULL,
    "description"     TEXT,
    "factionID"       BIGINT(20) UNSIGNED   NOT NULL DEFAULT 0,
    "factionName"     CHAR(50)                       DEFAULT NULL,
    "memberCount"     BIGINT(20) UNSIGNED   NOT NULL,
    "memberLimit"     BIGINT(20) UNSIGNED   NOT NULL DEFAULT 0,
    "shares"          BIGINT(20) UNSIGNED   NOT NULL,
    "stationID"       BIGINT(20) UNSIGNED   NOT NULL,
    "stationName"     CHAR(255)             NOT NULL,
    "taxRate"         DECIMAL(5,2) UNSIGNED NOT NULL,
    "ticker"          CHAR(5)               NOT NULL,
    "url"             VARCHAR(255)                   DEFAULT NULL,
    PRIMARY KEY ("corporationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
