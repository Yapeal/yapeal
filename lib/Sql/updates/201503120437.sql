-- Sql/update/201503120437.sql
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpMemberTracking',
                                      'base',
                                      'CHAR(255) DEFAULT NULL');
