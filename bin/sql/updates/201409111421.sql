CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'factionName',
                                      'CHAR(50) COLLATE utf8_unicode_ci DEFAULT NULL AFTER "factionID"');
