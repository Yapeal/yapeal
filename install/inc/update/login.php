<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal Setup - Login page.
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
if ($_COOKIE['yapealsetup'] == "" && !isset($_POST['login'])) {
  // Login Page
  OpenSite(LOGIN);
  echo '<h3>'.LOGIN.'</h3><br />' . PHP_EOL
      .'<form action="' . $_SERVER['SCRIPT_NAME'] . '?lang='.$_GET['lang'].'&edit='.$_GET['edit'].'" method="post">' . PHP_EOL
      .PASSWORD.' <input type="password" name="setuppass" /><br />' . PHP_EOL
      .'<input type="hidden" name="login" value="login" />' . PHP_EOL
      .'<input type="submit" value="'.LOGIN.'" />' . PHP_EOL
      .'</form>' . PHP_EOL;
  CloseSite();
  exit;
} elseif ($_POST['login'] == 'login') {
  if (md5($_POST['setuppass']) === $conf['password']) {
    setcookie('yapealsetup',$conf['password']);
    header("Location: " . $_SERVER['SCRIPT_NAME'] . "?lang=".$_GET['lang']."&edit=".$_GET['edit']);
  } else {
    // Login Page
    OpenSite(LOGIN);
    echo '<h3>'.LOGIN.' '.FAILED.'</h3><br />' . PHP_EOL
        .'<form action="' . $_SERVER['SCRIPT_NAME'] . '?lang='.$_GET['lang'].'&edit='.$_GET['edit'].'" method="post">' . PHP_EOL
        .PASSWORD.' <input type="password" name="setuppass" /><br />' . PHP_EOL
        .'<input type="hidden" name="login" value="login" />' . PHP_EOL
        .'<input type="submit" value="'.LOGIN.'" />' . PHP_EOL
        .'</form>' . PHP_EOL;
    CloseSite();
    exit;
  }
} elseif ($_COOKIE['yapealsetup'] === $conf['password']) {
  setcookie('yapealsetup',$conf['password']);
} else {
  setcookie('yapealsetup',$conf['password'],time()-3600);
	header("Location: " . $_SERVER['SCRIPT_NAME'] . "?lang=".$_GET['lang']."&edit=".$_GET['edit']);
};
?>
