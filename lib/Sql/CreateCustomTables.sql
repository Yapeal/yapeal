SET SESSION SQL_MODE = 'ANSI,TRADITIONAL';
SET SESSION TIME_ZONE = '+00:00';
SET NAMES UTF8;
START TRANSACTION;
REPLACE INTO "{database}"."{table_prefix}utilRegisteredKey" ("activeAPIMask","isActive","keyID","vCode")
VALUES
    (
        268435455,
        1,
        3596816,
        'xrD77kiIk4GOkc0bs55mjT12qnjejT558Vilh7KJ701e1u7gMIgfIngUNj3oShRs'),
    (
        268435455,
        1,
        52,
        'I0Z2knzpCbwkTMzkX9IjHJQz6kdkoOPe5wnA4Kv1TyYdcgg4BJpjpoCYufA7t28G'),
    (8388608,1,1156,'abc123'),
    (
        67108863,
        1,
        3468717,
        'OdLQa0PVtpsvh3sWLWoWv36PfDSnWS1efcnW085bUWDShWUivTGzVUyGa7VyHNHb'),
    (
        67108863,
        1,
        1837,
        'm90SLg1WUnDf3v2S6zhTl0vaFOpbmV8O6wAi9Yj14BhKI2jj2gnyKaaxHHE04WYG');
COMMIT;
