CREATE TABLE "{database}"."{table_prefix}corpMemberTracking" (
    "base"           CHAR(255)           DEFAULT NULL,
    "baseID"         BIGINT(20) UNSIGNED DEFAULT NULL,
    "characterID"    BIGINT(20) UNSIGNED NOT NULL,
    "grantableRoles" CHAR(64)            DEFAULT NULL,
    "location"       CHAR(255)           DEFAULT NULL,
    "locationID"     BIGINT(20) UNSIGNED DEFAULT 0,
    "logoffDateTime" DATETIME            DEFAULT NULL,
    "logonDateTime"  DATETIME            DEFAULT NULL,
    "name"           CHAR(50)            NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "roles"          CHAR(64)            DEFAULT NULL,
    "shipType"       CHAR(50)            DEFAULT NULL,
    "shipTypeID"     BIGINT(20)          DEFAULT 0,
    "startDateTime"  DATETIME            NOT NULL,
    "title"          TEXT,
    PRIMARY KEY ("characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMemberTracking" ADD INDEX "corpMemberTracking1"  ("ownerID");
