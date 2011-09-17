<?php
/**
 * Used to set revision information for Yapeal.
 *
 * Developers need to run admin/update-revision.php in their branch or trunk
 * as needed before committing.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal.
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
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 * @since      revision 894
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
  $mess = basename(__FILE__) . ' must be included it can not be ran directly';
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  } else {
    fwrite(STDERR, $mess . PHP_EOL);
    fwrite(STDOUT, 'error' . PHP_EOL);
    exit(1);
  };
};
if (!defined('YAPEAL_DATE')) {
  /**
   * Track date of script.
   */
  define('YAPEAL_DATE', str_replace('@', '', '@builddate@'));
};
if (!defined('YAPEAL_STABILITY')) {
  /**
   * Track stability of script.
   */
  define('YAPEAL_STABILITY', str_replace('@', '', '@svnstability@'));
};
if (!defined('YAPEAL_VERSION')) {
  /**
   * Track version of script.
   */
  define('YAPEAL_VERSION', str_replace('@', '', '@svnversion@'));
};
?>
