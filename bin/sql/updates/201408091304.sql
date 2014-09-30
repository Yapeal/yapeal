CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpWalletTransactions',
                                      'stationName',
                                      'CHAR(255) NULL DEFAULT NULL');
