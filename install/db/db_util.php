<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer Database util tables adder.
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
///////////////////////////////////////////
// Create Tables that is needed before creating those that can be chosen to activate.
///////////////////////////////////////////

// Creating Table CachedUntil
$query = "CREATE TABLE IF NOT EXISTS `CachedUntil` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `tableName` VARCHAR(255) NOT NULL ,
    `cachedUntil` DATETIME NOT NULL ,
    PRIMARY KEY (`tableName`, `ownerID`) )
  ENGINE = MEMORY
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler('CachedUntil', 'DCT', $query);

// Creating Table ServerStatus
$query = "
  CREATE  TABLE IF NOT EXISTS `ServerStatus` (
    `serverOpen` BOOLEAN NOT NULL ,
    `onlinePlayers` BIGINT UNSIGNED NOT NULL )
  ENGINE = MEMORY
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler('ServerStatus', 'DCT', $query);

// Creating Table RegisteredUser
$query = "
  CREATE  TABLE IF NOT EXISTS `RegisteredUser` (
    `userID` BIGINT UNSIGNED NOT NULL ,
    `fullApiKey` VARCHAR(64) NULL DEFAULT NULL ,
    `limitedApiKey` VARCHAR(64) NULL DEFAULT NULL ,
    PRIMARY KEY (`userID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
if (DBHandler('RegisteredUser', 'DCT', $query)) {
  // Insert info into RegisteredUser
	$query = "INSERT INTO `RegisteredUser` (`userID`,`fullApiKey`,`limitedApiKey`) VALUES ('".$_POST['api_user_id']."','".$_POST['api_full_key']."','".$_POST['api_limit_key']."')
        ON DUPLICATE KEY UPDATE `fullApiKey` = values(`fullApiKey`),`limitedApiKey` = values(`limitedApiKey`)";
  DBHandler('RegisteredUser', 'DII', $query);
};

// Creating Table RegisteredCharacter
$query = "
  CREATE  TABLE IF NOT EXISTS `RegisteredCharacter` (
    `characterID` BIGINT UNSIGNED NOT NULL ,
    `userID` BIGINT UNSIGNED NOT NULL ,
    `name` VARCHAR(255) NOT NULL ,
    `corporationID` BIGINT UNSIGNED NOT NULL ,
    `corporationName` VARCHAR(255) NOT NULL ,
    `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
    `graphic` BLOB NULL DEFAULT NULL ,
    `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
    PRIMARY KEY (`characterID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
if (DBHandler('RegisteredCharacter', 'DCT', $query)) {
  // Insert info into RegisteredCharacter
  $query = "INSERT INTO `RegisteredCharacter` (`characterID`,`userID`,`name`,`corporationID`,`corporationName`,`isActive`)
   VALUES ('".$charinfo[1]."','".$_POST['api_user_id']."','".$charinfo[0]."','".$charinfo[3]."','".$charinfo[2]."','1')
   ON DUPLICATE KEY UPDATE `userID` = values(`userID`), `name` = values(`name`), `corporationID` = values(`corporationID`), `corporationName` = values(`corporationName`), `isActive` = values(`isActive`)";
  DBHandler('RegisteredCharacter', 'DII', $query);
};

// Creating Table RegisteredCorporation
$query = "
  CREATE  TABLE IF NOT EXISTS `RegisteredCorporation` (
    `corporationID` BIGINT UNSIGNED NOT NULL ,
    `characterID` BIGINT UNSIGNED NOT NULL ,
    `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
    `graphic` BLOB NULL DEFAULT NULL ,
    `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
    PRIMARY KEY (`corporationID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
if (DBHandler('RegisteredCorporation', 'DCT', $query)) {
  // Insert info into RegisteredCorporation
  $query = "INSERT INTO `RegisteredCorporation` (`corporationID`,`characterID`,`isActive`)
   VALUES ('".$charinfo[3]."','".$charinfo[1]."','1')
   ON DUPLICATE KEY UPDATE `characterID` = values(`characterID`),`isActive` = values(`isActive`)";
  DBHandler('RegisteredCorporation', 'DII', $query);
};
?>
