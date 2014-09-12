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
                      FROM
                          "information_schema"."COLUMNS"
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
                                      '{table_prefix}charContactList',
                                      'inWatchlist',
                                      'CHAR(5) NOT NULL');
DROP PROCEDURE IF EXISTS "{database}"."AddOrModifyColumn";