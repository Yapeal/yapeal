CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charWalletJournal',
                                      'argName1',
                                      'CHAR(255) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charWalletJournal',
                                      'ownerName1',
                                      'CHAR(50) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charWalletJournal',
                                      'ownerName2',
                                      'CHAR(50) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpWalletJournal',
                                      'argName1',
                                      'CHAR(255) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpWalletJournal',
                                      'ownerName1',
                                      'CHAR(50) COLLATE utf8_unicode_ci');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpWalletJournal',
                                      'ownerName2',
                                      'CHAR(50) COLLATE utf8_unicode_ci');
