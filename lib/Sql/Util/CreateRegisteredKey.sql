CREATE TABLE "{database}"."{table_prefix}utilRegisteredKey" (
    "active"        TINYINT(1)          DEFAULT NULL,
    "activeAPIMask" BIGINT(20) UNSIGNED DEFAULT NULL,
    "keyID"         BIGINT(20) UNSIGNED NOT NULL,
    "vCode"         VARCHAR(64)         NOT NULL,
    PRIMARY KEY ("keyID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
INSERT INTO "{database}"."{table_prefix}utilRegisteredKey" ("activeAPIMask","active","keyID","vCode")
VALUES
    (8388608,1,1156,'abc123');
