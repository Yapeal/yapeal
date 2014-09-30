CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charIndustryJobs',
                                      'installerName', 'CHAR(50) DEFAULT NULL');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpIndustryJobs',
                                      'installerName', 'CHAR(50) DEFAULT NULL');
