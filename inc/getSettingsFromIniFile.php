<?php
/**
 * Contains getSettingsFromIniFile function.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
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
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2012, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be included.
 */
if (count(get_included_files()) < 2) {
  $mess = basename(__FILE__)
    . ' must be included it can not be ran directly.' . PHP_EOL;
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  };
  fwrite(STDERR, $mess);
  exit(1);
};
/**
 * Function used to get settings from an 'ini' configuration file.
 *
 * @param string $file Path and name of the ini file to get.
 * @param string $section Name of a section if only one is wanted.
 *
 * @return array Returns list of settings from file.
 *
 * @todo Look at making this into a full class instead of just a function.
 */
function getSettingsFromIniFile($file = NULL, $section = NULL) {
  // Check if given custom configuration file.
  if (empty($file) || !is_string($file)) {
    $file = @getenv('YAPEAL_INI');
    if ($file === FALSE) {
      $file = YAPEAL_CONFIG . 'yapeal.ini';
    };
  } else {
    $mess = 'Using custom configuration file ' . $file . PHP_EOL;
    fwrite(STDOUT, $mess);
  };
  if (!(is_readable($file) && is_file($file))) {
    $mess = 'The ' . $file . ' configuration file is missing!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  // Grab the info from ini file.
  $settings = parse_ini_file($file, TRUE);
  if (empty($settings)) {
    $mess = 'The ' . $file . ' configuration file contains no settings!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  if (isset($section)) {
    if (isset($settings[$section]) && !empty($settings[$section])) {
      return $settings[$section];
    };
    return array();
  };
  return $settings;
}// function getSettingsFromIniFile

