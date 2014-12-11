DROP PROCEDURE IF EXISTS "{database}"."DropColumn";
CREATE PROCEDURE "{database}"."DropColumn"(
    IN param_database_name VARCHAR(100),
    IN param_table_name    VARCHAR(100),
    IN param_column_name   VARCHAR(100))
    BEGIN
        IF EXISTS(SELECT
                      NULL
                  FROM
                      "information_schema"."COLUMNS"
                  WHERE
                      "COLUMN_NAME" = param_column_name AND
                      "TABLE_NAME" = param_table_name AND
                      "table_schema" = param_database_name)
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
