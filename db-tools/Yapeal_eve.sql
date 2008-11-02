/*============================================================================*/
/* DDL SCRIPT                                                                 */
/*============================================================================*/
/* Title:      Yapeal_eve                                                     */
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

DROP TABLE IF EXISTS alliancelist CASCADE;

DROP TABLE IF EXISTS conquerablestationlist CASCADE;

DROP TABLE IF EXISTS errorlist CASCADE;

DROP TABLE IF EXISTS reftypes CASCADE;

/*============================================================================*/
/* Tables                                                                     */
/*============================================================================*/
CREATE TABLE alliancelist (
    allianceid BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255),
    shortname VARCHAR(255),
    executorcorpid BIGINT UNSIGNED,
    membercount BIGINT UNSIGNED,
    startdate DATETIME,
    CONSTRAINT PK_alliancelist PRIMARY KEY (allianceid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE conquerablestationlist (
    stationid BIGINT UNSIGNED NOT NULL,
    stationname VARCHAR(255),
    stationtypeid BIGINT UNSIGNED,
    solarsystemid BIGINT UNSIGNED,
    corporationid BIGINT UNSIGNED,
    corporationname VARCHAR(255),
    CONSTRAINT PK_conquerablestationlist PRIMARY KEY (stationid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE errorlist (
    errorcode SMALLINT UNSIGNED NOT NULL,
    errortext TEXT NOT NULL,
    CONSTRAINT PK_errorlist PRIMARY KEY (errorcode)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;

CREATE TABLE reftypes (
    reftypeid SMALLINT UNSIGNED NOT NULL,
    reftypename VARCHAR(255),
    CONSTRAINT PK_reftypes PRIMARY KEY (reftypeid)
)
engine=InnoDB
character set utf8
collate utf8_unicode_ci;
