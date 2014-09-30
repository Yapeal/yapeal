CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpMemberTracking',
                                      'locationID',
                                      'BIGINT(20) UNSIGNED DEFAULT 0');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpMemberTracking',
                                      'name', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpMemberTracking',
                                      'shipTypeID', 'BIGINT(20) DEFAULT 0');
