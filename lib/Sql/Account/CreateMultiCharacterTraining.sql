CREATE TABLE "{database}"."{table_prefix}accountMultiCharacterTraining" (
    "trainingEnd" DATETIME            NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID","trainingEnd")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
