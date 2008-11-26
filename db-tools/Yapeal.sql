SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `CachedUntil`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CachedUntil` ;

CREATE  TABLE IF NOT EXISTS `CachedUntil` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `tableName` VARCHAR(255) NOT NULL ,
  `cachedUntil` DATETIME NOT NULL ,
  PRIMARY KEY (`tableName`, `ownerID`) )
ENGINE = MEMORY
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `RegisteredUser`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `RegisteredUser` ;

CREATE  TABLE IF NOT EXISTS `RegisteredUser` (
  `userID` BIGINT UNSIGNED NOT NULL ,
  `fullApiKey` VARCHAR(64) NULL DEFAULT NULL ,
  `limitedApiKey` VARCHAR(64) NULL DEFAULT NULL ,
  PRIMARY KEY (`userID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `RegisteredCharacter`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `RegisteredCharacter` ;

CREATE  TABLE IF NOT EXISTS `RegisteredCharacter` (
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `userID` BIGINT UNSIGNED NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
  `graphic` BLOB NULL DEFAULT NULL ,
  `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `RegisteredCorporation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `RegisteredCorporation` ;

CREATE  TABLE IF NOT EXISTS `RegisteredCorporation` (
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
  `graphic` BLOB NULL DEFAULT NULL ,
  `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
  PRIMARY KEY (`corporationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `CharacterSheet`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CharacterSheet` ;

CREATE  TABLE IF NOT EXISTS `CharacterSheet` (
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `race` VARCHAR(255) NOT NULL ,
  `bloodLine` VARCHAR(255) NOT NULL ,
  `gender` VARCHAR(255) NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  `charisma` SMALLINT UNSIGNED NOT NULL ,
  `intelligence` SMALLINT UNSIGNED NOT NULL ,
  `memory` SMALLINT UNSIGNED NOT NULL ,
  `perception` SMALLINT UNSIGNED NOT NULL ,
  `willpower` SMALLINT UNSIGNED NOT NULL ,
  `cloneName` VARCHAR(255) NOT NULL ,
  `cloneSkillPoints` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `skills`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skills` ;

CREATE  TABLE IF NOT EXISTS `skills` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `level` SMALLINT UNSIGNED NOT NULL ,
  `skillpoints` BIGINT UNSIGNED NOT NULL ,
  `unpublished` BOOLEAN NOT NULL DEFAULT FALSE ,
  PRIMARY KEY (`ownerID`, `typeID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet API';


-- -----------------------------------------------------
-- Table `CorporationSheet`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `CorporationSheet` ;

CREATE  TABLE IF NOT EXISTS `CorporationSheet` (
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `ticker` VARCHAR(255) NOT NULL ,
  `ceoID` BIGINT UNSIGNED NOT NULL ,
  `ceoName` VARCHAR(255) NOT NULL ,
  `stationID` BIGINT UNSIGNED NOT NULL ,
  `stationName` VARCHAR(255) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `url` VARCHAR(255) NULL DEFAULT NULL ,
  `allianceId` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `allianceName` VARCHAR(255) NULL DEFAULT NULL ,
  `taxRate` DECIMAL(17,2) UNSIGNED NOT NULL ,
  `memberCount` SMALLINT UNSIGNED NOT NULL ,
  `shares` BIGINT UNSIGNED NOT NULL ,
  `memberLimit` SMALLINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`corporationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `AccountBalance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AccountBalance` ;

CREATE  TABLE IF NOT EXISTS `AccountBalance` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `accountID` BIGINT UNSIGNED NOT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `AssetList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AssetList` ;

CREATE  TABLE IF NOT EXISTS `AssetList` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `itemID` BIGINT UNSIGNED NOT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `quantity` BIGINT UNSIGNED NOT NULL ,
  `flag` SMALLINT UNSIGNED NOT NULL ,
  `singleton` BOOLEAN NOT NULL ,
  `lft` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `rgt` BIGINT UNSIGNED NULL DEFAULT NULL ,
  PRIMARY KEY (`ownerID`, `itemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `logo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `logo` ;

CREATE  TABLE IF NOT EXISTS `logo` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `graphicID` BIGINT UNSIGNED NOT NULL ,
  `shape1` SMALLINT UNSIGNED NOT NULL ,
  `shape2` SMALLINT UNSIGNED NOT NULL ,
  `shape3` SMALLINT UNSIGNED NOT NULL ,
  `color1` BIGINT UNSIGNED NOT NULL ,
  `color2` BIGINT UNSIGNED NOT NULL ,
  `color3` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `graphicID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CorporationSheet API';


-- -----------------------------------------------------
-- Table `divisions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `divisions` ;

CREATE  TABLE IF NOT EXISTS `divisions` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `description` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountKey`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CorporationSheet API';


-- -----------------------------------------------------
-- Table `IndustryJobs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `IndustryJobs` ;

CREATE  TABLE IF NOT EXISTS `IndustryJobs` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `installTime` DATETIME NOT NULL ,
  `jobID` BIGINT UNSIGNED NOT NULL ,
  `assemblyLineID` BIGINT UNSIGNED NOT NULL ,
  `containerID` BIGINT UNSIGNED NOT NULL ,
  `installedItemID` BIGINT UNSIGNED NOT NULL ,
  `installedItemLocationID` BIGINT UNSIGNED NOT NULL ,
  `installedItemQuantity` BIGINT UNSIGNED NOT NULL ,
  `installedItemProductivityLevel` INT NOT NULL ,
  `installedItemMaterialLevel` INT NOT NULL ,
  `installedItemLicensedProductionRunsRemaining` BIGINT UNSIGNED NOT NULL ,
  `outputLocationID` BIGINT UNSIGNED NOT NULL ,
  `installerID` BIGINT UNSIGNED NOT NULL ,
  `runs` BIGINT UNSIGNED NOT NULL ,
  `licensedProductionRuns` BIGINT UNSIGNED NOT NULL ,
  `installedInSolarSystemID` BIGINT UNSIGNED NOT NULL ,
  `containerLocationID` BIGINT UNSIGNED NOT NULL ,
  `materialMultiplier` DECIMAL(17,2) NOT NULL ,
  `charMaterialMultiplier` DECIMAL(17,2) NOT NULL ,
  `timeMultiplier` DECIMAL(17,2) NOT NULL ,
  `charTimeMultiplier` DECIMAL(17,2) NOT NULL ,
  `installedItemTypeID` BIGINT UNSIGNED NOT NULL ,
  `outputTypeID` BIGINT UNSIGNED NOT NULL ,
  `containerTypeID` BIGINT UNSIGNED NOT NULL ,
  `installedItemCopy` BIGINT UNSIGNED NOT NULL ,
  `completed` SMALLINT UNSIGNED NOT NULL ,
  `completedSuccessfully` SMALLINT UNSIGNED NOT NULL ,
  `installedItemFlag` SMALLINT UNSIGNED NOT NULL ,
  `outputFlag` SMALLINT UNSIGNED NOT NULL ,
  `activityID` SMALLINT UNSIGNED NOT NULL ,
  `completedStatus` SMALLINT UNSIGNED NOT NULL ,
  `beginProductionTime` DATETIME NOT NULL ,
  `endProductionTime` DATETIME NOT NULL ,
  `pauseProductionTime` DATETIME NOT NULL ,
  PRIMARY KEY (`ownerID`, `installTime`, `jobID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `MarketOrders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `MarketOrders` ;

CREATE  TABLE IF NOT EXISTS `MarketOrders` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `issued` DATETIME NOT NULL ,
  `orderID` BIGINT UNSIGNED NOT NULL ,
  `charID` BIGINT UNSIGNED NOT NULL ,
  `stationID` BIGINT UNSIGNED NOT NULL ,
  `volEntered` BIGINT UNSIGNED NOT NULL ,
  `volRemaining` BIGINT UNSIGNED NOT NULL ,
  `minVolume` BIGINT UNSIGNED NOT NULL ,
  `orderState` TINYINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `range` SMALLINT NOT NULL ,
  `duration` SMALLINT UNSIGNED NOT NULL ,
  `escrow` DECIMAL(17,2) NOT NULL ,
  `price` DECIMAL(17,2) NOT NULL ,
  `bid` BOOLEAN NOT NULL ,
  `changed` TIMESTAMP NOT NULL COMMENT 'Added to API to allow tracking of when order was last active. Auto updated by MySQL' ,
  PRIMARY KEY (`ownerID`, `issued`, `orderID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `MemberTracking`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `MemberTracking` ;

CREATE  TABLE IF NOT EXISTS `MemberTracking` (
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `startDateTime` DATETIME NOT NULL ,
  `baseID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `base` VARCHAR(255) NULL DEFAULT NULL ,
  `title` TEXT NULL DEFAULT NULL ,
  `logonDateTime` DATETIME NOT NULL ,
  `logoffDateTime` DATETIME NOT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `location` VARCHAR(255) NOT NULL ,
  `shipTypeID` BIGINT UNSIGNED NOT NULL ,
  `shipType` VARCHAR(255) NOT NULL ,
  `roles` VARCHAR(64) NOT NULL ,
  `grantableRoles` VARCHAR(64) NOT NULL ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE INDEX `index1` ON `MemberTracking` (`ownerID` ASC) ;


-- -----------------------------------------------------
-- Table `StarbaseList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `StarbaseList` ;

CREATE  TABLE IF NOT EXISTS `StarbaseList` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `itemID` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `moonID` BIGINT UNSIGNED NOT NULL ,
  `state` SMALLINT UNSIGNED NOT NULL ,
  `stateTimestamp` DATETIME NOT NULL ,
  `onlineTimestamp` DATETIME NOT NULL ,
  PRIMARY KEY (`ownerID`, `itemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `walletDivisions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `walletDivisions` ;

CREATE  TABLE IF NOT EXISTS `walletDivisions` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `description` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountKey`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'This is sub-table from CorporationSheet API';


-- -----------------------------------------------------
-- Table `WalletJournal`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `WalletJournal` ;

CREATE  TABLE IF NOT EXISTS `WalletJournal` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `date` DATETIME NOT NULL ,
  `refID` BIGINT UNSIGNED NOT NULL ,
  `refTypeID` TINYINT UNSIGNED NOT NULL ,
  `ownerName1` VARCHAR(255) NULL ,
  `ownerID1` BIGINT UNSIGNED NULL ,
  `ownerName2` VARCHAR(255) NULL ,
  `ownerID2` BIGINT UNSIGNED NULL ,
  `argName1` VARCHAR(255) NULL ,
  `argID1` BIGINT UNSIGNED NULL ,
  `amount` DECIMAL(17,2) NOT NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  `reason` TEXT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL COMMENT 'Nothing in XML results IDs which wallet it is for we have to add it. Taken from POST call params.' ,
  PRIMARY KEY (`ownerID`, `date`, `refID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `WalletTransactions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `WalletTransactions` ;

CREATE  TABLE IF NOT EXISTS `WalletTransactions` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `transactionDateTime` DATETIME NOT NULL ,
  `transactionID` BIGINT UNSIGNED NOT NULL ,
  `quantity` BIGINT UNSIGNED NOT NULL ,
  `typeName` VARCHAR(255) NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `price` DECIMAL(17,2) NOT NULL ,
  `clientID` BIGINT UNSIGNED NULL ,
  `clientName` VARCHAR(255) NULL ,
  `characterID` BIGINT UNSIGNED NULL ,
  `characterName` VARCHAR(255) NULL ,
  `stationID` BIGINT UNSIGNED NULL ,
  `stationName` VARCHAR(255) NULL ,
  `transactionType` VARCHAR(255) NOT NULL DEFAULT 'sell' ,
  `transactionFor` VARCHAR(255) NOT NULL DEFAULT 'corporation' ,
  `accountKey` SMALLINT UNSIGNED NOT NULL COMMENT 'Nothing in XML results IDs which wallet it is for we have to add it. Taken from POST call params.' ,
  PRIMARY KEY (`ownerID`, `transactionDateTime`, `transactionID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `AllianceList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `AllianceList` ;

CREATE  TABLE IF NOT EXISTS `AllianceList` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `name` VARCHAR(255) NULL DEFAULT NULL ,
  `shortName` VARCHAR(255) NULL DEFAULT NULL ,
  `executorCorpID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `memberCount` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `startDate` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`allianceID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `ConquerableStationList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ConquerableStationList` ;

CREATE  TABLE IF NOT EXISTS `ConquerableStationList` (
  `stationID` BIGINT UNSIGNED NOT NULL ,
  `stationName` VARCHAR(255) NULL DEFAULT NULL ,
  `stationTypeID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `solarSystemID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `corporationID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `corporationName` VARCHAR(255) NULL DEFAULT NULL ,
  PRIMARY KEY (`stationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `ErrorList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ErrorList` ;

CREATE  TABLE IF NOT EXISTS `ErrorList` (
  `errorCode` SMALLINT UNSIGNED NOT NULL ,
  `errorText` TEXT NOT NULL ,
  PRIMARY KEY (`errorCode`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `RefTypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `RefTypes` ;

CREATE  TABLE IF NOT EXISTS `RefTypes` (
  `refTypeID` SMALLINT UNSIGNED NOT NULL ,
  `refTypeName` VARCHAR(255) NULL DEFAULT NULL ,
  PRIMARY KEY (`refTypeID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `certificates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `certificates` ;

CREATE  TABLE IF NOT EXISTS `certificates` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `certificateID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `certificateID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet';


-- -----------------------------------------------------
-- Table `ContainerLog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ContainerLog` ;

CREATE  TABLE IF NOT EXISTS `ContainerLog` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `logTime` DATETIME NOT NULL ,
  `itemID` BIGINT UNSIGNED NOT NULL ,
  `itemTypeID` BIGINT UNSIGNED NOT NULL ,
  `actorID` BIGINT UNSIGNED NOT NULL ,
  `actorName` VARCHAR(255) NOT NULL ,
  `flag` VARCHAR(255) NOT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `action` VARCHAR(255) NOT NULL ,
  `passwordType` VARCHAR(255) NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `quantity` BIGINT UNSIGNED NOT NULL ,
  `oldConfiguration` VARCHAR(255) NOT NULL ,
  `newConfiguration` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `logTime`, `itemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `ServerStatus`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ServerStatus` ;

CREATE  TABLE IF NOT EXISTS `ServerStatus` (
  `serverOpen` BOOLEAN NOT NULL ,
  `onlinePlayers` BIGINT UNSIGNED NOT NULL )
ENGINE = MEMORY
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
