CREATE TABLE "{database}"."{table_prefix}utilXmlCache" (
    "apiName"     CHAR(32)  NOT NULL,
    "hash"        CHAR(40)  NOT NULL,
    "modified"    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    "sectionName" CHAR(8)   NOT NULL,
    "xml"         LONGTEXT,
    PRIMARY KEY ("hash")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilXmlCache" ADD INDEX "utilXmlCache1" ("sectionName");
ALTER TABLE "{database}"."{table_prefix}utilXmlCache" ADD INDEX "utilXmlCache2" ("apiName");
