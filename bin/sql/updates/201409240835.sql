CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charContracts',
                                      'volume',
                                      'DECIMAL(18, 4) UNSIGNED NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpContracts',
                                      'volume',
                                      'DECIMAL(18, 4) UNSIGNED NOT NULL');
