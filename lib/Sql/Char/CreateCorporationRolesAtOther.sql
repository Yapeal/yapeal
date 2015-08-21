CREATE TABLE "{database}"."{table_prefix}charCorporationRolesAtOther" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "roleID"   BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
