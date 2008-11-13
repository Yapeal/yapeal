/*============================================================================*/
/* DDL SCRIPT                                                                 */
/*============================================================================*/
/* Title:      Yapeal_char                                                    */
/* Platform:   MySQL 5                                                        */
/* Version:    Concept                                                        */
/* Generated:  Saturday, October 25, 2008                                     */
/*============================================================================*/
/*
 * LICENSE: This file is part of Yapeal.
 *
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 */

DROP TABLE IF EXISTS charactersheet CASCADE;

DROP TABLE IF EXISTS accountbalance CASCADE;

DROP TABLE IF EXISTS assetlist CASCADE;

DROP TABLE IF EXISTS industryjobs CASCADE;

DROP TABLE IF EXISTS marketorders CASCADE;

DROP TABLE IF EXISTS skills CASCADE;

DROP TABLE IF EXISTS walletjournal CASCADE;

DROP TABLE IF EXISTS wallettransactions CASCADE;

/*============================================================================*/
/* Tables                                                                     */
/*============================================================================*/
CREATE TABLE charactersheet (
    characterid BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    race VARCHAR(255) NOT NULL,
    bloodline VARCHAR(255) NOT NULL,
    gender VARCHAR(255) NOT NULL,
    corporationname VARCHAR(255) NOT NULL,
    corporationid BIGINT UNSIGNED NOT NULL,
    balance DECIMAL(17,2) NOT NULL,
    charisma SMALLINT UNSIGNED NOT NULL,
    intelligence SMALLINT UNSIGNED NOT NULL,
    memory SMALLINT UNSIGNED NOT NULL,
    perception SMALLINT UNSIGNED NOT NULL,
    willpower SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT PK_charactersheet PRIMARY KEY (characterid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE accountbalance (
    ownerid BIGINT UNSIGNED NOT NULL,
    accountid BIGINT UNSIGNED NOT NULL,
    accountkey SMALLINT UNSIGNED NOT NULL,
    balance DECIMAL(17,2) NOT NULL,
    CONSTRAINT PK_accountbalance PRIMARY KEY (ownerid, accountid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE assetlist (
    ownerid BIGINT UNSIGNED NOT NULL,
    itemid BIGINT UNSIGNED NOT NULL,
    locationid BIGINT UNSIGNED NOT NULL,
    typeid BIGINT UNSIGNED NOT NULL,
    quantity BIGINT UNSIGNED NOT NULL,
    flag SMALLINT UNSIGNED NOT NULL,
    singleton BOOL NOT NULL,
    id BIGINT UNSIGNED,
    rootid BIGINT UNSIGNED,
    lft BIGINT UNSIGNED,
    rgt BIGINT UNSIGNED,
    norder BIGINT UNSIGNED,
    lvl BIGINT UNSIGNED,
    CONSTRAINT PK_assetlist PRIMARY KEY (ownerid, itemid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE industryjobs (
    ownerid BIGINT UNSIGNED NOT NULL,
    jobid BIGINT UNSIGNED NOT NULL,
    assemblylineid BIGINT UNSIGNED NOT NULL,
    containerid BIGINT UNSIGNED NOT NULL,
    installeditemid BIGINT UNSIGNED NOT NULL,
    installeditemlocationid BIGINT UNSIGNED NOT NULL,
    installeditemquantity BIGINT UNSIGNED NOT NULL,
    installeditemproductivitylevel INT NOT NULL,
    installeditemmateriallevel INT NOT NULL,
    installeditemlicensedproductionrunsremaining BIGINT UNSIGNED NOT NULL,
    outputlocationid BIGINT UNSIGNED NOT NULL,
    installerid BIGINT UNSIGNED NOT NULL,
    runs BIGINT UNSIGNED NOT NULL,
    licensedproductionruns BIGINT UNSIGNED NOT NULL,
    installedinsolarsystemid BIGINT UNSIGNED NOT NULL,
    containerlocationid BIGINT UNSIGNED NOT NULL,
    materialmultiplier DECIMAL(17,2) NOT NULL,
    charmaterialmultiplier DECIMAL(17,2) NOT NULL,
    timemultiplier DECIMAL(17,2) NOT NULL,
    chartimemultiplier DECIMAL(17,2) NOT NULL,
    installeditemtypeid BIGINT UNSIGNED NOT NULL,
    outputtypeid BIGINT UNSIGNED NOT NULL,
    containertypeid BIGINT UNSIGNED NOT NULL,
    installeditemcopy BIGINT UNSIGNED NOT NULL,
    completed SMALLINT UNSIGNED NOT NULL,
    completedsuccessfully SMALLINT UNSIGNED NOT NULL,
    installeditemflag SMALLINT UNSIGNED NOT NULL,
    outputflag SMALLINT UNSIGNED NOT NULL,
    activityid SMALLINT UNSIGNED NOT NULL,
    completedstatus SMALLINT UNSIGNED NOT NULL,
    installtime DATETIME NOT NULL,
    beginproductiontime DATETIME NOT NULL,
    endproductiontime DATETIME NOT NULL,
    pauseproductiontime DATETIME NOT NULL,
    CONSTRAINT PK_industryjobs PRIMARY KEY (ownerid, jobid),
    CONSTRAINT UC_industryjobs_1 UNIQUE (jobid, installtime)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE marketorders (
    ownerid BIGINT UNSIGNED NOT NULL,
    orderid BIGINT UNSIGNED NOT NULL,
    charid BIGINT UNSIGNED NOT NULL,
    stationid BIGINT UNSIGNED NOT NULL,
    volentered BIGINT UNSIGNED NOT NULL,
    volremaining BIGINT UNSIGNED NOT NULL,
    minvolume BIGINT UNSIGNED NOT NULL,
    orderstate TINYINT UNSIGNED NOT NULL,
    typeid BIGINT UNSIGNED NOT NULL,
    range SMALLINT NOT NULL,
    accountkey SMALLINT UNSIGNED NOT NULL,
    duration SMALLINT UNSIGNED NOT NULL,
    escrow DECIMAL(17,2) NOT NULL,
    price DECIMAL(17,2) NOT NULL,
    bid BOOL NOT NULL,
    issued DATETIME NOT NULL,
    changed TIMESTAMP,
    CONSTRAINT PK_marketorders PRIMARY KEY (ownerid, orderid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE skills (
    ownerid BIGINT UNSIGNED NOT NULL,
    typeid BIGINT UNSIGNED NOT NULL,
    level SMALLINT UNSIGNED NOT NULL,
    skillpoints BIGINT UNSIGNED NOT NULL,
    unpublished BOOL NOT NULL DEFAULT FALSE,
    CONSTRAINT PK_skills PRIMARY KEY (ownerid, typeid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE walletjournal (
    ownerid BIGINT UNSIGNED NOT NULL,
    refid BIGINT UNSIGNED NOT NULL,
    date DATETIME NOT NULL,
    reftypeid TINYINT UNSIGNED NOT NULL,
    ownername1 VARCHAR(255) NOT NULL,
    ownerid1 BIGINT UNSIGNED NOT NULL,
    ownername2 VARCHAR(255) NOT NULL,
    ownerid2 BIGINT UNSIGNED NOT NULL,
    argname1 VARCHAR(255) NOT NULL,
    argid1 BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(17,2) NOT NULL,
    balance DECIMAL(17,2) NOT NULL,
    reason TEXT NOT NULL,
    account SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT PK_walletjournal PRIMARY KEY (ownerid, refid),
    CONSTRAINT UC_walletjournal_1 UNIQUE (date, refid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE wallettransactions (
    ownerid BIGINT UNSIGNED NOT NULL,
    transactionid BIGINT UNSIGNED NOT NULL,
    transactiondatetime DATETIME NOT NULL,
    quantity BIGINT UNSIGNED NOT NULL,
    typename VARCHAR(255) NOT NULL,
    typeid BIGINT UNSIGNED NOT NULL,
    price DECIMAL(17,2) NOT NULL,
    clientid BIGINT UNSIGNED NOT NULL,
    clientname VARCHAR(255) NOT NULL,
    charactername VARCHAR(255) NOT NULL,
    stationid BIGINT UNSIGNED NOT NULL,
    stationname VARCHAR(255) NOT NULL,
    transactiontype ENUM('sell','buy') NOT NULL DEFAULT 'sell',
    transactionfor ENUM('corporation','personal') NOT NULL DEFAULT 'corporation',
    account SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT PK_wallettransactions PRIMARY KEY (ownerid, transactionid),
    CONSTRAINT UC_wallettransactions_1 UNIQUE (transactionid, transactiondatetime)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

/*============================================================================*/
/* Foreign keys                                                               */
/*============================================================================*/
ALTER TABLE accountbalance
    ADD CONSTRAINT FK_accountbalance
    FOREIGN KEY (ownerid) REFERENCES charactersheet (characterid)
    ON DELETE CASCADE;

ALTER TABLE assetlist
    ADD CONSTRAINT FK_assetlist
    FOREIGN KEY (ownerid) REFERENCES charactersheet (characterid)
    ON DELETE CASCADE;

ALTER TABLE industryjobs
    ADD CONSTRAINT FK_industryjobs
    FOREIGN KEY (ownerid) REFERENCES charactersheet (characterid)
    ON DELETE CASCADE;

ALTER TABLE marketorders
    ADD CONSTRAINT FK_marketorders
    FOREIGN KEY (ownerid) REFERENCES charactersheet (characterid)
    ON DELETE CASCADE;

ALTER TABLE skills
    ADD CONSTRAINT FK_NewTable
    FOREIGN KEY (ownerid) REFERENCES charactersheet (characterid)
    ON DELETE CASCADE;

ALTER TABLE walletjournal
    ADD CONSTRAINT FK_walletjournal
    FOREIGN KEY (ownerid) REFERENCES charactersheet (characterid)
    ON DELETE CASCADE;

ALTER TABLE wallettransactions
    ADD CONSTRAINT FK_wallettransactions
    FOREIGN KEY (ownerid) REFERENCES charactersheet (characterid)
    ON DELETE CASCADE;

/*============================================================================*/
/* Indexes                                                                    */
/*============================================================================*/
CREATE INDEX IDX_charactersheet_1 ON charactersheet (corporationid);

CREATE INDEX IDX_FK_accountbalance ON accountbalance (ownerid);

CREATE INDEX IDX_assetlist_1 ON assetlist (typeid);

CREATE INDEX IDX_assetlist_2 ON assetlist (flag);

CREATE INDEX IDX_assetlist_3 ON assetlist (locationid);

CREATE INDEX IDX_FK_assetlist ON assetlist (ownerid);

CREATE INDEX IDX_FK_industryjobs ON industryjobs (ownerid);

CREATE INDEX IDX_industryjobs_1 ON industryjobs (activityid);

CREATE INDEX IDX_industryjobs_2 ON industryjobs (installeditemtypeid);

CREATE INDEX IDX_FK_marketorders ON marketorders (ownerid);

CREATE INDEX IDX_marketorders_1 ON marketorders (accountkey);

CREATE INDEX IDX_marketorders_2 ON marketorders (orderstate);

CREATE INDEX IDX_marketorders_3 ON marketorders (typeid);

CREATE INDEX IDX_FK_NewTable ON skills (ownerid);

CREATE INDEX IDX_skills_1 ON skills (typeid);

CREATE INDEX IDX_FK_walletjournal ON walletjournal (ownerid);

CREATE INDEX IDX_walletjournal_1 ON walletjournal (date);

CREATE INDEX IDX_FK_wallettransactions ON wallettransactions (ownerid);

CREATE INDEX IDX_wallettransactions_1 ON wallettransactions (stationid);

CREATE INDEX IDX_wallettransactions_2 ON wallettransactions (account);

CREATE INDEX IDX_wallettransactions_3 ON wallettransactions (typeid);
