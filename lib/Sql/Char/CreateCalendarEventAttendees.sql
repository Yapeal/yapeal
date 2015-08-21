CREATE TABLE "{database}"."{table_prefix}charCalendarEventAttendees" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50)            NOT NULL,
    "response"      CHAR(10)            NOT NULL,
    PRIMARY KEY ("ownerID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
