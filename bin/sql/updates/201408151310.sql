CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charWalletTransactions',
                                      'typeName',
                                      'CHAR(255) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpWalletTransactions',
                                      'typeName',
                                      'CHAR(255) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}', '{table_prefix}eveTypeName',
                                      'typeName', 'CHAR(255) NOT NULL');
