/*============================================================================*/
/* DDL SCRIPT                                                                 */
/*============================================================================*/
/* Title:      Yapeal_util                                                       */
/* Platform:   MySQL 5                                                        */
/* Version:    Beta                                                           */
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

DROP TABLE IF EXISTS cacheduntil CASCADE;

DROP TABLE IF EXISTS registereduser CASCADE;

DROP TABLE IF EXISTS registeredcharacter CASCADE;

DROP TABLE IF EXISTS registeredcorporation CASCADE;

/*============================================================================*/
/* Tables                                                                     */
/*============================================================================*/
CREATE TABLE cacheduntil (
    ownerid BIGINT UNSIGNED NOT NULL,
    tablename VARCHAR(255) NOT NULL,
    cacheduntil DATETIME NOT NULL,
    CONSTRAINT PK_cacheduntil PRIMARY KEY (tablename, ownerid)
)
engine=MEMORY
character set utf8
collate utf8_unicode_ci;

CREATE TABLE registereduser (
    userid BIGINT UNSIGNED NOT NULL,
    fullapikey VARCHAR(64),
    limitedapikey VARCHAR(64),
    CONSTRAINT PK_registereduser PRIMARY KEY (userid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE registeredcharacter (
    characterid BIGINT UNSIGNED NOT NULL,
    userid BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    corporationid BIGINT UNSIGNED NOT NULL,
    corporationname VARCHAR(255) NOT NULL,
    is_active BOOL NOT NULL DEFAULT FALSE,
    graphic BLOB,
    graphictype VARCHAR(16),
    CONSTRAINT PK_registeredcharacter PRIMARY KEY (characterid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE registeredcorporation (
    corporationid BIGINT UNSIGNED NOT NULL,
    characterid BIGINT UNSIGNED NOT NULL,
    is_active BOOL NOT NULL DEFAULT FALSE,
    graphic BLOB,
    graphictype VARCHAR(16),
    CONSTRAINT PK_registeredcorporation PRIMARY KEY (corporationid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

/*============================================================================*/
/* Foreign keys                                                               */
/*============================================================================*/
ALTER TABLE registeredcharacter
    ADD CONSTRAINT FK_registeredcharacter
    FOREIGN KEY (userid) REFERENCES registereduser (userid);

ALTER TABLE registeredcorporation
    ADD CONSTRAINT FK_registeredcorporation
    FOREIGN KEY (characterid) REFERENCES registeredcharacter (characterid);

/*============================================================================*/
/* Indexes                                                                    */
/*============================================================================*/
CREATE INDEX IDX_FK_registeredcharacter ON registeredcharacter (userid);

CREATE INDEX IDX_registeredcharacter_1 ON registeredcharacter (corporationid);

CREATE INDEX IDX_FK_registeredcorporation ON registeredcorporation (characterid);
