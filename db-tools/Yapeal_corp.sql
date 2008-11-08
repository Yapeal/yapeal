/*============================================================================*/
/* DDL SCRIPT                                                                 */
/*============================================================================*/
/* Title:      Yapeal_corp                                                    */
/* Platform:   MySQL 5                                                        */
/* Version:    0.1                                                            */
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

DROP TABLE IF EXISTS corporationsheet CASCADE;

DROP TABLE IF EXISTS accountbalance CASCADE;

DROP TABLE IF EXISTS assetlist CASCADE;

DROP TABLE IF EXISTS corplogo CASCADE;

DROP TABLE IF EXISTS divisions CASCADE;

DROP TABLE IF EXISTS fuel CASCADE;

DROP TABLE IF EXISTS industryjobs CASCADE;

DROP TABLE IF EXISTS marketorders CASCADE;

DROP TABLE IF EXISTS membertracking CASCADE;

DROP TABLE IF EXISTS starbasedetail CASCADE;

DROP TABLE IF EXISTS starbaselist CASCADE;

DROP TABLE IF EXISTS walletdivisions CASCADE;

DROP TABLE IF EXISTS walletjournal CASCADE;

DROP TABLE IF EXISTS wallettransactions CASCADE;

/*============================================================================*/
/* Tables                                                                     */
/*============================================================================*/
CREATE TABLE corporationsheet (
    corporationid BIGINT UNSIGNED NOT NULL,
    corporationname VARCHAR(255) NOT NULL,
    ticker VARCHAR(255) NOT NULL,
    ceoid BIGINT UNSIGNED NOT NULL,
    ceoname VARCHAR(255) NOT NULL,
    stationid BIGINT UNSIGNED NOT NULL,
    stationname VARCHAR(255) NOT NULL,
    description TEXT,
    url VARCHAR(255),
    allianceid BIGINT UNSIGNED,
    alliancename VARCHAR(255),
    taxrate DECIMAL(17,2) NOT NULL,
    membercount SMALLINT UNSIGNED NOT NULL,
    memberlimit SMALLINT UNSIGNED NOT NULL,
    shares BIGINT UNSIGNED NOT NULL,
    CONSTRAINT PK_corporationsheet PRIMARY KEY (corporationid),
    CONSTRAINT UC_corporationsheet_1 UNIQUE (corporationname),
    CONSTRAINT UC_corporationsheet_2 UNIQUE (ticker)
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

/*
COMMENT ON COLUMN assetlist.id
NestedSet id
*/

/*
COMMENT ON COLUMN assetlist.rootid
NestedSet rootid
*/

/*
COMMENT ON COLUMN assetlist.lft
NestedSet l
*/

/*
COMMENT ON COLUMN assetlist.rgt
NestedSet r
*/

/*
COMMENT ON COLUMN assetlist.norder
NestedSet norder
*/

/*
COMMENT ON COLUMN assetlist.lvl
NestedSet level
*/

CREATE TABLE corplogo (
    ownerid BIGINT UNSIGNED NOT NULL,
    graphicid BIGINT UNSIGNED NOT NULL,
    shape1 SMALLINT UNSIGNED NOT NULL,
    shape2 SMALLINT UNSIGNED NOT NULL,
    shape3 SMALLINT UNSIGNED NOT NULL,
    color1 BIGINT UNSIGNED NOT NULL,
    color2 BIGINT UNSIGNED NOT NULL,
    color3 BIGINT UNSIGNED NOT NULL,
    CONSTRAINT PK_corplogo PRIMARY KEY (ownerid, graphicid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE divisions (
    ownerid BIGINT UNSIGNED NOT NULL,
    accountkey SMALLINT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    CONSTRAINT PK_divisions PRIMARY KEY (ownerid, accountkey)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE fuel (
    itemid BIGINT NOT NULL,
    typeid BIGINT UNSIGNED NOT NULL,
    quantity BIGINT UNSIGNED NOT NULL,
    CONSTRAINT PK_fuel PRIMARY KEY (itemid, typeid)
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
    `range` SMALLINT NOT NULL,
    accountkey SMALLINT UNSIGNED NOT NULL,
    duration SMALLINT UNSIGNED NOT NULL,
    escrow DECIMAL(17,2) NOT NULL,
    price DECIMAL(17,2) NOT NULL,
    bid BOOL NOT NULL,
    issued DATETIME NOT NULL,
    changed TIMESTAMP,
    CONSTRAINT PK_marketorders PRIMARY KEY (ownerid, orderid),
    CONSTRAINT UC_marketorders_1 UNIQUE (orderid, issued)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE membertracking (
    characterid BIGINT UNSIGNED NOT NULL,
    ownerid BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    startdatetime DATETIME NOT NULL,
    baseid BIGINT UNSIGNED,
    base VARCHAR(255),
    title TEXT,
    logondatetime DATETIME NOT NULL,
    logoffdatetime DATETIME NOT NULL,
    locationid BIGINT UNSIGNED NOT NULL,
    location VARCHAR(255) NOT NULL,
    shiptypeid BIGINT UNSIGNED NOT NULL,
    shiptype VARCHAR(255) NOT NULL,
    roles VARCHAR(64) NOT NULL,
    grantableroles VARCHAR(64) NOT NULL,
    CONSTRAINT PK_membertracking PRIMARY KEY (characterid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE starbasedetail (
    ownerid BIGINT UNSIGNED NOT NULL,
    itemid BIGINT UNSIGNED NOT NULL,
    usageflags TINYINT UNSIGNED NOT NULL,
    deployflags TINYINT UNSIGNED NOT NULL,
    allowcorporationmembers BOOL NOT NULL,
    allowalliancemembers BOOL NOT NULL,
    claimsovereignty BOOL NOT NULL,
    onstandingdropenabled BOOL NOT NULL,
    onstandingdropstanding DECIMAL(17,2) NOT NULL,
    onstatusdropenabled BOOL NOT NULL,
    onstatusdropstanding DECIMAL(17,2) NOT NULL,
    onaggressionenabled BOOL NOT NULL,
    oncorporationwarenabled BOOL NOT NULL,
    changed TIMESTAMP NOT NULL,
    CONSTRAINT PK_starbasedetail PRIMARY KEY (ownerid, itemid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE starbaselist (
    ownerid BIGINT UNSIGNED NOT NULL,
    itemid BIGINT UNSIGNED NOT NULL,
    typeid BIGINT UNSIGNED NOT NULL,
    locationid BIGINT UNSIGNED NOT NULL,
    moonid BIGINT UNSIGNED NOT NULL,
    state SMALLINT UNSIGNED NOT NULL,
    statetimestamp DATETIME NOT NULL,
    onlinetimestamp DATETIME NOT NULL,
    CONSTRAINT PK_starbaselist PRIMARY KEY (ownerid, itemid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

/*
COMMENT ON COLUMN starbaselist.state
0 - Unanchored (also unanchoring??) (has valid stateTimestamp) Note that moonID is zero for unanchored Towers, but locationID will still yield the solar system ID : 1 - Anchored / Offline (no time information stored) : 2 - Onlining (will be online at time = onlineTimestamp) : 3 - Reinforced (until time = stateTimestamp) : 4 - Online (continuously since time = onlineTimestamp)
*/

CREATE TABLE walletdivisions (
    ownerid BIGINT UNSIGNED NOT NULL,
    accountkey SMALLINT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    CONSTRAINT PK_walletdivisions PRIMARY KEY (ownerid, accountkey)
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
    CONSTRAINT UC_walletjournal_1 UNIQUE (refid, date)
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
    characterid BIGINT UNSIGNED NOT NULL,
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
    ADD CONSTRAINT FK_corporationsheet_accountbalance
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE assetlist
    ADD CONSTRAINT FK_corporationsheet_assetlist
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE corplogo
    ADD CONSTRAINT FK_corporationsheet_corplogo
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE divisions
    ADD CONSTRAINT FK_corporationsheet_corpdivisions
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE industryjobs
    ADD CONSTRAINT FK_corporationsheet_industryjobs
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE marketorders
    ADD CONSTRAINT FK_corporationsheet_marketorders
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE membertracking
    ADD CONSTRAINT FK_corporationsheet_membertracking
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE starbasedetail
    ADD CONSTRAINT FK_corporationsheet_starbasedetail
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE starbaselist
    ADD CONSTRAINT FK_corporationsheet_starbaselist
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE walletdivisions
    ADD CONSTRAINT FK_corporationsheet_corpwalletdivisions
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE walletjournal
    ADD CONSTRAINT FK_corporationsheet_walletjournal
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

ALTER TABLE wallettransactions
    ADD CONSTRAINT FK_corporationsheet_wallettransactions
    FOREIGN KEY (ownerid) REFERENCES corporationsheet (corporationid)
    ON DELETE CASCADE;

/*============================================================================*/
/* Indexes                                                                    */
/*============================================================================*/
CREATE INDEX IDX_FK_corporationsheet_accountbalance ON accountbalance (ownerid);

CREATE INDEX IDX_assetlist_1 ON assetlist (typeid);

CREATE INDEX IDX_assetlist_2 ON assetlist (flag);

CREATE INDEX IDX_FK_corporationsheet_assetlist ON assetlist (ownerid);

CREATE INDEX IDX_FK_corporationsheet_corplogo ON corplogo (ownerid);

CREATE INDEX IDX_FK_corporationsheet_corpdivisions ON divisions (ownerid);

CREATE INDEX IDX_FK_corporationsheet_industryjobs ON industryjobs (ownerid);

CREATE INDEX IDX_industryjobs_1 ON industryjobs (activityid);

CREATE INDEX IDX_industryjobs_2 ON industryjobs (installeditemtypeid);

CREATE INDEX IDX_FK_corporationsheet_marketorders ON marketorders (ownerid);

CREATE INDEX IDX_marketorders_1 ON marketorders (accountkey);

CREATE INDEX IDX_marketorders_2 ON marketorders (orderstate);

CREATE INDEX IDX_marketorders_3 ON marketorders (typeid);

CREATE INDEX IDX_FK_corporationsheet_membertracking ON membertracking (ownerid);

CREATE INDEX IDX_membertracking_1 ON membertracking (name);

CREATE INDEX IDX_FK_corporationsheet_starbasedetail ON starbasedetail (ownerid);

CREATE INDEX IDX_FK_corporationsheet_starbaselist ON starbaselist (ownerid);

CREATE INDEX IDX_starbaselist_1 ON starbaselist (state);

CREATE INDEX IDX_FK_corporationsheet_corpwalletdivisions ON walletdivisions (ownerid);

CREATE INDEX IDX_FK_corporationsheet_walletjournal ON walletjournal (ownerid);

CREATE INDEX IDX_walletjournal_1 ON walletjournal (account);

CREATE INDEX IDX_FK_corporationsheet_wallettransactions ON wallettransactions (ownerid);

CREATE INDEX IDX_wallettransactions_2 ON wallettransactions (account);

CREATE INDEX IDX_wallettransactions_3 ON wallettransactions (typeid);
