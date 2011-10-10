#!/usr/bin/php -Cq
<?php
/**
 * Used to add test character to utilRegisteredCharacter table.
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
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @subpackage Install
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
 * @internal Only let this code be ran in CLI.
 */
if (PHP_SAPI != 'cli') {
  header('HTTP/1.0 403 Forbidden', TRUE, 403);
  $mess = basename(__FILE__) . ' only works with CLI version of PHP but tried';
  $mess = ' to run it using ' . PHP_SAPI . ' instead';
  die($mess);
};
/**
 * @internal Only let this code be ran directly.
 */
$included = get_included_files();
if (count($included) > 1 || $included[0] != __FILE__) {
  $mess = basename(__FILE__) . ' must be called directly and can not be included';
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error' . PHP_EOL);
  exit(1);
};
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
/**
 * Define shortened name for DIRECTORY_SEPARATOR
 * @ignore
 */
define('DS', DIRECTORY_SEPARATOR);
// Pull in Yapeal revision constants.
$path = $dir . DS . '..' . DS . 'revision.php';
require_once realpath($path);
// Move down and over to 'inc' directory to read common_backend.php
$path = $dir . DS . '..' . DS . 'inc' . DS . 'common_backend.php';
require_once realpath($path);
if ($argc < 3) {
  $mess = 'XMLFile, CharacterID are required in ';
  $mess .= $argv[0] . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error');
  exit(2);
};
// Strip any quotes
$replace = array("'", '"');
for ($i = 1; $i < $argc; ++$i) {
  $argv[$i] = str_replace($replace, '', $argv[$i]);
};
$xmlFile = $argv[1];
$charID = (string)$argv[2];
try {
  $xml = simplexml_load_file($xmlFile);
  $chars = $xml->xpath('//row');
  if (!empty($chars)) {
    // Need list of allowed APIs
    $section = new Sections('char', FALSE);
    foreach ($chars as $row) {
      $characterID = (string)$row['characterID'];
      $char = new RegisteredCharacter($characterID);
      $char->activeAPIMask = (string)$section->activeAPIMask;
      if ($characterID == $charID) {
        $char->isActive = 1;
      } else {
        $char->isActive = 0;
      };
      $char->characterName = (string)$row['characterName'];
      //$char->proxy = '';
      // Add the character to the database.
      $char->store();
      $mess = 'Added ' . $characterID . ' to database';
      fwrite(STDERR, $mess);
      $pic = new Graphic($characterID);
      $pic->ownerType = 'char';
      $url = 'http://image.eveonline.com/Character/' . $characterID . '_256.jpg';
      $http = array('timeout' => 45, 'method' => 'GET',
        'url' => $url);
      $curl = new CurlRequest($http);
      $result = $curl->exec();
      // Now check for errors.
      if ($result['curl_error'] != '' || 200 != $result['http_code'] ||
        $result['body'] == '') {
        $picFile = realpath(YAPEAL_PICS . 'blank.png');
        $pic->graphic = '0x' . bin2hex(file_get_contents($picFile));
        $pic->graphicType = 'png';
      } else {
        // Have the picture now it can be added to $pic.
        $pic->graphic = '0x' . bin2hex($result['body']);
        $pic->graphicType = 'jpg';
      };
    };// foreach $chars ...
    fwrite(STDOUT, 'true');
    exit(0);
  };
  $mess = 'No characters' . PHP_EOL;
  fwrite(STDERR, $mess);
  exit(3);
}
catch (Exception $e) {
  $mess = <<<MESS
EXCEPTION:
     Code: {$e->getCode()}
  Message: {$e->getMessage()}
     File: {$e->getFile()}
     Line: {$e->getLine()}
Backtrace:
{$e->getTraceAsString()}
MESS;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error');
  exit(4);
}
$mess = 'No character found';
fwrite(STDERR, $mess);
fwrite(STDOUT, 'error');
exit(5);
?>
