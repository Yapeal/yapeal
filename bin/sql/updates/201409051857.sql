CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharacterInfo',
                                      'shipName',
                                      'CHAR(255) COLLATE utf8_unicode_ci DEFAULT \'\'');
