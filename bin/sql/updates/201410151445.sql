CREATE TABLE IF NOT EXISTS "{database}"."{table_prefix}accountMultiCharacterTraining" (
    "trainingEnd" DATETIME            NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID", "trainingEnd")
)
    ENGINE =InnoDB
    DEFAULT CHARSET =ascii;
