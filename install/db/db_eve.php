<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer Database eve tables adder.
 *
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know as Yapeal.
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
 *
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};

// Creating Table AllianceList
$query = "
  CREATE  TABLE IF NOT EXISTS `AllianceList` (
    `allianceID` BIGINT UNSIGNED NOT NULL ,
    `name` VARCHAR(255) NULL DEFAULT NULL ,
    `shortName` VARCHAR(255) NULL DEFAULT NULL ,
    `executorCorpID` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `memberCount` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `startDate` DATETIME NULL DEFAULT NULL ,
    PRIMARY KEY (`allianceID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("AllianceList", "DCT", $query);

// Creating Table ConquerableStationList
$query = "
  CREATE  TABLE IF NOT EXISTS `ConquerableStationList` (
    `stationID` BIGINT UNSIGNED NOT NULL ,
    `stationName` VARCHAR(255) NULL DEFAULT NULL ,
    `stationTypeID` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `solarSystemID` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `corporationID` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `corporationName` VARCHAR(255) NULL DEFAULT NULL ,
    PRIMARY KEY (`stationID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("ConquerableStationList", "DCT", $query);

// Creating Table ErrorList
$query = "
  CREATE  TABLE IF NOT EXISTS `ErrorList` (
    `errorCode` SMALLINT UNSIGNED NOT NULL ,
    `errorText` TEXT NOT NULL ,
    PRIMARY KEY (`errorCode`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("ErrorList", "DCT", $query);

// Creating Table RefTypes
$query = "
  CREATE  TABLE IF NOT EXISTS `RefTypes` (
    `refTypeID` SMALLINT UNSIGNED NOT NULL ,
    `refTypeName` VARCHAR(255) NULL DEFAULT NULL ,
    PRIMARY KEY (`refTypeID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("RefTypes", "DCT", $query);
?>
