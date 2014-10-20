CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}charIndustryJobs',
                                      'successfulRuns',
                                      'BIGINT(20) UNSIGNED DEFAULT 0 AFTER "status"');
CALL "{database}"."AddOrModifyColumn"('{database}',
                                      '{table_prefix}corpIndustryJobs',
                                      'successfulRuns',
                                      'BIGINT(20) UNSIGNED DEFAULT 0 AFTER "status"');
