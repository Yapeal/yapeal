CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveEmploymentHistory',
                                      'corporationName',
                                      'CHAR(50) NULL DEFAULT NULL AFTER "corporationID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveTypeName',
                                      'typeName',
                                      'CHAR(255) NOT NULL');
