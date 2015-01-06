CREATE TABLE "{database}"."{table_prefix}corpAccountBalance" (
    "ownerID"    BIGINT(20) UNSIGNED  NOT NULL,
    "accountID"  BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey" SMALLINT(5) UNSIGNED NOT NULL,
    "balance"    DECIMAL(17,2)        NOT NULL,
    PRIMARY KEY ("ownerID","accountKey")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpAllianceContactList" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(50)            NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "standing"      DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","contactID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpAssetList" (
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
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAssetList" ADD INDEX "corpAssetList1"  ("lft");
ALTER TABLE "{database}"."{table_prefix}corpAssetList" ADD INDEX "corpAssetList2"  ("locationID");
CREATE TABLE "{database}"."{table_prefix}corpAttackers" (
    "killID"          BIGINT(20) UNSIGNED NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50)                     DEFAULT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)                     DEFAULT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)                     DEFAULT NULL,
    "damageDone"      BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50)            NOT NULL,
    "finalBlow"       TINYINT(1)          NOT NULL,
    "securityStatus"  DOUBLE              NOT NULL,
    "shipTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    "weaponTypeID"    BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","characterID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpBlueprints" (
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
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpCalendarEventAttendees" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID"   BIGINT(20) UNSIGNED NOT NULL,
    "characterName" CHAR(50)            NOT NULL,
    "response"      CHAR(10)            NOT NULL,
    PRIMARY KEY ("ownerID","characterID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpCombatSettings" (
    "ownerID"                 BIGINT(20) UNSIGNED   NOT NULL,
    "itemID"                  BIGINT(20) UNSIGNED   NOT NULL,
    "onAggressionEnabled"     TINYINT(1)            NOT NULL,
    "onCorporationWarEnabled" TINYINT(1)            NOT NULL,
    "onStandingDropStanding"  DECIMAL(5,2) UNSIGNED NOT NULL,
    "onStatusDropEnabled"     TINYINT(1)            NOT NULL,
    "onStatusDropStanding"    DECIMAL(5,2) UNSIGNED NOT NULL,
    "useStandingsFromOwnerID" BIGINT(20) UNSIGNED   NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpContainerLog" (
    "ownerID"          BIGINT(20) UNSIGNED  NOT NULL,
    "action"           CHAR(24)             NOT NULL,
    "actorID"          BIGINT(20) UNSIGNED  NOT NULL,
    "actorName"        CHAR(50)             NOT NULL,
    "flag"             SMALLINT(5) UNSIGNED NOT NULL,
    "itemID"           BIGINT(20) UNSIGNED  NOT NULL,
    "itemTypeID"       BIGINT(20) UNSIGNED  NOT NULL,
    "locationID"       BIGINT(20) UNSIGNED  NOT NULL,
    "logTime"          DATETIME             NOT NULL,
    "newConfiguration" SMALLINT(4) UNSIGNED NOT NULL,
    "oldConfiguration" SMALLINT(4) UNSIGNED NOT NULL,
    "passwordType"     CHAR(12)             NOT NULL,
    "quantity"         BIGINT(20) UNSIGNED  NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID","itemID","logTime")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpContracts" (
    "ownerID"        BIGINT(20) UNSIGNED    NOT NULL,
    "contractID"     BIGINT(20) UNSIGNED    NOT NULL,
    "issuerID"       BIGINT(20) UNSIGNED    NOT NULL,
    "issuerCorpID"   BIGINT(20) UNSIGNED    NOT NULL,
    "assigneeID"     BIGINT(20) UNSIGNED    NOT NULL,
    "acceptorID"     BIGINT(20) UNSIGNED    NOT NULL,
    "startStationID" BIGINT(20) UNSIGNED    NOT NULL,
    "endStationID"   BIGINT(20) UNSIGNED    NOT NULL,
    "type"           CHAR(15)               NOT NULL,
    "status"         CHAR(24)               NOT NULL,
    "title"          CHAR(50) DEFAULT NULL,
    "forCorp"        TINYINT(1)             NOT NULL,
    "availability"   CHAR(8)                NOT NULL,
    "dateIssued"     DATETIME               NOT NULL,
    "dateExpired"    DATETIME               NOT NULL,
    "dateAccepted"   DATETIME DEFAULT NULL,
    "numDays"        SMALLINT(3) UNSIGNED   NOT NULL,
    "dateCompleted"  DATETIME DEFAULT NULL,
    "price"          DECIMAL(17,2)          NOT NULL,
    "reward"         DECIMAL(17,2)          NOT NULL,
    "collateral"     DECIMAL(17,2)          NOT NULL,
    "buyout"         DECIMAL(17,2)          NOT NULL,
    "volume"         DECIMAL(18,4) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","contractID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpCorporateContactList" (
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(50)            NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED DEFAULT NULL,
    "standing"      DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","contactID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpCorporationSheet" (
    "allianceID"      BIGINT(20) UNSIGNED   NOT NULL DEFAULT 0,
    "allianceName"    CHAR(50)                       DEFAULT NULL,
    "ceoID"           BIGINT(20) UNSIGNED   NOT NULL,
    "ceoName"         CHAR(50)              NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED   NOT NULL,
    "corporationName" CHAR(50)              NOT NULL,
    "description"     TEXT,
    "factionID"       BIGINT(20) UNSIGNED   NOT NULL DEFAULT 0,
    "factionName"     CHAR(50)                       DEFAULT NULL,
    "memberCount"     BIGINT(20) UNSIGNED   NOT NULL,
    "memberLimit"     BIGINT(20) UNSIGNED   NOT NULL DEFAULT 0,
    "shares"          BIGINT(20) UNSIGNED   NOT NULL,
    "stationID"       BIGINT(20) UNSIGNED   NOT NULL,
    "stationName"     CHAR(255)             NOT NULL,
    "taxRate"         DECIMAL(5,2) UNSIGNED NOT NULL,
    "ticker"          CHAR(5)               NOT NULL,
    "url"             VARCHAR(255)                   DEFAULT NULL,
    PRIMARY KEY ("corporationID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpDivisions" (
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"  SMALLINT(4) UNSIGNED NOT NULL,
    "description" VARCHAR(255)         NOT NULL,
    PRIMARY KEY ("ownerID","accountKey")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpFacilities" (
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "facilityID"       BIGINT(20) UNSIGNED NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED NOT NULL,
    "typeName"         CHAR(255)           NOT NULL,
    "solarSystemID"    BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName"  CHAR(255)           NOT NULL,
    "regionID"         BIGINT(20) UNSIGNED NOT NULL,
    "regionName"       CHAR(255)           NOT NULL,
    "starbaseModifier" DECIMAL(17,2)       NOT NULL,
    "tax"              DECIMAL(17,2)       NOT NULL,
    PRIMARY KEY ("ownerID","facilityID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpFacWarStats" (
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
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpFacWarStats" ADD INDEX "corpFacWarStats1"  ("factionID");
CREATE TABLE "{database}"."{table_prefix}corpFuel" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "itemID"   BIGINT(20) UNSIGNED NOT NULL,
    "typeID"   BIGINT(20) UNSIGNED NOT NULL,
    "quantity" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","itemID","typeID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpGeneralSettings" (
    "ownerID"                 BIGINT(20) UNSIGNED  NOT NULL,
    "itemID"                  BIGINT(20) UNSIGNED  NOT NULL,
    "allowAllianceMembers"    TINYINT(1)           NOT NULL,
    "allowCorporationMembers" TINYINT(1)           NOT NULL,
    "deployFlags"             SMALLINT(5) UNSIGNED NOT NULL,
    "usageFlags"              SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpIndustryJobs" (
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
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpItems" (
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
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpKillMails" (
    "killID"        BIGINT(20) UNSIGNED NOT NULL,
    "killTime"      DATETIME            NOT NULL,
    "moonID"        BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","killTime")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpLogo" (
    "ownerID"   BIGINT(20) UNSIGNED  NOT NULL,
    "color1"    SMALLINT(5) UNSIGNED NOT NULL,
    "color2"    SMALLINT(5) UNSIGNED NOT NULL,
    "color3"    SMALLINT(5) UNSIGNED NOT NULL,
    "graphicID" BIGINT(20) UNSIGNED  NOT NULL,
    "shape1"    SMALLINT(5) UNSIGNED NOT NULL,
    "shape2"    SMALLINT(5) UNSIGNED NOT NULL,
    "shape3"    SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","graphicID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpMarketOrders" (
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
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpMedals" (
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "created"     DATETIME            NOT NULL,
    "creatorID"   BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT,
    "medalID"     BIGINT(20) UNSIGNED NOT NULL,
    "title"       VARCHAR(255)        NOT NULL,
    PRIMARY KEY ("ownerID","medalID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpMemberMedals" (
    "ownerID"     BIGINT(20) UNSIGNED NOT NULL,
    "medalID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    "issued"      DATETIME            NOT NULL,
    "issuerID"    BIGINT(20) UNSIGNED NOT NULL,
    "reason"      TEXT,
    "status"      CHAR(8)             NOT NULL,
    PRIMARY KEY ("ownerID","medalID","characterID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpMemberTracking" (
    "base"           CHAR(50)            DEFAULT NULL,
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
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMemberTracking" ADD INDEX "corpMemberTracking1"  ("ownerID");
CREATE TABLE "{database}"."{table_prefix}corpOutpostList" (
    "ownerID"                  BIGINT(20) UNSIGNED NOT NULL,
    "dockingCostPerShipVolume" DECIMAL(17,2)       NOT NULL,
    "officeRentalCost"         DECIMAL(17,2)       NOT NULL,
    "reprocessingEfficiency"   DECIMAL(5,4)        NOT NULL,
    "reprocessingStationTake"  DECIMAL(5,4)        NOT NULL,
    "solarSystemID"            BIGINT(20) UNSIGNED NOT NULL,
    "standingOwnerID"          BIGINT(20) UNSIGNED NOT NULL,
    "stationID"                BIGINT(20) UNSIGNED NOT NULL,
    "stationName"              CHAR(50)            NOT NULL,
    "stationTypeID"            BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","stationID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpOutpostServiceDetail" (
    "ownerID"                 BIGINT(20) UNSIGNED   NOT NULL,
    "stationID"               BIGINT(20) UNSIGNED   NOT NULL,
    "discountPerGoodStanding" DECIMAL(5,2)          NOT NULL,
    "minStanding"             DECIMAL(5,2) UNSIGNED NOT NULL,
    "serviceName"             CHAR(50)              NOT NULL,
    "surchargePerBadStanding" DECIMAL(5,2)          NOT NULL,
    PRIMARY KEY ("ownerID","stationID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpStandingsFromAgents" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "fromID"   BIGINT(20) UNSIGNED NOT NULL,
    "fromName" CHAR(50)            NOT NULL,
    "standing" DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","fromID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpStandingsFromFactions" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "fromID"   BIGINT(20) UNSIGNED NOT NULL,
    "fromName" CHAR(50)            NOT NULL,
    "standing" DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","fromID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpStandingsFromNPCCorporations" (
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "fromID"   BIGINT(20) UNSIGNED NOT NULL,
    "fromName" CHAR(50)            NOT NULL,
    "standing" DECIMAL(5,2)        NOT NULL,
    PRIMARY KEY ("ownerID","fromID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpStarbaseDetail" (
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "itemID"          BIGINT(20) UNSIGNED NOT NULL,
    "onlineTimestamp" DATETIME            NOT NULL,
    "state"           TINYINT(2) UNSIGNED NOT NULL,
    "stateTimestamp"  DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpStarbaseList" (
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "itemID"          BIGINT(20) UNSIGNED NOT NULL,
    "locationID"      BIGINT(20) UNSIGNED NOT NULL,
    "moonID"          BIGINT(20) UNSIGNED NOT NULL,
    "onlineTimestamp" DATETIME            NOT NULL,
    "standingOwnerID" BIGINT(20) UNSIGNED NOT NULL,
    "state"           TINYINT(2) UNSIGNED NOT NULL,
    "stateTimestamp"  DATETIME            NOT NULL,
    "typeID"          BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpVictim" (
    "killID"          BIGINT(20) UNSIGNED NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50)                     DEFAULT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)                     DEFAULT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)                     DEFAULT NULL,
    "damageTaken"     BIGINT(20) UNSIGNED NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    "factionName"     CHAR(50)            NOT NULL,
    "shipTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("killID","characterID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpWalletDivisions" (
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"  SMALLINT(4) UNSIGNED NOT NULL,
    "description" VARCHAR(255)         NOT NULL,
    PRIMARY KEY ("ownerID","accountKey")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpWalletJournal" (
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
    PRIMARY KEY ("ownerID","accountKey","refID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}corpWalletTransactions" (
    "ownerID"              BIGINT(20) UNSIGNED  NOT NULL,
    "accountKey"           SMALLINT(4) UNSIGNED NOT NULL,
    "characterID"          BIGINT(20) UNSIGNED           DEFAULT NULL,
    "characterName"        CHAR(50)                      DEFAULT NULL,
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
    PRIMARY KEY ("ownerID","accountKey","transactionID")
)
ENGINE ={ engine}
COLLATE utf8_unicode_ci;
