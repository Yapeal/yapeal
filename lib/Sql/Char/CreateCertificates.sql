CREATE TABLE "{database}"."{table_prefix}charCertificates" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "certificateID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","certificateID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
