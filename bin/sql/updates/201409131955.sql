CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'ceoName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "ceoID"');
