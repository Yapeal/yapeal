SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilAccessMask`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilAccessMask` (
    `section`     VARCHAR(8)          NOT NULL,
    `api`         VARCHAR(32)         NOT NULL,
    `description` TEXT,
    `mask`        BIGINT(20) UNSIGNED NOT NULL,
    `status`      TINYINT(3) UNSIGNED NOT NULL,
    PRIMARY KEY (`section`, `api`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
INSERT INTO `{database}`.`{table_prefix}utilAccessMask` (`section`, `api`, `description`, `mask`, `status`)
VALUES
    ('account', 'AccountStatus', 'EVE player account status.', 33554432, 16),
    ('account', 'APIKeyInfo', 'Used to get information about a keyID', 1, 16),
    ('char', 'AccountBalance', 'Current balance of characters wallet.', 1, 16),
    ('char', 'AssetList', 'Entire asset list of character.', 2, 16),
    ('char', 'CalendarEventAttendees', 'Event attendee responses. Requires UpcomingCalendarEvents to function.', 4, 2),
    ('char', 'CharacterSheet', 'Character Sheet information. Contains basic "Show Info" information along with clones, account balance, implants, attributes, skills, certificates and corporation roles.', 8, 16),
    ('char', 'ContactList', 'List of character contacts and relationship levels.', 16, 16),
    ('char', 'ContactNotifications', 'Most recent contact notifications for the character.', 32, 16),
    ('char', 'Contracts', 'List of all Contracts the character is involved in.', 67108864, 16),
    ('char', 'FacWarStats', 'Characters Factional Warfare Statistics.', 64, 8),
    ('char', 'IndustryJobs', 'Character jobs, completed and active.', 128, 16),
    ('char', 'KillMails', 'Character''s killmails.', 256, 16),
    ('char', 'Locations', 'Allows the fetching of coordinate and name data for items owned by the character.', 134217728, 1),
    ('char', 'MailBodies', 'EVE Mail bodies. Requires MailMessages as well to function.', 512, 16),
    ('char', 'MailingLists', 'List of all Mailing Lists the character subscribes to.', 1024, 16),
    ('char', 'MailMessages', 'List of all messages in the characters EVE Mail Inbox.', 2048, 16),
    ('char', 'MarketOrders', 'List of all Market Orders the character has made.', 4096, 16),
    ('char', 'Medals', 'Medals awarded to the character.', 8192, 2),
    ('char', 'Notifications', 'List of recent notifications sent to the character.', 16384, 16),
    ('char', 'NotificationTexts', 'Actual body of notifications sent to the character. Requires Notification access to function.', 32768, 16),
    ('char', 'Research', 'List of all Research agents working for the character and the progress of the research.', 65536, 16),
    ('char', 'SkillInTraining', 'Skill currently in training on the character. Subset of entire Skill Queue.', 131072, 16),
    ('char', 'SkillQueue', 'Entire skill queue of character.', 262144, 16),
    ('char', 'Standings', 'NPC Standings towards the character.', 524288, 16),
    ('char', 'UpcomingCalendarEvents', 'Upcoming events on characters calendar.', 1048576, 2),
    ('char', 'WalletJournal', 'Wallet journal of character.', 2097152, 16),
    ('char', 'WalletTransactions', 'Market transaction journal of character.', 4194304, 16),
    ('corp', 'AccountBalance', 'Current balance of all corporation accounts.', 1, 16),
    ('corp', 'AssetList', 'List of all corporation assets.', 2, 16),
    ('corp', 'ContactList', 'Corporate contact list and relationships.', 16, 16),
    ('corp', 'ContainerLog', 'Corporate secure container access log.', 32, 16),
    ('corp', 'Contracts', 'List of recent Contracts the corporation is involved in.', 8388608, 16),
    ('corp', 'CorporationSheet', 'Exposes basic "Show Info" information as well as Member Limit and basic division and wallet info.', 8, 16),
    ('corp', 'FacWarStats', 'Corporations Factional Warfare Statistics.', 64, 8),
    ('corp', 'IndustryJobs', 'Corporation jobs, completed and active.', 128, 16),
    ('corp', 'KillMails', 'Corporation killmails.', 256, 16),
    ('corp', 'Locations', 'Allows the fetching of coordinate and name data for items owned by the corporation.', 16777216, 1),
    ('corp', 'MarketOrders', 'List of all corporate market orders.', 4096, 16),
    ('corp', 'Medals', 'List of all medals created by the corporation.', 8192, 16),
    ('corp', 'MemberMedals', 'List of medals awarded to corporation members.', 4, 16),
    ('corp', 'MemberSecurity', 'Member roles and titles.', 512, 2),
    ('corp', 'MemberSecurityLog', 'Member role and title change log.', 1024, 2),
    ('corp', 'MemberTracking', 'Extensive Member information. Time of last logoff, last known location and ship.', 33554432, 16),
    ('corp', 'MemberTrackingLimited', 'Limited Member information.', 2048, 16),
    ('corp', 'OutpostList', 'List of all outposts controlled by the corporation.', 16384, 16),
    ('corp', 'OutpostServiceDetail', 'List of all service settings of corporate outposts.', 32768, 16),
    ('corp', 'Shareholders', 'Shareholders of the corporation.', 65536, 2),
    ('corp', 'Standings', 'NPC Standings towards corporation.', 262144, 16),
    ('corp', 'StarbaseDetail', 'List of all settings of corporate starbases.', 131072, 16),
    ('corp', 'StarbaseList', 'List of all corporate starbases.', 524288, 16),
    ('corp', 'Titles', 'Titles of corporation and the roles they grant.', 4194304, 2),
    ('corp', 'WalletJournal', 'Wallet journal for all corporate accounts.', 1048576, 16),
    ('corp', 'WalletTransactions', 'Market transactions of all corporate accounts.', 2097152, 16),
    ('eve', 'AllianceList', 'Returns a list of alliances in eve.', 1, 16),
    ('eve', 'CertificateTree', 'Returns a list of certificates in eve.', 2, 1),
    ('eve', 'CharacterID', 'Returns the ownerID for a given character, faction, alliance or corporation name, or the typeID for other objects such as stations, solar systems, planets, etc.', 4, 1),
    ('eve', 'CharacterInfo', 'Character information, exposes skill points and current ship information on top of "Show Info" information.', 0, 1),
    ('eve', 'CharacterInfoPrivate', 'Sensitive Character Information, exposes account balance and last known location on top of the other Character Information call.', 16777216, 1),
    ('eve', 'CharacterInfoPublic', 'Character information, exposes skill points and current ship information on top of "Show Info" information.', 8388608, 1),
    ('eve', 'CharacterName', 'Returns the name associated with an ownerID or a typeID.', 8, 1),
    ('eve', 'ConquerableStationList', 'Conquerable Station List including Outpost.', 16, 16),
    ('eve', 'ErrorList', 'Returns a list of error codes that can be returned by the EVE API servers.', 32, 16),
    ('eve', 'FacWarStats', 'Returns global stats on the factions in factional warfare including the number of pilots in each faction, the number of systems they control, and how many kills and victory points each and all factions obtained yesterday, in the last week, and total.', 64, 16),
    ('eve', 'FacWarTopStats', 'Returns Factional Warfare Top 100 Stats.', 128, 16),
    ('eve', 'RefTypes', 'Returns a list of transaction types used in the Journal Entries.', 256, 16),
    ('eve', 'SkillTree', 'XML of currently in-game skills (including unpublished skills).', 512, 1),
    ('map', 'FacWarSystems', 'Returns a list of contestable solarsystems  and the NPC faction currently occupying them. It should be noted that this file only returns a non-zero ID if the occupying faction is not the sovereign faction.', 1, 16),
    ('map', 'Jumps', 'Returns a list of systems where any jumps have happened.', 2, 16),
    ('map', 'Kills', 'Returns the number of kills in solarsystems within the last hour. Only solar system where kills have been made are listed, so assume zero in case the system is not listed.', 4, 16),
    ('map', 'Sovereignty', 'Returns a list of solarsystems and what faction or alliance controls them. ', 8, 16),
    ('map', 'SovereigntyStatus', 'Returns a list of all sovereignty structures in EVE. This API has been disabled and is not expected to return but was included for completeness.', 16, 1),
    ('server', 'ServerStatus', 'Returns current Eve server status and number of players online.', 1, 16);
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilCachedInterval`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilCachedInterval` (
    `section`  VARCHAR(8)       NOT NULL,
    `api`      VARCHAR(32)      NOT NULL,
    `interval` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`section`, `api`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
INSERT INTO `{database}`.`{table_prefix}utilCachedInterval` (`section`, `api`, `interval`)
VALUES
    ('account', 'AccountStatus', 3600),
    ('account', 'APIKeyInfo', 300),
    ('char', 'AccountBalance', 900),
    ('char', 'AssetList', 21600),
    ('char', 'CalendarEventAttendees', 3600),
    ('char', 'CharacterSheet', 3600),
    ('char', 'ContactList', 900),
    ('char', 'ContactNotifications', 21600),
    ('char', 'Contracts', 900),
    ('char', 'FacWarStats', 3600),
    ('char', 'IndustryJobs', 900),
    ('char', 'KillMails', 1800),
    ('char', 'Locations', 3600),
    ('char', 'MailBodies', 1800),
    ('char', 'MailingLists', 21600),
    ('char', 'MailMessages', 1800),
    ('char', 'MarketOrders', 3600),
    ('char', 'Medals', 3600),
    ('char', 'Notifications', 1800),
    ('char', 'NotificationTexts', 1800),
    ('char', 'Research', 900),
    ('char', 'SkillInTraining', 300),
    ('char', 'SkillQueue', 900),
    ('char', 'Standings', 3600),
    ('char', 'UpcomingCalendarEvents', 900),
    ('char', 'WalletJournal', 1620),
    ('char', 'WalletTransactions', 3600),
    ('corp', 'AccountBalance', 900),
    ('corp', 'AssetList', 21600),
    ('corp', 'ContactList', 900),
    ('corp', 'ContainerLog', 3600),
    ('corp', 'Contracts', 900),
    ('corp', 'CorporationSheet', 21600),
    ('corp', 'FacWarStats', 3600),
    ('corp', 'IndustryJobs', 900),
    ('corp', 'KillMails', 1800),
    ('corp', 'Locations', 3600),
    ('corp', 'MarketOrders', 3600),
    ('corp', 'Medals', 3600),
    ('corp', 'MemberMedals', 3600),
    ('corp', 'MemberSecurity', 3600),
    ('corp', 'MemberSecurityLog', 3600),
    ('corp', 'MemberTracking', 21600),
    ('corp', 'OutpostList', 3600),
    ('corp', 'OutpostServiceDetail', 3600),
    ('corp', 'Shareholders', 3600),
    ('corp', 'Standings', 3600),
    ('corp', 'StarbaseDetail', 3600),
    ('corp', 'StarbaseList', 3600),
    ('corp', 'Titles', 3600),
    ('corp', 'WalletJournal', 1620),
    ('corp', 'WalletTransactions', 3600),
    ('eve', 'AllianceList', 3600),
    ('eve', 'CertificateTree', 86400),
    ('eve', 'CharacterInfo', 3600),
    ('eve', 'ConquerableStationList', 3600),
    ('eve', 'ErrorList', 86400),
    ('eve', 'FacWarStats', 3600),
    ('eve', 'FacWarTopStats', 3600),
    ('eve', 'RefTypes', 86400),
    ('eve', 'SkillTree', 86400),
    ('map', 'FacWarSystems', 3600),
    ('map', 'Jumps', 3600),
    ('map', 'Kills', 3600),
    ('map', 'Sovereignty', 3600),
    ('server', 'ServerStatus', 180);
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilCachedUntil`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilCachedUntil` (
    `ownerID`     BIGINT(20) UNSIGNED NOT NULL,
    `api`         VARCHAR(32)         NOT NULL,
    `cachedUntil` DATETIME            NOT NULL,
    `section`     VARCHAR(8)          NOT NULL,
    PRIMARY KEY (`ownerID`, `api`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
INSERT INTO `{database}`.`{table_prefix}utilCachedUntil` (`ownerID`, `api`, `cachedUntil`, `section`)
VALUES
    (0, 'FacWarSystems', '2014-04-09 09:03:19', 'map'),
    (0, 'ServerStatus', '2014-04-09 08:05:58', 'server'),
    (1156, 'APIKeyInfo', '2014-04-09 08:07:22', 'account');
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilGraphic`;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilRegisteredCharacter`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilRegisteredCharacter` (
    `activeAPIMask` BIGINT(20) UNSIGNED DEFAULT NULL,
    `characterID`   BIGINT(20) UNSIGNED NOT NULL,
    `characterName` VARCHAR(100)
                    COLLATE utf8_unicode_ci DEFAULT NULL,
    `isActive`      TINYINT(1) DEFAULT NULL,
    `proxy`         VARCHAR(255)
                    COLLATE utf8_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilRegisteredCorporation`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilRegisteredCorporation` (
    `activeAPIMask`   BIGINT(20) UNSIGNED DEFAULT NULL,
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` VARCHAR(150)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `isActive`        TINYINT(1) DEFAULT NULL,
    `proxy`           VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`corporationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilRegisteredKey`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilRegisteredKey` (
    `activeAPIMask` BIGINT(20) UNSIGNED DEFAULT NULL,
    `isActive`      TINYINT(1) DEFAULT NULL,
    `keyID`         BIGINT(20) UNSIGNED NOT NULL,
    `proxy`         VARCHAR(255) DEFAULT NULL,
    `vCode`         VARCHAR(64)         NOT NULL,
    PRIMARY KEY (`keyID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
INSERT INTO `{database}`.`{table_prefix}utilRegisteredKey` (`activeAPIMask`, `isActive`, `keyID`, `proxy`, `vCode`)
VALUES
    (8388608, 1, 1156, NULL, 'abc123');
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilRegisteredUploader`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilRegisteredUploader` (
    `isActive`            TINYINT(1) DEFAULT NULL,
    `key`                 VARCHAR(255) DEFAULT NULL,
    `ownerID`             BIGINT(20) UNSIGNED NOT NULL,
    `uploadDestinationID` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`ownerID`, `uploadDestinationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilSections`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilSections` (
    `activeAPIMask` BIGINT(20) UNSIGNED NOT NULL,
    `isActive`      TINYINT(1)          NOT NULL,
    `proxy`         VARCHAR(255) DEFAULT NULL,
    `sectionID`     BIGINT(20) UNSIGNED NOT NULL,
    `section`       VARCHAR(8) DEFAULT NULL,
    PRIMARY KEY (`sectionID`),
    KEY `utilSection1` (`section`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
INSERT INTO `{database}`.`{table_prefix}utilSections` (`activeAPIMask`, `isActive`, `proxy`, `sectionID`, `section`)
VALUES
    (33554433, 1, NULL, 1, 'account'),
    (74440635, 1, NULL, 2, 'char'),
    (46068159, 1, NULL, 3, 'corp'),
    (497, 1, NULL, 4, 'eve'),
    (15, 1, NULL, 5, 'map'),
    (1, 1, NULL, 6, 'server');
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilUploadDestination`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilUploadDestination` (
    `isActive`            TINYINT(1) DEFAULT NULL,
    `name`                VARCHAR(25) DEFAULT NULL,
    `uploadDestinationID` BIGINT(20) UNSIGNED NOT NULL,
    `url`                 VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`uploadDestinationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}utilXmlCache`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}utilXmlCache` (
    `hash`     CHAR(40)
               CHARACTER SET ascii     NOT NULL,
    `api`      CHAR(32)
               CHARACTER SET ascii     NOT NULL,
    `modified` TIMESTAMP               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `section`  VARCHAR(8)
               COLLATE utf8_unicode_ci NOT NULL,
    `xml`      LONGTEXT
               COLLATE utf8_unicode_ci,
    PRIMARY KEY (`hash`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
ALTER TABLE `{database}`.`{table_prefix}utilXmlCache` ADD INDEX `utilXmlCache1`  (`section`);
ALTER TABLE `{database}`.`{table_prefix}utilXmlCache` ADD INDEX `utilXmlCache2`  (`api`);
