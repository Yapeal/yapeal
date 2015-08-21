CREATE TABLE "{database}"."{table_prefix}apiCallGroups" (
    "description" TEXT                NOT NULL,
    "groupID"     BIGINT(20) UNSIGNED NOT NULL,
    "name"        CHAR(24)            NOT NULL,
    PRIMARY KEY ("groupID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
