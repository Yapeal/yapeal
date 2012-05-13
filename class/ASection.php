<?php
/**
 * Contains abstract Section class.
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
 * Abstract class used to hold common methods needed by Section* classes.
 *
 * @package Yapeal
 * @subpackage Api_sections
 */
abstract class ASection {
  /**
   * @var bool Use to signal to child that parent constructor aborted.
   */
  protected $abort = FALSE;
  /**
   * @var object Hold AccessMask class used to convert between mask and APIs.
   */
  protected $am;
  /**
   * @var array Holds the mask of APIs for this section.
   */
  protected $mask;
  /**
   * @var string Hold section name.
   */
  protected $section;
  /**
   * Constructor
   */
  public function __construct() {
    try {
      $section = new Sections(strtolower($this->section), FALSE);
    }
    catch (Exception $e) {
      Logger::getLogger('yapeal')->error($e);
      // Section does not exist in utilSections table or other error occurred.
      $this->abort = TRUE;
      return;
    }
    if ($section->isActive == 0) {
      // Skip inactive sections.
      $this->abort = TRUE;
      return;
    };
    $this->mask = $section->activeAPIMask;
    // Skip if there's no active APIs for this section.
    if ($this->mask == 0) {
      $this->abort = TRUE;
      return;
    };
    $this->am = new AccessMask();
    $path = YAPEAL_CLASS . 'api' . DS . $this->section . DS;
    $foundAPIs = FilterFileFinder::getStrippedFiles($path, $this->section);
    $knownAPIs = $this->am->apisToMask($foundAPIs, $this->section);
    if ($knownAPIs !== FALSE) {
      $this->mask &= $knownAPIs;
    } else {
      $this->abort = TRUE;
      $mess = 'No known APIs found for section ' . $this->section;
      Logger::getLogger('yapeal')->error($mess);
      return;
    };// else $foundAPIs ...
  }// function __construct
  /**
   * Function called by Yapeal.php to start section pulling XML from servers.
   *
   * @return bool Returns TRUE if all APIs were pulled cleanly else FALSE.
   */
  abstract public function pullXML();
}

