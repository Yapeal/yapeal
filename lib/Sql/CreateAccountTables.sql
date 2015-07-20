CREATE TABLE "{database}"."{table_prefix}accountAccountStatus" (
    "keyID"        BIGINT(20) UNSIGNED NOT NULL,
    "createDate"   DATETIME            NOT NULL,
    "logonCount"   BIGINT(20) UNSIGNED NOT NULL,
    "logonMinutes" BIGINT(20) UNSIGNED NOT NULL,
    "paidUntil"    DATETIME            NOT NULL,
    PRIMARY KEY ("keyID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}accountAPIKeyInfo" (
    "keyID"      BIGINT(20) UNSIGNED                       NOT NULL,
    "accessMask" BIGINT(20) UNSIGNED                       NOT NULL,
    "expires"    DATETIME                                  NOT NULL DEFAULT '2038-01-19 03:14:07',
    "type"       ENUM('Account','Character','Corporation') NOT NULL,
    PRIMARY KEY ("keyID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountAPIKeyInfo" ADD INDEX "accountAPIKeyInfo1"  ("type");
CREATE TABLE "{database}"."{table_prefix}accountCharacters" (
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(50)            NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(50)            NOT NULL,
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(50)            NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "factionName"     CHAR(50)            NOT NULL,
    PRIMARY KEY ("characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountCharacters" ADD INDEX "accountCharacters1"  ("corporationID");
CREATE TABLE "{database}"."{table_prefix}accountKeyBridge" (
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountKeyBridge" ADD UNIQUE INDEX "accountKeyBridge1"  ("characterID","keyID");
CREATE TABLE "{database}"."{table_prefix}accountMultiCharacterTraining" (
    "trainingEnd" DATETIME            NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID","trainingEnd")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
CREATE TABLE "{database}"."{table_prefix}accountOffers" (
    "from"        CHAR(20)            NOT NULL,
    "ISK"         DECIMAL(17,2)       NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    "offeredDate" DATETIME            NOT NULL,
    "offerID"     BIGINT(20) UNSIGNED NOT NULL,
    "to"          CHAR(20)            NOT NULL,
    PRIMARY KEY ("keyID","offerID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
