<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal Setup - Config page.
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
}
// Get config info
$ini_yapeal = parse_ini_file('..'.$DS.'config'.$DS.'yapeal.ini', true);
$db = new mysqli($ini_yapeal['Database']['host'],$ini_yapeal['Database']['username'],$ini_yapeal['Database']['password']);
$query = "SELECT * FROM `".$ini_yapeal['Database']['database']."`.`".$ini_yapeal['Database']['table_prefix']."utilconfig`";
$result = $db->query($query);
while ($row = $result->fetch_assoc()) {
	$conf[$row['Name']] = $row['Value'];
}
$result->close();
$db->close();
// Get login info
require_once('inc'.$DS.'update'.$DS.'login.php');
if (isset($_GET['edit']) && $_GET['edit'] == "setup") {
	// Main edit site
  require_once('inc'.$DS.'update'.$DS.'config.php');
} elseif (isset($_GET['edit']) && $_GET['edit'] == "select") {
	// Main edit site
  require_once('inc'.$DS.'update'.$DS.'char_select.php');
} elseif (isset($_GET['edit']) && $_GET['edit'] == "go") {
	// Main edit site
  require_once('inc'.$DS.'update'.$DS.'go.php');
} else {
  $langs = array();

  if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

    if (count($lang_parse[1])) {
      // create a list like "en" => 0.8
      $langs = array_combine($lang_parse[1], $lang_parse[4]);

      // set default to 1 for any without q factor
      foreach ($langs as $lang => $val) {
        if ($val === '') $langs[$lang] = 1;
      }

      // sort list based on value	
      arsort($langs, SORT_NUMERIC);
    }; // if count $lang_parse[1]
  }; // if isset $_SERVER['HTTP_ACCEPT_LANGUAGE']
  foreach ($langs as $lang => $val) {
    if (strpos($lang, 'da') === 0) {
      // show Danish site
      header("Location: ".$_SERVER['SCRIPT_NAME']."?lang=da&edit=setup");
      exit;
    } else if (strpos($lang, 'ru') === 0) {
      // show English site
      header("Location: ".$_SERVER['SCRIPT_NAME']."?lang=ru&edit=setup");
      exit;
    } else if (strpos($lang, 'en') === 0) {
      // show English site
      header("Location: ".$_SERVER['SCRIPT_NAME']."?lang=en&edit=setup");
      exit;
    }; 
  }
  header("Location: ".$_SERVER['SCRIPT_NAME']."?lang=en&edit=setup");
};
?>