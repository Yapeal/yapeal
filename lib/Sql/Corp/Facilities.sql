-- sql/updates/201508142225.sql
CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}corpFacilities" (
    ""        VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
