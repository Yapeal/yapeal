-- sql/updates/201412101858.sql
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charAllianceContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCorporateContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpAllianceContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporateContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
-- sql/updates/201412102009.sql
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'memberCount',
                                      'BIGINT(20) UNSIGNED NOT NULL AFTER "factionName"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'memberLimit',
                                      'BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER "memberCount"');
-- sql/updates/201412112108.sql
DROP PROCEDURE IF EXISTS "{database}"."DropColumn";
CREATE PROCEDURE "{database}"."DropColumn"(
    IN param_database_name VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    IN param_table_name    VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    IN param_column_name   VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci)
    BEGIN
        IF EXISTS(SELECT
                      NULL
                  FROM
                      "information_schema"."COLUMNS"
                  WHERE
                      "COLUMN_NAME" COLLATE utf8_unicode_ci = param_column_name AND
                      "TABLE_NAME" COLLATE utf8_unicode_ci = param_table_name AND
                      "table_schema" COLLATE utf8_unicode_ci = param_database_name)
        THEN
/* Create the full statement to execute */
            SET @StatementToExecute = concat('ALTER TABLE "',
                                             param_database_name,'"."',
                                             param_table_name,
                                             '" DROP COLUMN "',
                                             param_column_name,'"') $$
/* Prepare and execute the statement that was built */
            PREPARE DynamicStatement FROM @StatementToExecute$$
            EXECUTE DynamicStatement$$
/* Cleanup the prepared statement */
            DEALLOCATE PREPARE DynamicStatement$$
        END IF$$
    END;
CALL "{database}"."DropColumn"('{database}',
                               '{table_prefix}charCharacterSheet',
                               'cloneName');
CALL "{database}"."DropColumn"('{database}',
                               '{table_prefix}charCharacterSheet',
                               'cloneSkillPoints');
CALL "{database}"."DropColumn"('{database}',
                               '{table_prefix}charCharacterSheet',
                               'cloneTypeID');
DROP PROCEDURE IF EXISTS "{database}"."DropColumn";
-- sql/updates/201412130159.sql
ALTER DATABASE "{database}"
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_unicode_ci;
/* Convert existing columns */
ALTER TABLE "{database}"."{table_prefix}accountAccountStatus" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountAPIKeyInfo" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountCharacters" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountKeyBridge" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountMultiCharacterTraining" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}apiCallGroups" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}apiCalls" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAccountBalance" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAllianceContactList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAssetList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAttackers" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAttributes" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charBlueprints" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCalendarEventAttendees" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCertificates" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCharacterSheet" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContactList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContactNotifications" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContractBids" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContractItems" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContracts" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporateContactList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRoles" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRolesAtBase" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRolesAtHQ" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRolesAtOther" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationTitles" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charFacWarStats" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charImplants" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charIndustryJobs" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charItems" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charJumpCloneImplants" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charJumpClones" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charKillMails" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMailBodies" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMailingLists" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMailMessages" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMarketOrders" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charNotifications" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charNotificationTexts" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charResearch" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charSkillInTraining" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charSkillQueue" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charSkills" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charStandingsFromAgents" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charStandingsFromFactions" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charStandingsFromNPCCorporations" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charVictim" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charWalletJournal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charWalletTransactions" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAccountBalance" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAllianceContactList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAssetList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAttackers" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpBlueprints" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCalendarEventAttendees" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCombatSettings" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpContainerLog" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpContracts" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCorporateContactList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCorporationSheet" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpDivisions" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpFacilities" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpFacWarStats" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpFuel" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpGeneralSettings" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpIndustryJobs" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpItems" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpKillMails" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpLogo" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMarketOrders" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMedals" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMemberMedals" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMemberTracking" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpOutpostList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpOutpostServiceDetail" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStandingsFromAgents" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStandingsFromFactions" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStandingsFromNPCCorporations" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStarbaseDetail" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStarbaseList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpVictim" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpWalletDivisions" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpWalletJournal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpWalletTransactions" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveAllianceList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharacterInfo" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersKillsLastWeek" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersKillsTotal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersKillsYesterday" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsLastWeek" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsTotal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsYesterday" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveConquerableStationList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsKillsLastWeek" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsKillsTotal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsKillsYesterday" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsLastWeek" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsTotal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsYesterday" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveEmploymentHistory" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveErrorList" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactions" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsKillsLastWeek" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsKillsTotal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsKillsYesterday" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsLastWeek" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsTotal" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsYesterday" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionWars" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFacWarStats" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveMemberCorporations" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveRefTypes" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveTypeName" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapFacWarSystems" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapJumps" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapKills" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapSovereignty" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}serverServerStatus" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilCachedUntil" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilDatabaseVersion" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilEveApi" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilRegisteredKey" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilRegisteredUploader" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilUploadDestination" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilXmlCache" CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
/* Convert Tables */
ALTER TABLE "{database}"."{table_prefix}accountAccountStatus" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountAPIKeyInfo" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountCharacters" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountKeyBridge" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountMultiCharacterTraining" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}apiCallGroups" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}apiCalls" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAccountBalance" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAllianceContactList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAssetList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAttackers" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charAttributes" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charBlueprints" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCalendarEventAttendees" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCertificates" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCharacterSheet" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContactList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContactNotifications" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContractBids" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContractItems" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charContracts" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporateContactList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRoles" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRolesAtBase" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRolesAtHQ" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationRolesAtOther" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charCorporationTitles" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charFacWarStats" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charImplants" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charIndustryJobs" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charItems" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charJumpCloneImplants" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charJumpClones" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charKillMails" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMailBodies" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMailingLists" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMailMessages" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charMarketOrders" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charNotifications" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charNotificationTexts" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charResearch" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charSkillInTraining" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charSkillQueue" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charSkills" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charStandingsFromAgents" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charStandingsFromFactions" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charStandingsFromNPCCorporations" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charVictim" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charWalletJournal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}charWalletTransactions" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAccountBalance" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAllianceContactList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAssetList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpAttackers" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpBlueprints" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCalendarEventAttendees" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCombatSettings" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpContainerLog" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpContracts" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCorporateContactList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpCorporationSheet" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpDivisions" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpFacilities" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpFacWarStats" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpFuel" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpGeneralSettings" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpIndustryJobs" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpItems" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpKillMails" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpLogo" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMarketOrders" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMedals" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMemberMedals" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpMemberTracking" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpOutpostList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpOutpostServiceDetail" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStandingsFromAgents" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStandingsFromFactions" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStandingsFromNPCCorporations" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStarbaseDetail" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpStarbaseList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpVictim" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpWalletDivisions" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpWalletJournal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}corpWalletTransactions" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveAllianceList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharacterInfo" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersKillsLastWeek" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersKillsTotal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersKillsYesterday" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsLastWeek" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsTotal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCharactersVictoryPointsYesterday" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveConquerableStationList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsKillsLastWeek" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsKillsTotal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsKillsYesterday" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsLastWeek" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsTotal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveCorporationsVictoryPointsYesterday" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveEmploymentHistory" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveErrorList" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactions" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsKillsLastWeek" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsKillsTotal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsKillsYesterday" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsLastWeek" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsTotal" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionsVictoryPointsYesterday" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFactionWars" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveFacWarStats" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveMemberCorporations" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveRefTypes" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}eveTypeName" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapFacWarSystems" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapJumps" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapKills" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}mapSovereignty" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}serverServerStatus" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilCachedUntil" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilDatabaseVersion" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilEveApi" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilRegisteredKey" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilRegisteredUploader" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilUploadDestination" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}utilXmlCache" CHARACTER SET utf8 COLLATE utf8_unicode_ci;
