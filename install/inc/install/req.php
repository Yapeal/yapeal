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
/**
 * Log where in the setup progress we are
 */
$logtime = $_POST['logtime'];
$logtimenow = date('H:i:s',time());
$logfile = basename(__FILE__);
$log = <<<LOGTEXT
--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
Time: [$logtimenow]
Page: {$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}
File: $logfile
--------------------------------------------------------------------------------
[$logtimenow] Check if post['c_action'] is not = 0.
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Check for c_action
check_c_action();
/**
 * Run the script if check_c_action(); didn't exit the script
 */
$error = 0;
$chmodcheck = 0;
$content  = '<tr>' . PHP_EOL;
$content .= '  <td>'.INSTALLER_PHP_VERSION.' 5.2.1 +</td>' . PHP_EOL;
$content .= '  <td>'.phpversion().'</td>' . PHP_EOL;
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Check php version.
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Insure minimum version of PHP 5 we need to run.
if (version_compare(PHP_VERSION, '5.2.1', '<')) {
  $content .= '  <td class="warning">'.FAILED.'</td>' . PHP_EOL;
  $error++;
} else {
  $content .= '  <td class="good">'.OK.'</td>' . PHP_EOL;
};
$content .= '</tr>' . PHP_EOL;
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Check php extensions.
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Check for some required extensions
$required = array('curl', 'date', 'mysqli', 'SimpleXML', 'SPL');
$exts = get_loaded_extensions();
foreach ($required as $ext) {
  $content .= '<tr>' . PHP_EOL;
  $content .= '  <td>'.INSTALLER_PHP_EXT.': '.$ext.'</td>' . PHP_EOL;
  if (!in_array($ext, $exts)) {
    $content .= '  <td>'.MISSING.'</td>' . PHP_EOL;
    $content .= '  <td class="warning">'.INSTALLER_REQ_PHP_EXT.$ext.INSTALLER_IS_MISS.'</td>' . PHP_EOL;
    $error++;
  } else {
    $content .= '  <td>'.LOADED.'</td>' . PHP_EOL;
    $content .= '  <td class="good">'.OK.'</td>' . PHP_EOL;
  };
  $content .= '</tr>' . PHP_EOL;
};
$content2 = '';
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Check file/dir write premission.
LOGTEXT;
c_logging($log,$logtime,$logtype);
/**
 * check if write able
 */
WritChecker('config'.$DS);
WritChecker('cache'.$DS);
WritChecker('cache'.$DS.'account'.$DS);
WritChecker('cache'.$DS.'char'.$DS);
WritChecker('cache'.$DS.'corp'.$DS);
WritChecker('cache'.$DS.'eve'.$DS);
WritChecker('cache'.$DS.'log'.$DS);
WritChecker('cache'.$DS.'log'.$DS.'yapeal_error.log');
WritChecker('cache'.$DS.'log'.$DS.'yapeal_notice.log');
WritChecker('cache'.$DS.'log'.$DS.'yapeal_trace.log');
WritChecker('cache'.$DS.'log'.$DS.'yapeal_warning.log');
WritChecker('cache'.$DS.'map'.$DS);
WritChecker('cache'.$DS.'server'.$DS);
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Generate Page.
LOGTEXT;
c_logging($log,$logtime,$logtype);
/**
 * check if there was an extension or write premission that was not set corect
 */
if ($error > 0 || $chmodcheck > 0) {
  if ($error > 0) {
    $end = '<h2 class="warning">'.INSTALLER_HOST_NOT_SUPORTED.'</h2>' . PHP_EOL;
  } else {
    $end = '<h2 class="warning">'.INSTALLER_CHMOD_CHECK_FAIL.'</h2>' . PHP_EOL;
  };
} else {
  $end = '<form action="' . $_SERVER['SCRIPT_NAME'] . '?install=step2" method="post">' . PHP_EOL
        .'  <input type="hidden" name="logtime" value="'.$_POST['logtime'].'" />' . PHP_EOL
        .'  <input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
        .'  <input type="hidden" name="c_action" value="'.$_POST['c_action'].'" />' . PHP_EOL
        .'  <input type="submit" value="'.NEXT.'" />' . PHP_EOL
        .'</form>' . PHP_EOL;
};
OpenSite(INSTALLER_REQ_CHECK);
echo '<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="3">'.INSTALLER_REQ_CHECK.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl" style="text-align:center;">'.INSTALLER_REQUIRE.'</td>' . PHP_EOL
    .'    <td class="tableinfolbl" style="text-align:center;">'.INSTALLER_RESULT.'</td>' . PHP_EOL
    .'    <td class="tableinfolbl" style="text-align:center;">'.INSTALLER_STATUS.'</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .$content
    .'</table>' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.INSTALLER_WRITEABLE.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">'.INSTALLER_CHK_FILE_DIR_WRITE_PREM.'</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .$content2
    .'</table><br />' . PHP_EOL
    .$end;
CloseSite();
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Generate Page Done.
LOGTEXT;
c_logging($log,$logtime,$logtype);
?>
