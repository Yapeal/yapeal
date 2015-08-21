CREATE TABLE "{database}"."{table_prefix}accountKeyBridge" (
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID","characterID")
)
ENGINE = { engine}
COLLATE utf8_unicode_ci;
ALTER TABLE "{database}"."{table_prefix}accountKeyBridge" ADD UNIQUE INDEX "accountKeyBridge1"  ("characterID","keyID");
