CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'memberCount',
                                      'BIGINT(20) UNSIGNED NOT NULL AFTER "factionName"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'memberLimit',
                                      'BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER "memberCount"');
