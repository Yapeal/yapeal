<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal Setup - Update page.
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
if (conRev($ini_yapeal['version'])>471) {
  $db = new mysqli($ini_yapeal['Database']['host'],$ini_yapeal['Database']['username'],$ini_yapeal['Database']['password']);
  $query = "SELECT * FROM `".$ini_yapeal['Database']['database']."`.`".$ini_yapeal['Database']['table_prefix']."utilconfig`";
  $result = $db->query($query);
  while ($row = $result->fetch_assoc()) {
    $conf[$row['Name']] = $row['Value'];
  }
  require_once('inc'.$DS.'update'.$DS.'login.php');
};
if (isset($_GET['edit']) && $_GET['edit'] == "usetup") {
  // Main edit site
  require_once('inc'.$DS.'update'.$DS.'uconfig.php');
} elseif (isset($_GET['edit']) && $_GET['edit'] == "uselect") {
  // Main edit site
  require_once('inc'.$DS.'update'.$DS.'uchar_select.php');
} elseif (isset($_GET['edit']) && $_GET['edit'] == "newupdate") {
  // Main edit site
  OpenSite(UPD_NEW_UPDATE,true);
  echo  UPD_NEW_UPDATE_DES
	     .'<form action="' . $_SERVER['SCRIPT_NAME'] . '?lang=' . $_GET['lang'] . '&amp;edit=usetup" method="post">' . PHP_EOL
       .'<input type="hidden" name="c_action" value="2" />' . PHP_EOL
       .'<input type="submit" value="'.UPDATE.'" />' . PHP_EOL
       .'</form>' . PHP_EOL;
  CloseSite();
} elseif (isset($_GET['edit']) && $_GET['edit'] == "go") {
  // Do update
  require_once('inc'.$DS.'update'.$DS.'go.php');
} else {
  header("Location: ".$_SERVER['SCRIPT_NAME']."?lang=".GetBrowserLang()."&edit=newupdate");
};
/* if (isset($ini_yapeal['table_prefix'])) {

}; */

?>
