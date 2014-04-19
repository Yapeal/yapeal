SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
ALTER TABLE `{table_prefix}accountCharacters` ADD COLUMN `factionID` BIGINT(20) UNSIGNED DEFAULT '0';
ALTER TABLE `{table_prefix}accountCharacters` ADD COLUMN `factionName` VARCHAR(255)
COLLATE utf8_unicode_ci DEFAULT '';
ALTER TABLE `{table_prefix}charMailMessages` ADD COLUMN `senderName` VARCHAR(255)
COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `{table_prefix}mapFacWarSystems` ADD COLUMN `victoryPoints` BIGINT(20) UNSIGNED NOT NULL;
ALTER TABLE `{table_prefix}mapFacWarSystems` ADD COLUMN `victoryPointThreshold` BIGINT(20) UNSIGNED NOT NULL;
