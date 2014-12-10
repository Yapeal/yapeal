CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charAllianceContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCorporateContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpAllianceContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporateContactList',
                                      'contactName',
                                      'CHAR(50) COLLATE utf8_unicode_ci NOT NULL AFTER "contactID"');
