CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}accountCharacters',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charAttackers',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCalendarEventAttendees',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charCharacterSheet',
                                      'name', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charStandingsFromAgents',
                                      'fromName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}', '{table_prefix}charVictim',
                                      'characterName', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpAttackers',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCalendarEventAttendees',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpContainerLog',
                                      'actorName', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpCorporationSheet',
                                      'stationName',
                                      'CHAR(255) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpStandingsFromAgents',
                                      'fromName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}', '{table_prefix}corpVictim',
                                      'characterName', 'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpWalletTransactions',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharacterInfo',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersKillsLastWeek',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersKillsTotal',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersKillsYesterday',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersVictoryPointsLastWeek',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersVictoryPointsTotal',
                                      'characterName',
                                      'CHAR(50) NOT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}eveCharactersVictoryPointsYesterday',
                                      'characterName', 'CHAR(50) NOT NULL');
