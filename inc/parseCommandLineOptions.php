<?php
/**
 * Contains parseCommandLineOptions function.
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
 * Function used to parser command line options.
 *
 * This function was made to save having to make new one every time it might be
 * needed. It also helps with consistent option handle which should lead to less
 * user as well as programmer confusion.
 *
 * The -h, --help and -V, --version options don't have to be included in the
 * parameters as they will always be include automatically.
 *
 * Note that with older versions of PHP long options aren't available so all
 * required options must have a short form that can be used.
 *
 * @param array $shortOptions An array of short options to accept. The elements
 * should be in the same format as the short option parameter for setopt() i.e.
 * can be a single character followed by optional single colon for options that
 * have required values or double colons for ones that take an optional value.
 * @param array $longOptions A simple array of long option names to accept. The
 * same options as setopt() work i.e. single colon for required values and
 * double colons for ones that take a optional value.
 *
 * @return array Returns an array of options or an empty array.
 *
 * @todo Look at making this into a full class instead of just a function.
 */
function parseCommandLineOptions(array $shortOptions = NULL,
  array $longOptions = NULL) {
  if (!function_exists('getopt')) {
    return array();
  };
  $shortOptions = array_merge($shortOptions, array('h', 'V'));
  $shortOptions = implode('', $shortOptions);
  $longOptions = array_merge($longOptions, array('help', 'version'));
  if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
    $options = getopt($shortOptions, $longOptions);
  } else {
    $options = getopt($shortOptions);
  };
  $settings = array();
  if (empty($options)) {
    return $settings;
  };
  $optionsOnlyMap = array(
    'h' => 'help', 'help' => 'help',
    'V' => 'version', 'version' => 'version'
  );
  $optionsWithValuesMap = array(
    'c' => 'config', 'config' => 'config',
    'd' => 'database', 'database' => 'database',
    'driver' => 'driver',
    'l' => 'log-config', 'log' => 'log-config',
    'p' => 'password', 'password' => 'password',
    's' => 'host', 'server' =>'host',
    'suffix' => 'suffix',
    'table-prefix' => 'table_prefix',
    'u' => 'username', 'username' => 'username'
  );
  $optionsWithListMap = array(
    'privileges' => 'privileges',
    'xml' => 'xml'
  );
  foreach ($options as $opt => $value) {
    if (array_key_exists($opt, $optionsOnlyMap)) {
      $settings[$optionsOnlyMap[$opt]] = TRUE;
      continue;
    };
    if (array_key_exists($opt, $optionsWithValuesMap)) {
      if (is_array($value)) {
        // If option is used multiple times use the last value.
        $value = $value[count($value) - 1];
      };
      $settings[$optionsWithValuesMap[$opt]] = (string)$value;
      continue;
    };// if ... $optionsWithValuesMap ...
    if (array_key_exists($opt, $optionsWithListMap)) {
      if (is_array($value)) {
        // If option is used multiple times combined them.
        $value = implode(' ', $value);
      };
      if (isset($settings[$optionsWithListMap[$opt]])) {
        // Append to the existing list from short option.
        $settings[$optionsWithListMap[$opt]] .= ' ' . $value;
      } else {
        $settings[$optionsWithListMap[$opt]] = $value;
      };
      continue;
    };// if ... $optionsWithListMap ...
  };// foreach $options...
  return $settings;
}// function parseCommandLineOptions

