<?php
/**
 * Used to add test corporation to utilRegisteredCorporation table.
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
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @subpackage Install
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be ran directly.
 */
if (basename(__FILE__) != basename($_SERVER['PHP_SELF'])) {
  $mess = 'Including of ' . $argv[0] . ' is not allowed' . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error');
  exit(1);
};
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// Define shortened name for DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);
// Pull in Yapeal revision constants.
$path = $dir . DS . '..' . DS . 'revision.php';
require_once realpath($path);
// Move down and over to 'inc' directory to read common_backend.php
$path = $dir . DS . '..' . DS . 'inc' . DS . 'common_backend.php';
require_once realpath($path);
if ($argc < 4) {
  $mess = 'XMLFile, CorporationID, CharacterID are required in ';
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
$corpID = (string)$argv[2];
$charID = (string)$argv[3];
try {
  $xml = simplexml_load_file($xmlFile);
  $corps = $xml->xpath('//row');
  if (!empty($corps)) {
    // Need list of allowed APIs
    $section = new Sections('corp', FALSE);
    foreach ($corps as $row) {
      $corporationID = (string)$row['corporationID'];
      $corp = new RegisteredCorporation($corporationID);
      $corp->activeAPI = (string)$section->activeAPI;
      $corp->characterID = (string)$row['characterID'];
      $corp->corporationName = (string)$row['corporationName'];
      $postData = array('id' => $corporationID);
      $http = array('timeout' => 15, 'method' => 'POST',
        'url' => 'http://www.evecorplogo.net/logo.php');
      $http['content'] = http_build_query($postData, NULL, '&');
      $curl = new CurlRequest($http);
      $result = $curl->exec();
      // Now check for errors.
      if ($result['curl_error'] != ''  ||  200 != $result['http_code'] ||
        $result['body'] == '') {
        $picFile = realpath(YAPEAL_PICS . 'blank.png');
        $corp->graphic = '0x' . bin2hex(file_get_contents($picFile));
        $corp->graphicType = 'png';
      } else {
        // Have the picture now it can be added to $corp.
        $corp->graphic = '0x' . bin2hex($result['body']);
        $corp->graphicType = 'png';
      };
      if ($corp->characterID == $charID) {
        $corp->isActive = 1;
      } else {
        $corp->isActive = 0;
      };
      $corp->proxy = '';
      // Add the corporation to the database.
      $corp->store();
      $mess = 'Added ' . $corporationID . ' to database';
      fwrite(STDERR, $mess);
    };// foreach $chars ...
    fwrite(STDOUT, 'true');
    exit(0);
  };
  $mess = 'No corporations' . PHP_EOL;
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
$mess = 'No corporation found';
fwrite(STDERR, $mess);
fwrite(STDOUT, 'error');
exit(5);
?>
