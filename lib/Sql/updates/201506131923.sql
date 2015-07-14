-- Sql/updates/201506131923.sql
ALTER TABLE "{database}"."{table_prefix}utilEveApi"
 CHANGE COLUMN "isActive" "active" TINYINT(1) NOT NULL FIRST;
ALTER TABLE "{database}"."{table_prefix}utilRegisteredKey"
 CHANGE COLUMN "isActive" "active" TINYINT(1) DEFAULT NULL FIRST;
ALTER TABLE "{database}"."{table_prefix}utilRegisteredUploader"
 CHANGE COLUMN "isActive" "active" TINYINT(1) DEFAULT NULL FIRST;
ALTER TABLE "{database}"."{table_prefix}utilUploadDestination"
 CHANGE COLUMN "isActive" "active" TINYINT(1) DEFAULT NULL FIRST;
