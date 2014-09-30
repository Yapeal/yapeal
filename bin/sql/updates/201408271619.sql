CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCorporationTitles',
                                      'titleName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charMailMessages',
                                      'title',
                                      'CHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charContracts',
                                      'title',
                                      'CHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL');
