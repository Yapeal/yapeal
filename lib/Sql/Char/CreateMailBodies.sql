CREATE TABLE "{database}"."{table_prefix}charMailBodies" (
    "ownerID"   BIGINT(20) UNSIGNED NOT NULL,
    "body"      TEXT,
    "messageID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","messageID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
