<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer php functions.
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

//////////////////////////////////
// Functions
//////////////////////////////////

// Input standard Site header
function OpenSite($subtitle = "", $JS = false) {
  if ($subtitle != "") {
    $subtitle = ' - '.$subtitle;
  };
  if ($JS) {
    $JS = PHP_EOL . '<script language="javascript" type="text/javascript" src="inc/api.js"></script>';
  } else {
    $JS = "";
  };
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL
      .'<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL
      .'<head>' . PHP_EOL
      .'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL
      .'<title>Yapeal Installer' . $subtitle . '</title>' . PHP_EOL
      .'<link href="inc/style.css" rel="stylesheet" type="text/css" />' . $JS . PHP_EOL
      .'</head>' . PHP_EOL
      .'<body>' . PHP_EOL
      .'<div id="wrapper">' . PHP_EOL
      .'<img src="../pics/yapealblue.png" width="150" height="50" alt="Yapeal logo" /><br />' . PHP_EOL
      .'<h1>Installer</h1>' . PHP_EOL;
}

// Input standard Site footer
function CloseSite() {
  echo '</div>' . PHP_EOL
      .'</body>' . PHP_EOL
      .'</html>' . PHP_EOL;
}

// DB Handler
function DBHandler($info, $dbtype = "CON", $checker = "") {
  global $link, $output, $stop;
  $select = false;
  if ($dbtype==="CON") {
    // Check Connection
    $errorval = 1;
    $request_type = "Database: Connect To";
    $okay = "Connected";
    $select = $link;
    $errortext = @mysqli_connect_error();
  } else if ($dbtype==="DS") {
    // Check Selected DB
    $errorval = 1;
    $request_type = "Database: Select Database";
    $okay = "Selected";
    $select = @mysqli_select_db($link,$checker);
    $errortext = 'Database '.$info.' was not found';
  } else if ($dbtype==="DCT") {
    // Check Table Create
    $errorval = 0;
    $request_type = "Database: Create Table";
    $okay = "Done";
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error();
  } else if ($dbtype==="DII") {
    // Check Insert
    $errorval = 0;
    $request_type = "Database: Insert Into";
    $okay = "Done";
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error();
  } else if ($dbtype==="CLOSE") {
    // Check Close DB
    if (!$link) { return false; };
    $errorval = 0;
    $request_type = "Database: Close Connection";
    $okay = "Closed";
    $select = @mysqli_close($link);
  } else {
    // Return False
    return false;
  };
  if (!$select) {
    $stop += $errorval;
    $output .= '  <tr>' . PHP_EOL;
    $output .= '    <td class="tableinfolbl" style="text-align: left;">'.$request_type.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$info.'</td>' . PHP_EOL;
    $output .= '    <td class="warning">'.$errortext.'</td>' . PHP_EOL;
    $output .= '  </tr>' . PHP_EOL;
    return false;
  } else {
    $output .= '  <tr>' . PHP_EOL;
    $output .= '    <td class="tableinfolbl" style="text-align: left;">'.$request_type.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$info.'</td>' . PHP_EOL;
    $output .= '    <td class="good">'.$okay.'</td>' . PHP_EOL;
    $output .= '  </tr>' . PHP_EOL;
    return true;
  };
}

// Dir and File writ anabled checker
function WritChecker($path) {
  global $content2, $chmodcheck;
  if (is_file('../'.$path)) {
    $type = '(File)';
    $cmod = 'file to 666';
  } else {
    $type = '(Dir)';
    $cmod = 'folder to 777';
  }
  $content2 .= '  <tr>' . PHP_EOL;
  $content2 .= '    <td width="220">'.$path.' '.$type.'</td>' . PHP_EOL;
  if (is_writable("../".$path)) {
    $content2 .= '    <td class="good">Yes</td>' . PHP_EOL;
  } else {
    $content2 .= '    <td class="warning">No - chmod '.$cmod.'</td>' . PHP_EOL;
    $chmodcheck++;
  };
  $content2 .= '  </tr>' . PHP_EOL;
}

// Check disabled form values
function DisableChecker($value) {
  if ($value == "") {
    return '0';
  } else {
    return $value;
  };
}
?>
