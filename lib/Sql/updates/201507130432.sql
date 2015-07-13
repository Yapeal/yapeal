-- Sql/update/201507130432.sql
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveConquerableStationList',
                                      'x',
                                      'BIGINT(20) NOT NULL AFTER "stationTypeID"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveConquerableStationList',
                                      'y',
                                      'BIGINT(20) NOT NULL AFTER "x"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveConquerableStationList',
                                      'z',
                                      'BIGINT(20) NOT NULL AFTER "y"');
