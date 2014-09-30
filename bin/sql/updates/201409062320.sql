CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpAccountBalance',
                                      'accountKey',
                                      'SMALLINT(5) UNSIGNED NOT NULL');
