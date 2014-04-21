SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `{database}`.`{table_prefix}accountAccountStatus`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}accountAccountStatus` (
    `keyID`        BIGINT(20) UNSIGNED NOT NULL,
    `createDate`   DATETIME            NOT NULL,
    `logonCount`   BIGINT(20) UNSIGNED NOT NULL,
    `logonMinutes` BIGINT(20) UNSIGNED NOT NULL,
    `paidUntil`    DATETIME            NOT NULL,
    PRIMARY KEY (`keyID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}accountAPIKeyInfo`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}accountAPIKeyInfo` (
    `keyID`      BIGINT(20) UNSIGNED NOT NULL,
    `accessMask` BIGINT(20) UNSIGNED NOT NULL,
    `expires`    DATETIME            NOT NULL DEFAULT '2038-01-19 03:14:07',
    `type`       ENUM('Account', 'Character', 'Corporation')
                 CHARACTER SET ascii NOT NULL,
    PRIMARY KEY (`keyID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
ALTER TABLE `{database}`.`{table_prefix}accountAPIKeyInfo` ADD INDEX `accountAPIKeyInfo1`  (`type`);
DROP TABLE IF EXISTS `{database}`.`{table_prefix}accountCharacters`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}accountCharacters` (
    `characterID`     BIGINT(20) UNSIGNED     NOT NULL,
    `characterName`   VARCHAR(255)
                      COLLATE utf8_unicode_ci NOT NULL,
    `corporationID`   BIGINT(20) UNSIGNED     NOT NULL,
    `corporationName` VARCHAR(255)
                      COLLATE utf8_unicode_ci NOT NULL,
    `allianceID`      BIGINT(20) UNSIGNED     NOT NULL,
    `allianceName`    VARCHAR(255)
                      COLLATE utf8_unicode_ci NOT NULL,
    `factionID`       BIGINT(20) UNSIGNED     NOT NULL,
    `factionName`     VARCHAR(255)
                      COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
ALTER TABLE `{database}`.`{table_prefix}accountCharacters` ADD INDEX `accountCharacters1`  (`corporationID`);
DROP TABLE IF EXISTS `{database}`.`{table_prefix}accountKeyBridge`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}accountKeyBridge` (
    `keyID`       BIGINT(20) UNSIGNED NOT NULL,
    `characterID` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`keyID`, `characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
ALTER TABLE `{database}`.`{table_prefix}accountKeyBridge` ADD UNIQUE INDEX `accountKeyBridge1`  (`characterID`, `keyID`);
