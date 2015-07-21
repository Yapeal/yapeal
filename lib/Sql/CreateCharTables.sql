CREATE TABLE "{database}"."{table_prefix}charAccountBalance" (
    "ownerID"    BIGINT(20) UNSIGNED  NOT NULL,
    "accountID"  BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey" SMALLINT(4) UNSIGNED NOT NULL,
    "balance"    DECIMAL(17,2)        NOT NULL,
    PRIMARY KEY ("ownerID","accountKey")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charAllianceContactList" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(50)            NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","contactID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charAssetList" (
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "flag"        SMALLINT(5) UNSIGNED NOT NULL,
    "itemID"      BIGINT(20) UNSIGNED  NOT NULL,
    "lft"         BIGINT(20) UNSIGNED  NOT NULL,
    "locationID"  BIGINT(20) UNSIGNED  NOT NULL,
    "lvl"         TINYINT(2) UNSIGNED  NOT NULL,
    "quantity"    BIGINT(20) UNSIGNED  NOT NULL,
    "rawQuantity" BIGINT(20) DEFAULT NULL,
    "rgt"         BIGINT(20) UNSIGNED  NOT NULL,
    "singleton"   TINYINT(1)           NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAssetList" ADD INDEX "charAssetList1"  ("lft");
ALTER TABLE "{database}"."{table_prefix}charAssetList" ADD INDEX "charAssetList2"  ("locationID");
CREATE TABLE "{database}"."{table_prefix}charAttackers" (
    "killID"          BIGINT(20) UNSIGNED NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50)                     DEFAULT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)                     DEFAULT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)                     DEFAULT NULL,
    "damageDone"      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50)                     DEFAULT NULL,
    "finalBlow"       TINYINT(1)          NOT NULL,
    "securityStatus"  DOUBLE              NOT NULL,
    "shipTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    "weaponTypeID"    BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charAttributes" (
    "charisma"     TINYINT(2) UNSIGNED NOT NULL,
    "intelligence" TINYINT(2) UNSIGNED NOT NULL,
    "memory"       TINYINT(2) UNSIGNED NOT NULL,
    "ownerID"      BIGINT(20) UNSIGNED NOT NULL,
    "perception"   TINYINT(2) UNSIGNED NOT NULL,
    "willpower"    TINYINT(2) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charBlueprints" (
    "ownerID"            BIGINT(20) UNSIGNED NOT NULL,
    "itemID"             BIGINT(20) UNSIGNED NOT NULL,
    "locationID"         BIGINT(20) UNSIGNED NOT NULL,
    "typeID"             BIGINT(20) UNSIGNED NOT NULL,
    "typeName"           CHAR(255)           NOT NULL,
    "flagID"             BIGINT(20) UNSIGNED NOT NULL,
    "quantity"           BIGINT(20)          NOT NULL,
    "timeEfficiency"     TINYINT(3) UNSIGNED NOT NULL,
    "materialEfficiency" TINYINT(3) UNSIGNED NOT NULL,
    "runs"               BIGINT(20)          NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCalendarEventAttendees" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50)            NOT NULL,
    "response"      CHAR(10)            NOT NULL,
    PRIMARY KEY ("ownerID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCertificates" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "certificateID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","certificateID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCharacterSheet" (
    "allianceID"        BIGINT(20) UNSIGNED          DEFAULT 0,
    "allianceName"      CHAR(50)                     DEFAULT '',
    "ancestry"          CHAR(24)            NOT NULL,
    "ancestryID"        BIGINT(20) UNSIGNED NOT NULL,
    "balance"           DECIMAL(17,2)       NOT NULL,
    "bloodLine"         CHAR(24)            NOT NULL,
    "bloodLineID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID"       BIGINT(20) UNSIGNED NOT NULL,
    "cloneJumpDate"     DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "corporationID"     BIGINT(20) UNSIGNED NOT NULL,
    "corporationName"   CHAR(50)            NOT NULL,
    "DoB"               DATETIME            NOT NULL,
    "factionID"         BIGINT(20) UNSIGNED          DEFAULT 0,
    "factionName"       CHAR(50)                     DEFAULT '',
    "freeRespecs"       INT(4) UNSIGNED     NOT NULL DEFAULT 0,
    "freeSkillPoints"   BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    "gender"            CHAR(6)             NOT NULL,
    "homeStationID"     BIGINT(20) UNSIGNED NOT NULL,
    "jumpActivation"    DATETIME            NOT NULL,
    "jumpFatigue"       DATETIME            NOT NULL,
    "jumpLastUpdate"    DATETIME            NOT NULL,
    "lastRespecDate"    DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "lastTimedRespec"   DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "name"              CHAR(50)            NOT NULL,
    "race"              CHAR(8)             NOT NULL,
    "remoteStationDate" DATETIME            NOT NULL,
    PRIMARY KEY ("characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charContactList" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(50)            NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "inWatchlist"   CHAR(5)             NOT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","contactID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charContactNotifications" (
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED NOT NULL,
    "senderName"     CHAR(50)            NOT NULL,
    "sentDate"       DATETIME            NOT NULL,
    "messageData"    TEXT,
    PRIMARY KEY ("ownerID","notificationID","senderID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charContracts" (
    "acceptorID"     BIGINT(20) UNSIGNED    NOT NULL,
    "assigneeID"     BIGINT(20) UNSIGNED    NOT NULL,
    "availability"   CHAR(8)                NOT NULL,
    "buyout"         DECIMAL(17,2)          NOT NULL,
    "collateral"     DECIMAL(17,2)          NOT NULL,
    "contractID"     BIGINT(20) UNSIGNED    NOT NULL,
    "dateAccepted"   DATETIME  DEFAULT NULL,
    "dateCompleted"  DATETIME  DEFAULT NULL,
    "dateExpired"    DATETIME               NOT NULL,
    "dateIssued"     DATETIME               NOT NULL,
    "endStationID"   BIGINT(20) UNSIGNED    NOT NULL,
    "forCorp"        TINYINT(1)             NOT NULL,
    "issuerCorpID"   BIGINT(20) UNSIGNED    NOT NULL,
    "issuerID"       BIGINT(20) UNSIGNED    NOT NULL,
    "numDays"        SMALLINT(3) UNSIGNED   NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED    NOT NULL,
    "price"          DECIMAL(17,2)          NOT NULL,
    "reward"         DECIMAL(17,2)          NOT NULL,
    "startStationID" BIGINT(20) UNSIGNED    NOT NULL,
    "status"         CHAR(24)               NOT NULL,
    "title"          CHAR(255) DEFAULT NULL,
    "type"           CHAR(15)               NOT NULL,
    "volume"         DECIMAL(20,4) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","contractID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charContractItems" (
    "contractID"  BIGINT(20) UNSIGNED NOT NULL,
    "included"    TINYINT(1) UNSIGNED NOT NULL,
    "quantity"    BIGINT(20) UNSIGNED NOT NULL,
    "rawQuantity" TINYINT(1),
    "recordID"    BIGINT(20) UNSIGNED NOT NULL,
    "singleton"   TINYINT(1) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("contractID","recordID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charContractBids" (
    "amount"     DECIMAL(17,2)       NOT NULL,
    "bidID"      BIGINT(20) UNSIGNED NOT NULL,
    "bidderID"   BIGINT(20) UNSIGNED NOT NULL,
    "contractID" BIGINT(20) UNSIGNED NOT NULL,
    "dateBid"    DATETIME            NOT NULL,
    PRIMARY KEY ("contractID","bidID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCorporateContactList" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(50)            NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","contactID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCorporationRoles" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "roleID"   BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCorporationRolesAtBase" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "roleID"   BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCorporationRolesAtHQ" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "roleID"   BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCorporationRolesAtOther" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "roleID"   BIGINT(20) UNSIGNED NOT NULL,
    "roleName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","roleID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charCorporationTitles" (
    "ownerID"   BIGINT(20) UNSIGNED NOT NULL,
    "titleID"   BIGINT(20) UNSIGNED NOT NULL,
    "titleName" CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","titleID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charFacWarStats" (
    "ownerID"                BIGINT(20) UNSIGNED NOT NULL,
    "factionID"              BIGINT(20) UNSIGNED NOT NULL,
    "factionName"            CHAR(50)            NOT NULL,
    "enlisted"               DATETIME            NOT NULL,
    "currentRank"            BIGINT(20) UNSIGNED NOT NULL,
    "highestRank"            BIGINT(20) UNSIGNED NOT NULL,
    "killsYesterday"         BIGINT(20) UNSIGNED NOT NULL,
    "killsLastWeek"          BIGINT(20) UNSIGNED NOT NULL,
    "killsTotal"             BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsYesterday" BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsLastWeek"  BIGINT(20) UNSIGNED NOT NULL,
    "victoryPointsTotal"     BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charFacWarStats" ADD INDEX "charFacWarStats1"  ("factionID");
CREATE TABLE "{database}"."{table_prefix}charImplants" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "typeID"   BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID","typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charIndustryJobs" (
    "ownerID"              BIGINT(20) UNSIGNED NOT NULL,
    "activityID"           TINYINT(2) UNSIGNED NOT NULL,
    "blueprintID"          BIGINT(20) UNSIGNED NOT NULL,
    "blueprintLocationID"  BIGINT(20) UNSIGNED NOT NULL,
    "blueprintTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    "blueprintTypeName"    CHAR(255)           NOT NULL,
    "completedCharacterID" BIGINT(20) UNSIGNED NOT NULL,
    "completedDate"        DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "cost"                 DECIMAL(17,2)       NOT NULL,
    "endDate"              DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "facilityID"           BIGINT(20) UNSIGNED NOT NULL,
    "installerID"          BIGINT(20) UNSIGNED NOT NULL,
    "installerName"        CHAR(50)                     DEFAULT NULL,
    "jobID"                BIGINT(20) UNSIGNED NOT NULL,
    "licensedRuns"         BIGINT(20) UNSIGNED NOT NULL,
    "outputLocationID"     BIGINT(20) UNSIGNED NOT NULL,
    "pauseDate"            DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "probability"          CHAR(24)                     DEFAULT NULL,
    "productTypeID"        BIGINT(20) UNSIGNED NOT NULL,
    "productTypeName"      CHAR(255)           NOT NULL,
    "runs"                 BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID"        BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName"      CHAR(255)           NOT NULL,
    "startDate"            DATETIME            NOT NULL,
    "stationID"            BIGINT(20) UNSIGNED NOT NULL,
    "status"               INT                 NOT NULL,
    "successfulRuns"       BIGINT(20) UNSIGNED          DEFAULT 0,
    "teamID"               BIGINT(20) UNSIGNED NOT NULL,
    "timeInSeconds"        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","jobID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charItems" (
    "flag"         SMALLINT(5) UNSIGNED NOT NULL,
    "killID"       BIGINT(20) UNSIGNED  NOT NULL,
    "lft"          BIGINT(20) UNSIGNED  NOT NULL,
    "lvl"          TINYINT(2) UNSIGNED  NOT NULL,
    "rgt"          BIGINT(20) UNSIGNED  NOT NULL,
    "qtyDropped"   BIGINT(20) UNSIGNED  NOT NULL,
    "qtyDestroyed" BIGINT(20) UNSIGNED  NOT NULL,
    "singleton"    SMALLINT(5) UNSIGNED NOT NULL,
    "typeID"       BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("killID","lft")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charJumpCloneImplants" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "typeName"    CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID","jumpCloneID","typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charJumpClones" (
    "jumpCloneID" BIGINT(20) UNSIGNED NOT NULL,
    "locationID"  BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "cloneName"   CHAR(50)            NOT NULL,
    PRIMARY KEY ("ownerID","jumpCloneID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charKillMails" (
    "killID"        BIGINT(20) UNSIGNED NOT NULL,
    "killTime"      DATETIME            NOT NULL,
    "moonID"        BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","killTime")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charMailBodies" (
    "ownerID"   BIGINT(20) UNSIGNED NOT NULL,
    "body"      TEXT,
    "messageID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","messageID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charMailingLists" (
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "displayName" CHAR(50)            NOT NULL,
    "listID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","listID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charMailMessages" (
    "ownerID"            BIGINT(20) UNSIGNED NOT NULL,
    "messageID"          BIGINT(20) UNSIGNED NOT NULL,
    "senderID"           BIGINT(20) UNSIGNED NOT NULL,
    "senderName"         CHAR(50)            DEFAULT NULL,
    "sentDate"           DATETIME            NOT NULL,
    "title"              CHAR(255)           DEFAULT NULL,
    "toCharacterIDs"     TEXT,
    "toCorpOrAllianceID" BIGINT(20) UNSIGNED DEFAULT '0',
    "toListID"           TEXT,
    "senderTypeID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ("ownerID","messageID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charMarketOrders" (
    "ownerID"      BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"   SMALLINT(4) UNSIGNED NOT NULL,
    "bid"          TINYINT(1)           NOT NULL,
    "charID"       BIGINT(20) UNSIGNED  NOT NULL,
    "duration"     SMALLINT(3) UNSIGNED NOT NULL,
    "escrow"       DECIMAL(17,2)        NOT NULL,
    "issued"       DATETIME             NOT NULL,
    "minVolume"    BIGINT(20) UNSIGNED  NOT NULL,
    "orderID"      BIGINT(20) UNSIGNED  NOT NULL,
    "orderState"   TINYINT(2) UNSIGNED  NOT NULL,
    "price"        DECIMAL(17,2)        NOT NULL,
    "range"        SMALLINT(6)          NOT NULL,
    "stationID"    BIGINT(20) UNSIGNED DEFAULT NULL,
    "typeID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    "volEntered"   BIGINT(20) UNSIGNED  NOT NULL,
    "volRemaining" BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID","orderID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charNotifications" (
    "ownerID"        BIGINT(20) UNSIGNED  NOT NULL,
    "notificationID" BIGINT(20) UNSIGNED  NOT NULL,
    "read"           TINYINT(1)           NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED  NOT NULL,
    "senderName"     CHAR(50) DEFAULT NULL,
    "sentDate"       DATETIME             NOT NULL,
    "typeID"         SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","notificationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charNotificationTexts" (
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "text"           TEXT,
    PRIMARY KEY ("ownerID","notificationID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charResearch" (
    "ownerID"           BIGINT(20) UNSIGNED NOT NULL,
    "agentID"           BIGINT(20) UNSIGNED NOT NULL,
    "pointsPerDay"      DOUBLE              NOT NULL,
    "skillTypeID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    "remainderPoints"   DOUBLE              NOT NULL,
    "researchStartDate" DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID","agentID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charSkillInTraining" (
    "currentTQTime"         DATETIME                     DEFAULT NULL,
    "offset"                TINYINT(2)          NOT NULL,
    "ownerID"               BIGINT(20) UNSIGNED NOT NULL,
    "skillInTraining"       TINYINT(1) UNSIGNED NOT NULL,
    "trainingDestinationSP" BIGINT(20) UNSIGNED NOT NULL,
    "trainingEndTime"       DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "trainingStartSP"       BIGINT(20) UNSIGNED NOT NULL,
    "trainingStartTime"     DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "trainingToLevel"       TINYINT(1) UNSIGNED NOT NULL,
    "trainingTypeID"        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charSkillQueue" (
    "endSP"         BIGINT(20) UNSIGNED NOT NULL,
    "endTime"       DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "level"         TINYINT(1) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "queuePosition" TINYINT(2) UNSIGNED NOT NULL,
    "startSP"       BIGINT(20) UNSIGNED NOT NULL,
    "startTime"     DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "typeID"        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","queuePosition")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charSkills" (
    "level"       TINYINT(1) UNSIGNED NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "skillpoints" BIGINT(20) UNSIGNED NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED NOT NULL,
    "published"   TINYINT(1)          NOT NULL,
    PRIMARY KEY ("ownerID","typeID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charStandingsFromAgents" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "fromID"   BIGINT(20) UNSIGNED NOT NULL,
    "fromName" CHAR(50)            NOT NULL,
    "standing" DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","fromID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charStandingsFromFactions" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "fromID"   BIGINT(20) UNSIGNED NOT NULL,
    "fromName" CHAR(50)            NOT NULL,
    "standing" DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","fromID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charStandingsFromNPCCorporations" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "fromID"   BIGINT(20) UNSIGNED NOT NULL,
    "fromName" CHAR(50)            NOT NULL,
    "standing" DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","fromID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charVictim" (
    "killID"          BIGINT(20) UNSIGNED NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50) DEFAULT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)            NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50) DEFAULT NULL,
    "damageTaken"     BIGINT(20) UNSIGNED NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50) DEFAULT NULL,
    "shipTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charWalletJournal" (
    "ownerID"       BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"    SMALLINT(4) UNSIGNED NOT NULL,
    "amount"        DECIMAL(17,2)        NOT NULL,
    "argID1"        BIGINT(20) UNSIGNED DEFAULT NULL,
    "argName1"      CHAR(255)           DEFAULT NULL,
    "balance"       DECIMAL(17,2)        NOT NULL,
    "date"          DATETIME             NOT NULL,
    "ownerID1"      BIGINT(20) UNSIGNED DEFAULT NULL,
    "ownerID2"      BIGINT(20) UNSIGNED DEFAULT NULL,
    "ownerName1"    CHAR(50)            DEFAULT NULL,
    "ownerName2"    CHAR(50)            DEFAULT NULL,
    "reason"        TEXT,
    "refID"         BIGINT(20) UNSIGNED  NOT NULL,
    "refTypeID"     SMALLINT(5) UNSIGNED NOT NULL,
    "taxAmount"     DECIMAL(17,2)        NOT NULL,
    "taxReceiverID" BIGINT(20) UNSIGNED DEFAULT '0',
    "owner1TypeID"  BIGINT(20) UNSIGNED DEFAULT NULL,
    "owner2TypeID"  BIGINT(20) UNSIGNED DEFAULT NULL,
    PRIMARY KEY ("ownerID","refID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}charWalletTransactions" (
    "ownerID"              BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"           SMALLINT(4) UNSIGNED NOT NULL,
    "clientID"             BIGINT(20) UNSIGNED           DEFAULT NULL,
    "clientName"           CHAR(50)                      DEFAULT NULL,
    "clientTypeID"         BIGINT(20) UNSIGNED           DEFAULT NULL,
    "journalTransactionID" BIGINT(20) UNSIGNED  NOT NULL,
    "price"                DECIMAL(17,2)        NOT NULL,
    "quantity"             BIGINT(20) UNSIGNED  NOT NULL,
    "stationID"            BIGINT(20) UNSIGNED           DEFAULT NULL,
    "stationName"          CHAR(255)                     DEFAULT NULL,
    "transactionDateTime"  DATETIME             NOT NULL,
    "transactionFor"       CHAR(12)             NOT NULL DEFAULT 'corporation',
    "transactionID"        BIGINT(20) UNSIGNED  NOT NULL,
    "transactionType"      CHAR(4)              NOT NULL DEFAULT 'sell',
    "typeID"               BIGINT(20) UNSIGNED  NOT NULL,
    "typeName"             CHAR(255)            NOT NULL,
    PRIMARY KEY ("ownerID","transactionID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
