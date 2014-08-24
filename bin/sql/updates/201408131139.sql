SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE;
DROP PROCEDURE IF EXISTS "{database}"."AddOrModifyColumn";
CREATE PROCEDURE "{database}"."AddOrModifyColumn"(
    IN param_database_name  VARCHAR(100),
    IN param_table_name     VARCHAR(100),
    IN param_column_name    VARCHAR(100),
    IN param_column_details VARCHAR(255))
    BEGIN
        IF NOT EXISTS(SELECT NULL
                      FROM "information_schema"."COLUMNS"
                      WHERE
                          "COLUMN_NAME" = param_column_name AND
                          "TABLE_NAME" = param_table_name AND
                          "table_schema" = param_database_name)
        THEN
/* Create the full statement to execute */
            SET @StatementToExecute = concat('ALTER TABLE "',
                                             param_database_name, '"."',
                                             param_table_name,
                                             '" ADD COLUMN "',
                                             param_column_name, '" ',
                                             param_column_details) $$
/* Prepare and execute the statement that was built */
            PREPARE DynamicStatement FROM @StatementToExecute$$
            EXECUTE DynamicStatement$$
/* Cleanup the prepared statement */
            DEALLOCATE PREPARE DynamicStatement$$
        ELSE
/* Create the full statement to execute */
            SET @StatementToExecute = concat('ALTER TABLE "',
                                             param_database_name, '"."',
                                             param_table_name,
                                             '" MODIFY COLUMN "',
                                             param_column_name, '" ',
                                             param_column_details) $$
/* Prepare and execute the statement that was built */
            PREPARE DynamicStatement FROM @StatementToExecute$$
            EXECUTE DynamicStatement$$
/* Cleanup the prepared statement */
            DEALLOCATE PREPARE DynamicStatement$$
        END IF$$
    END;
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}accountCharacters',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charAttackers',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCalendarEventAttendees',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'name', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charStandingsFromAgents',
                                      'fromName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}', '{table_prefix}charVictim',
                                      'characterName', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpAttackers',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCalendarEventAttendees',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpContainerLog',
                                      'actorName', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'stationName',
                                      'CHAR(255) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpStandingsFromAgents',
                                      'fromName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}', '{table_prefix}corpVictim',
                                      'characterName', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpWalletTransactions',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharacterInfo',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersKillsLastWeek',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersKillsTotal',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersKillsYesterday',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersVictoryPointsLastWeek',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersVictoryPointsTotal',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersVictoryPointsYesterday',
                                      'characterName', 'CHAR(50) NOT NULL');
DROP PROCEDURE IF EXISTS "{database}"."AddOrModifyColumn";
