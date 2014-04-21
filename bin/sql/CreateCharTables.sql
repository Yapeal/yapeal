SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charAccountBalance`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charAccountBalance` (
    `ownerID`    BIGINT(20) UNSIGNED  NOT NULL,
    `accountID`  BIGINT(20) UNSIGNED  NOT NULL,
    `accountKey` SMALLINT(4) UNSIGNED NOT NULL,
    `balance`    DECIMAL(17, 2)       NOT NULL,
    PRIMARY KEY (`ownerID`, `accountKey`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charAllianceContactList`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charAllianceContactList` (
    `ownerID`       BIGINT(20) UNSIGNED     NOT NULL,
    `contactID`     BIGINT(20) UNSIGNED     NOT NULL,
    `contactTypeID` BIGINT(20) UNSIGNED DEFAULT NULL,
    `contactName`   VARCHAR(255)
                    COLLATE utf8_unicode_ci NOT NULL,
    `standing`      DECIMAL(5, 2)           NOT NULL,
    PRIMARY KEY (`ownerID`, `contactID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charAssetList`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charAssetList` (
    `ownerID`     BIGINT(20) UNSIGNED  NOT NULL,
    `flag`        SMALLINT(5) UNSIGNED NOT NULL,
    `itemID`      BIGINT(20) UNSIGNED  NOT NULL,
    `lft`         BIGINT(20) UNSIGNED  NOT NULL,
    `locationID`  BIGINT(20) UNSIGNED  NOT NULL,
    `lvl`         TINYINT(2) UNSIGNED  NOT NULL,
    `quantity`    BIGINT(20) UNSIGNED  NOT NULL,
    `rawQuantity` BIGINT(20) DEFAULT NULL,
    `rgt`         BIGINT(20) UNSIGNED  NOT NULL,
    `singleton`   TINYINT(1)           NOT NULL,
    `typeID`      BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY (`ownerID`, `itemID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
ALTER TABLE `{database}`.`{table_prefix}charAssetList` ADD INDEX `charAssetList1`  (`lft`);
ALTER TABLE `{database}`.`{table_prefix}charAssetList` ADD INDEX `charAssetList2`  (`locationID`);
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charAttackers`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charAttackers` (
    `killID`          BIGINT(20) UNSIGNED NOT NULL,
    `allianceID`      BIGINT(20) UNSIGNED NOT NULL,
    `allianceName`    VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `characterID`     BIGINT(20) UNSIGNED NOT NULL,
    `characterName`   VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `damageDone`      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    `factionID`       BIGINT(20) UNSIGNED NOT NULL,
    `factionName`     VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `finalBlow`       TINYINT(1)          NOT NULL,
    `securityStatus`  DOUBLE              NOT NULL,
    `shipTypeID`      BIGINT(20) UNSIGNED NOT NULL,
    `weaponTypeID`    BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`killID`, `characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charAttributeEnhancers`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charAttributeEnhancers` (
    `ownerID`          BIGINT(20) UNSIGNED     NOT NULL,
    `augmentatorName`  VARCHAR(100)
                       COLLATE utf8_unicode_ci NOT NULL,
    `augmentatorValue` TINYINT(2) UNSIGNED     NOT NULL,
    `bonusName`        VARCHAR(100)
                       COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `bonusName`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charAttributes`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charAttributes` (
    `charisma`     TINYINT(2) UNSIGNED NOT NULL,
    `intelligence` TINYINT(2) UNSIGNED NOT NULL,
    `memory`       TINYINT(2) UNSIGNED NOT NULL,
    `ownerID`      BIGINT(20) UNSIGNED NOT NULL,
    `perception`   TINYINT(2) UNSIGNED NOT NULL,
    `willpower`    TINYINT(2) UNSIGNED NOT NULL,
    PRIMARY KEY (`ownerID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCalendarEventAttendees`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCalendarEventAttendees` (
    `ownerID`       BIGINT(20) UNSIGNED     NOT NULL,
    `characterID`   BIGINT(20) UNSIGNED     NOT NULL,
    `characterName` VARCHAR(255)
                    COLLATE utf8_unicode_ci NOT NULL,
    `response`      VARCHAR(32)
                    COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCertificates`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCertificates` (
    `ownerID`       BIGINT(20) UNSIGNED NOT NULL,
    `certificateID` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`ownerID`, `certificateID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCharacterSheet`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCharacterSheet` (
    `allianceID`       BIGINT(20) UNSIGNED DEFAULT '0',
    `allianceName`     VARCHAR(255)
                       COLLATE utf8_unicode_ci DEFAULT '',
    `ancestry`         VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL,
    `balance`          DECIMAL(17, 2)          NOT NULL,
    `bloodLine`        VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL,
    `characterID`      BIGINT(20) UNSIGNED     NOT NULL,
    `cloneName`        VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL,
    `cloneSkillPoints` BIGINT(20) UNSIGNED     NOT NULL,
    `corporationID`    BIGINT(20) UNSIGNED     NOT NULL,
    `corporationName`  VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL,
    `factionID`        BIGINT(20) UNSIGNED DEFAULT '0',
    `factionName`      VARCHAR(255)
                       COLLATE utf8_unicode_ci DEFAULT '',
    `DoB`              DATETIME                NOT NULL,
    `gender`           VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL,
    `name`             VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL,
    `race`             VARCHAR(255)
                       COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charContactList`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charContactList` (
    `ownerID`       BIGINT(20) UNSIGNED     NOT NULL,
    `contactID`     BIGINT(20) UNSIGNED     NOT NULL,
    `contactTypeID` BIGINT(20) UNSIGNED DEFAULT NULL,
    `contactName`   VARCHAR(255)
                    COLLATE utf8_unicode_ci NOT NULL,
    `inWatchlist`   TINYINT(1)              NOT NULL,
    `standing`      DECIMAL(5, 2)           NOT NULL,
    PRIMARY KEY (`ownerID`, `contactID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charContactNotifications`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charContactNotifications` (
    `ownerID`        BIGINT(20) UNSIGNED     NOT NULL,
    `notificationID` BIGINT(20) UNSIGNED     NOT NULL,
    `senderID`       BIGINT(20) UNSIGNED     NOT NULL,
    `senderName`     VARCHAR(255)
                     COLLATE utf8_unicode_ci NOT NULL,
    `sentDate`       DATETIME                NOT NULL,
    `messageData`    TEXT
                     COLLATE utf8_unicode_ci,
    PRIMARY KEY (`ownerID`, `notificationID`, `senderID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charContracts`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charContracts` (
    `ownerID`        BIGINT(20) UNSIGNED     NOT NULL,
    `contractID`     BIGINT(20) UNSIGNED     NOT NULL,
    `issuerID`       BIGINT(20) UNSIGNED     NOT NULL,
    `issuerCorpID`   BIGINT(20) UNSIGNED     NOT NULL,
    `assigneeID`     BIGINT(20) UNSIGNED     NOT NULL,
    `acceptorID`     BIGINT(20) UNSIGNED     NOT NULL,
    `startStationID` BIGINT(20) UNSIGNED     NOT NULL,
    `endStationID`   BIGINT(20) UNSIGNED     NOT NULL,
    `type`           VARCHAR(255)
                     COLLATE utf8_unicode_ci NOT NULL,
    `status`         VARCHAR(255)
                     COLLATE utf8_unicode_ci NOT NULL,
    `title`          VARCHAR(255)
                     COLLATE utf8_unicode_ci DEFAULT NULL,
    `forCorp`        TINYINT(1)              NOT NULL,
    `availability`   VARCHAR(255)
                     COLLATE utf8_unicode_ci NOT NULL,
    `dateIssued`     DATETIME                NOT NULL,
    `dateExpired`    DATETIME                NOT NULL,
    `dateAccepted`   DATETIME DEFAULT NULL,
    `numDays`        SMALLINT(3) UNSIGNED    NOT NULL,
    `dateCompleted`  DATETIME DEFAULT NULL,
    `price`          DECIMAL(17, 2)          NOT NULL,
    `reward`         DECIMAL(17, 2)          NOT NULL,
    `collateral`     DECIMAL(17, 2)          NOT NULL,
    `buyout`         DECIMAL(17, 2)          NOT NULL,
    `volume`         BIGINT(20) UNSIGNED     NOT NULL,
    PRIMARY KEY (`ownerID`, `contractID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCorporateContactList`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCorporateContactList` (
    `ownerID`       BIGINT(20) UNSIGNED     NOT NULL,
    `contactID`     BIGINT(20) UNSIGNED     NOT NULL,
    `contactTypeID` BIGINT(20) UNSIGNED DEFAULT NULL,
    `contactName`   VARCHAR(255)
                    COLLATE utf8_unicode_ci NOT NULL,
    `standing`      DECIMAL(5, 2)           NOT NULL,
    PRIMARY KEY (`ownerID`, `contactID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCorporationRoles`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCorporationRoles` (
    `ownerID`  BIGINT(20) UNSIGNED     NOT NULL,
    `roleID`   BIGINT(20) UNSIGNED     NOT NULL,
    `roleName` VARCHAR(255)
               COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `roleID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCorporationRolesAtBase`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCorporationRolesAtBase` (
    `ownerID`  BIGINT(20) UNSIGNED     NOT NULL,
    `roleID`   BIGINT(20) UNSIGNED     NOT NULL,
    `roleName` VARCHAR(255)
               COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `roleID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCorporationRolesAtHQ`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCorporationRolesAtHQ` (
    `ownerID`  BIGINT(20) UNSIGNED     NOT NULL,
    `roleID`   BIGINT(20) UNSIGNED     NOT NULL,
    `roleName` VARCHAR(255)
               COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `roleID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCorporationRolesAtOther`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCorporationRolesAtOther` (
    `ownerID`  BIGINT(20) UNSIGNED     NOT NULL,
    `roleID`   BIGINT(20) UNSIGNED     NOT NULL,
    `roleName` VARCHAR(255)
               COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `roleID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charCorporationTitles`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charCorporationTitles` (
    `ownerID`   BIGINT(20) UNSIGNED     NOT NULL,
    `titleID`   BIGINT(20) UNSIGNED     NOT NULL,
    `titleName` VARCHAR(255)
                COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `titleID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charFacWarStats`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charFacWarStats` (
    `ownerID`                BIGINT(20) UNSIGNED     NOT NULL,
    `factionID`              BIGINT(20) UNSIGNED     NOT NULL,
    `factionName`            VARCHAR(32)
                             COLLATE utf8_unicode_ci NOT NULL,
    `enlisted`               DATETIME                NOT NULL,
    `currentRank`            BIGINT(20) UNSIGNED     NOT NULL,
    `highestRank`            BIGINT(20) UNSIGNED     NOT NULL,
    `killsYesterday`         BIGINT(20) UNSIGNED     NOT NULL,
    `killsLastWeek`          BIGINT(20) UNSIGNED     NOT NULL,
    `killsTotal`             BIGINT(20) UNSIGNED     NOT NULL,
    `victoryPointsYesterday` BIGINT(20) UNSIGNED     NOT NULL,
    `victoryPointsLastWeek`  BIGINT(20) UNSIGNED     NOT NULL,
    `victoryPointsTotal`     BIGINT(20) UNSIGNED     NOT NULL,
    PRIMARY KEY (`ownerID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
ALTER TABLE `{database}`.`{table_prefix}charFacWarStats` ADD INDEX `charFacWarStats1`  (`factionID`);
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charIndustryJobs`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charIndustryJobs` (
    `ownerID`                                      BIGINT(20) UNSIGNED  NOT NULL,
    `activityID`                                   TINYINT(2) UNSIGNED  NOT NULL,
    `assemblyLineID`                               BIGINT(20) UNSIGNED  NOT NULL,
    `beginProductionTime`                          DATETIME             NOT NULL,
    `charMaterialMultiplier`                       DECIMAL(4, 2)        NOT NULL,
    `charTimeMultiplier`                           DECIMAL(4, 2)        NOT NULL,
    `completed`                                    TINYINT(1)           NOT NULL,
    `completedStatus`                              TINYINT(2) UNSIGNED  NOT NULL,
    `completedSuccessfully`                        TINYINT(2) UNSIGNED  NOT NULL,
    `containerID`                                  BIGINT(20) UNSIGNED  NOT NULL,
    `containerLocationID`                          BIGINT(20) UNSIGNED  NOT NULL,
    `containerTypeID`                              BIGINT(20) UNSIGNED  NOT NULL,
    `endProductionTime`                            DATETIME             NOT NULL,
    `installedInSolarSystemID`                     BIGINT(20) UNSIGNED  NOT NULL,
    `installedItemCopy`                            BIGINT(20) UNSIGNED  NOT NULL,
    `installedItemFlag`                            SMALLINT(5) UNSIGNED NOT NULL,
    `installedItemID`                              BIGINT(20) UNSIGNED  NOT NULL,
    `installedItemLicensedProductionRunsRemaining` BIGINT(20)           NOT NULL,
    `installedItemLocationID`                      BIGINT(20) UNSIGNED  NOT NULL,
    `installedItemMaterialLevel`                   BIGINT(20)           NOT NULL,
    `installedItemProductivityLevel`               BIGINT(20)           NOT NULL,
    `installedItemQuantity`                        BIGINT(20) UNSIGNED  NOT NULL,
    `installedItemTypeID`                          BIGINT(20) UNSIGNED  NOT NULL,
    `installerID`                                  BIGINT(20) UNSIGNED  NOT NULL,
    `installTime`                                  DATETIME             NOT NULL,
    `jobID`                                        BIGINT(20) UNSIGNED  NOT NULL,
    `licensedProductionRuns`                       BIGINT(20)           NOT NULL,
    `materialMultiplier`                           DECIMAL(4, 2)        NOT NULL,
    `outputFlag`                                   SMALLINT(5) UNSIGNED NOT NULL,
    `outputLocationID`                             BIGINT(20) UNSIGNED  NOT NULL,
    `outputTypeID`                                 BIGINT(20) UNSIGNED  NOT NULL,
    `pauseProductionTime`                          DATETIME             NOT NULL,
    `runs`                                         BIGINT(20) UNSIGNED  NOT NULL,
    `timeMultiplier`                               DECIMAL(4, 2)        NOT NULL,
    PRIMARY KEY (`ownerID`, `jobID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charItems`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charItems` (
    `flag`         SMALLINT(5) UNSIGNED NOT NULL,
    `killID`       BIGINT(20) UNSIGNED  NOT NULL,
    `lft`          BIGINT(20) UNSIGNED  NOT NULL,
    `lvl`          TINYINT(2) UNSIGNED  NOT NULL,
    `rgt`          BIGINT(20) UNSIGNED  NOT NULL,
    `qtyDropped`   BIGINT(20) UNSIGNED  NOT NULL,
    `qtyDestroyed` BIGINT(20) UNSIGNED  NOT NULL,
    `singleton`    SMALLINT(5) UNSIGNED NOT NULL,
    `typeID`       BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY (`killID`, `lft`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charKillMails`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charKillMails` (
    `killID`        BIGINT(20) UNSIGNED NOT NULL,
    `killTime`      DATETIME            NOT NULL,
    `moonID`        BIGINT(20) UNSIGNED NOT NULL,
    `solarSystemID` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`killID`, `killTime`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charMailBodies`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charMailBodies` (
    `ownerID`   BIGINT(20) UNSIGNED NOT NULL,
    `body`      TEXT
                COLLATE utf8_unicode_ci,
    `messageID` BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`ownerID`, `messageID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charMailingLists`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charMailingLists` (
    `ownerID`     BIGINT(20) UNSIGNED     NOT NULL,
    `displayName` VARCHAR(255)
                  COLLATE utf8_unicode_ci NOT NULL,
    `listID`      BIGINT(20) UNSIGNED     NOT NULL,
    PRIMARY KEY (`ownerID`, `listID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charMailMessages`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charMailMessages` (
    `ownerID`            BIGINT(20) UNSIGNED NOT NULL,
    `messageID`          BIGINT(20) UNSIGNED NOT NULL,
    `senderID`           BIGINT(20) UNSIGNED NOT NULL,
    `senderName`         VARCHAR(255)
                         COLLATE utf8_unicode_ci DEFAULT NULL,
    `sentDate`           DATETIME            NOT NULL,
    `title`              VARCHAR(255)
                         COLLATE utf8_unicode_ci DEFAULT NULL,
    `toCharacterIDs`     TEXT
                         COLLATE utf8_unicode_ci,
    `toCorpOrAllianceID` BIGINT(20) UNSIGNED DEFAULT '0',
    `toListID`           TEXT
                         COLLATE utf8_unicode_ci,
    `senderTypeID`       BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`ownerID`, `messageID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charMarketOrders`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charMarketOrders` (
    `ownerID`      BIGINT(20) UNSIGNED  NOT NULL,
    `accountKey`   SMALLINT(4) UNSIGNED NOT NULL,
    `bid`          TINYINT(1)           NOT NULL,
    `charID`       BIGINT(20) UNSIGNED  NOT NULL,
    `duration`     SMALLINT(3) UNSIGNED NOT NULL,
    `escrow`       DECIMAL(17, 2)       NOT NULL,
    `issued`       DATETIME             NOT NULL,
    `minVolume`    BIGINT(20) UNSIGNED  NOT NULL,
    `orderID`      BIGINT(20) UNSIGNED  NOT NULL,
    `orderState`   TINYINT(2) UNSIGNED  NOT NULL,
    `price`        DECIMAL(17, 2)       NOT NULL,
    `range`        SMALLINT(6)          NOT NULL,
    `stationID`    BIGINT(20) UNSIGNED DEFAULT NULL,
    `typeID`       BIGINT(20) UNSIGNED DEFAULT NULL,
    `volEntered`   BIGINT(20) UNSIGNED  NOT NULL,
    `volRemaining` BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY (`ownerID`, `orderID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charNotifications`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charNotifications` (
    `ownerID`        BIGINT(20) UNSIGNED  NOT NULL,
    `notificationID` BIGINT(20) UNSIGNED  NOT NULL,
    `read`           TINYINT(1)           NOT NULL,
    `senderID`       BIGINT(20) UNSIGNED  NOT NULL,
    `sentDate`       DATETIME             NOT NULL,
    `typeID`         SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY (`ownerID`, `notificationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charNotificationTexts`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charNotificationTexts` (
    `ownerID`        BIGINT(20) UNSIGNED NOT NULL,
    `notificationID` BIGINT(20) UNSIGNED NOT NULL,
    `text`           TEXT
                     COLLATE utf8_unicode_ci,
    PRIMARY KEY (`ownerID`, `notificationID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charResearch`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charResearch` (
    `ownerID`           BIGINT(20) UNSIGNED NOT NULL,
    `agentID`           BIGINT(20) UNSIGNED NOT NULL,
    `pointsPerDay`      DECIMAL(5, 2)       NOT NULL,
    `skillTypeID`       BIGINT(20) UNSIGNED DEFAULT NULL,
    `remainderPoints`   DOUBLE              NOT NULL,
    `researchStartDate` DATETIME            NOT NULL,
    PRIMARY KEY (`ownerID`, `agentID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charSkillInTraining`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charSkillInTraining` (
    `currentTQTime`         DATETIME DEFAULT NULL,
    `offset`                TINYINT(2)          NOT NULL,
    `ownerID`               BIGINT(20) UNSIGNED NOT NULL,
    `skillInTraining`       TINYINT(1) UNSIGNED NOT NULL,
    `trainingDestinationSP` BIGINT(20) UNSIGNED NOT NULL,
    `trainingEndTime`       DATETIME DEFAULT NULL,
    `trainingStartSP`       BIGINT(20) UNSIGNED NOT NULL,
    `trainingStartTime`     DATETIME DEFAULT NULL,
    `trainingToLevel`       TINYINT(1) UNSIGNED NOT NULL,
    `trainingTypeID`        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`ownerID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charSkillQueue`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charSkillQueue` (
    `endSP`         BIGINT(20) UNSIGNED NOT NULL,
    `endTime`       DATETIME            NOT NULL,
    `level`         TINYINT(1) UNSIGNED NOT NULL,
    `ownerID`       BIGINT(20) UNSIGNED NOT NULL,
    `queuePosition` TINYINT(2) UNSIGNED NOT NULL,
    `startSP`       BIGINT(20) UNSIGNED NOT NULL,
    `startTime`     DATETIME            NOT NULL,
    `typeID`        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`ownerID`, `queuePosition`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charSkills`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charSkills` (
    `level`       TINYINT(1) UNSIGNED NOT NULL,
    `ownerID`     BIGINT(20) UNSIGNED NOT NULL,
    `skillpoints` BIGINT(20) UNSIGNED NOT NULL,
    `typeID`      BIGINT(20) UNSIGNED NOT NULL,
    `published`   TINYINT(1)          NOT NULL,
    PRIMARY KEY (`ownerID`, `typeID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charStandingsFromAgents`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charStandingsFromAgents` (
    `ownerID`  BIGINT(20) UNSIGNED     NOT NULL,
    `fromID`   BIGINT(20) UNSIGNED     NOT NULL,
    `fromName` VARCHAR(255)
               COLLATE utf8_unicode_ci NOT NULL,
    `standing` DECIMAL(5, 2)           NOT NULL,
    PRIMARY KEY (`ownerID`, `fromID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charStandingsFromFactions`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charStandingsFromFactions` (
    `ownerID`  BIGINT(20) UNSIGNED     NOT NULL,
    `fromID`   BIGINT(20) UNSIGNED     NOT NULL,
    `fromName` VARCHAR(255)
               COLLATE utf8_unicode_ci NOT NULL,
    `standing` DECIMAL(5, 2)           NOT NULL,
    PRIMARY KEY (`ownerID`, `fromID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charStandingsFromNPCCorporations`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charStandingsFromNPCCorporations` (
    `ownerID`  BIGINT(20) UNSIGNED     NOT NULL,
    `fromID`   BIGINT(20) UNSIGNED     NOT NULL,
    `fromName` VARCHAR(255)
               COLLATE utf8_unicode_ci NOT NULL,
    `standing` DECIMAL(5, 2)           NOT NULL,
    PRIMARY KEY (`ownerID`, `fromID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charVictim`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charVictim` (
    `killID`          BIGINT(20) UNSIGNED NOT NULL,
    `allianceID`      BIGINT(20) UNSIGNED NOT NULL,
    `allianceName`    VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `characterID`     BIGINT(20) UNSIGNED NOT NULL,
    `characterName`   VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `corporationID`   BIGINT(20) UNSIGNED NOT NULL,
    `corporationName` VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `damageTaken`     BIGINT(20) UNSIGNED NOT NULL,
    `factionID`       BIGINT(20) UNSIGNED NOT NULL,
    `factionName`     VARCHAR(255)
                      COLLATE utf8_unicode_ci DEFAULT NULL,
    `shipTypeID`      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`killID`, `characterID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charWalletJournal`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charWalletJournal` (
    `ownerID`       BIGINT(20) UNSIGNED  NOT NULL,
    `accountKey`    SMALLINT(4) UNSIGNED NOT NULL,
    `amount`        DECIMAL(17, 2)       NOT NULL,
    `argID1`        BIGINT(20) UNSIGNED DEFAULT NULL,
    `argName1`      VARCHAR(255)
                    COLLATE utf8_unicode_ci DEFAULT NULL,
    `balance`       DECIMAL(17, 2)       NOT NULL,
    `date`          DATETIME             NOT NULL,
    `ownerID1`      BIGINT(20) UNSIGNED DEFAULT NULL,
    `ownerID2`      BIGINT(20) UNSIGNED DEFAULT NULL,
    `ownerName1`    VARCHAR(255)
                    COLLATE utf8_unicode_ci DEFAULT NULL,
    `ownerName2`    VARCHAR(255)
                    COLLATE utf8_unicode_ci DEFAULT NULL,
    `reason`        TEXT
                    COLLATE utf8_unicode_ci,
    `refID`         BIGINT(20) UNSIGNED  NOT NULL,
    `refTypeID`     INT(3) UNSIGNED      NOT NULL,
    `taxAmount`     DECIMAL(17, 2)       NOT NULL,
    `taxReceiverID` BIGINT(20) UNSIGNED DEFAULT '0',
    `owner1TypeID`  BIGINT(20) UNSIGNED DEFAULT NULL,
    `owner2TypeID`  BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`ownerID`, `refID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
DROP TABLE IF EXISTS `{database}`.`{table_prefix}charWalletTransactions`;
CREATE TABLE IF NOT EXISTS `{database}`.`{table_prefix}charWalletTransactions` (
    `ownerID`              BIGINT(20) UNSIGNED     NOT NULL,
    `accountKey`           SMALLINT(4) UNSIGNED    NOT NULL,
    `clientID`             BIGINT(20) UNSIGNED DEFAULT NULL,
    `clientName`           VARCHAR(255)
                           COLLATE utf8_unicode_ci DEFAULT NULL,
    `clientTypeID`         BIGINT(20) UNSIGNED DEFAULT NULL,
    `journalTransactionID` BIGINT(20) UNSIGNED     NOT NULL,
    `price`                DECIMAL(17, 2)          NOT NULL,
    `quantity`             BIGINT(20) UNSIGNED     NOT NULL,
    `stationID`            BIGINT(20) UNSIGNED DEFAULT NULL,
    `stationName`          VARCHAR(255)
                           COLLATE utf8_unicode_ci DEFAULT NULL,
    `transactionDateTime`  DATETIME                NOT NULL,
    `transactionFor`       VARCHAR(255)
                           COLLATE utf8_unicode_ci NOT NULL DEFAULT 'corporation',
    `transactionID`        BIGINT(20) UNSIGNED     NOT NULL,
    `transactionType`      VARCHAR(255)
                           COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sell',
    `typeID`               BIGINT(20) UNSIGNED     NOT NULL,
    `typeName`             VARCHAR(255)
                           COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`ownerID`, `transactionID`)
)
    ENGINE =InnoDB
    DEFAULT CHARSET =utf8
    COLLATE =utf8_unicode_ci;
