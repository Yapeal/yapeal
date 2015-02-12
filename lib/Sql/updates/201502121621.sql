-- Sql/update/201502121621.sql
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpContracts',
                                      'volume',
                                      'DECIMAL(20,4) UNSIGNED NOT NULL AFTER "type"');
