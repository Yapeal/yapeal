<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Requirements.
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
$error = 0;
$chmodcheck = 0;
$content  = '<tr>' . PHP_EOL;
$content .= '  <td>PHP version 5.2.1 +</td>' . PHP_EOL;
$content .= '  <td>'.phpversion().'</td>' . PHP_EOL;
// Insure minimum version of PHP 5 we need to run.
if (version_compare(PHP_VERSION, '5.2.1', '<')) {
  $content .= '  <td class="warning">Bad</td>' . PHP_EOL;
  $error++;
} else {
  $content .= '  <td class="good">Ok</td>' . PHP_EOL;
};
$content .= '</tr>' . PHP_EOL;
// Check for some required extensions
$required = array('curl', 'mysqli', 'SimpleXML');
$exts = get_loaded_extensions();
foreach ($required as $ext) {
  $content .= '<tr>' . PHP_EOL;
  $content .= '  <td>PHP extension: '.$ext.'</td>' . PHP_EOL;
  if (!in_array($ext, $exts)) {
    $content .= '  <td>Missing</td>' . PHP_EOL;
    $content .= '  <td class="warning">The required PHP extension ' . $ext . ' is missing!</td>' . PHP_EOL;
    $error++;
  } else {
    $content .= '  <td>Loaded</td>' . PHP_EOL;
    $content .= '  <td class="good">Ok</td>' . PHP_EOL;
  };
  $content .= '</tr>' . PHP_EOL;
};
$content2 = '';
WritChecker('config/');
WritChecker('cache/');
WritChecker('cache/account/');
WritChecker('cache/char/');
WritChecker('cache/corp/');
WritChecker('cache/eve/');
WritChecker('cache/log/');
WritChecker('cache/log/yapeal_error.log');
WritChecker('cache/log/yapeal_notice.log');
WritChecker('cache/log/yapeal_trace.log');
WritChecker('cache/log/yapeal_warning.log');
WritChecker('cache/map/');
WritChecker('cache/server/');
if ($error > 0 || $chmodcheck > 0) {
  if ($error > 0) {
    $end = '<h2 class="warning">This web host does not support Yapeal.<br />' . PHP_EOL . 'Solution: Rent a web host that meets the requirement<br />' . PHP_EOL . 'or if it your own, then update/install the requirements.</h2>' . PHP_EOL;
  } else {
    $end = '<h2 class="warning">Some files or folder was not writable.<br />' . PHP_EOL . 'Chmod the file or folders correctly!</h2>' . PHP_EOL;
  };
} else {
  $end = '<form action="' . $_SERVER['SCRIPT_NAME'] . '?install=step2" method="post">' . PHP_EOL
        .'  <input type="submit" value="Next" />' . PHP_EOL
        .'</form>' . PHP_EOL;
};
OpenSite('Requirement Check');
echo '<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="3">Requirement Check</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl" style="text-align:center;">Require</td>' . PHP_EOL
    .'    <td class="tableinfolbl" style="text-align:center;">Result</td>' . PHP_EOL
    .'    <td class="tableinfolbl" style="text-align:center;">Status</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .$content
    .'</table>' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">Writable</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">Checking file and folder write permissions.</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .$content2
    .'</table><br />' . PHP_EOL
    .$end;
CloseSite();
?>