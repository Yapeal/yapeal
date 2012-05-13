<?php
/**
 * Contains FilterFileFinder class.
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
 * Class to recursive search through a directory for files/directories that match
 * a search string.
 *
 * @package Yapeal
 */
class FilterFileFinder extends FilterIterator {
  /**
   * @var string Contains the filesystem path to be searched.
   */
  protected $path;
  /**
   * @var string The string to search for.
   */
  protected $match;
  /**
   * @var string The search type to be done.
   */
  protected $type;
  /**
   * @var string Specifies which element of $path to look for $match in.
   */
  protected $piece;
  /**
   * Constructor
   *
   * @param string $path Contains the filesystem path to be searched.
   * @param string $match The string to search for.
   * @param string $type The search type to be done. Must equal one of the class
   * constants: self::PREFIX, self::CONTAINS, self::SUFFIX. The default is
   * self::PREFIX.
   * @param integer $piece Specifies which element of $path to look for $match in.
   * Must be one of PATHINFO_DIRNAME, PATHINFO_BASENAME, PATHINFO_EXTENSION, or
   * PATHINFO_FILENAME. The default is PATHINFO_FILENAME.
   */
  public function __construct($path, $match, $type = self::PREFIX,
    $piece = PATHINFO_FILENAME) {
    $this->path = $path;
    $this->match = $match;
    $this->type = $type;
    $this->piece = $piece;
    $flat = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    // Fix for issue 65 try 2
    if (class_exists('FilesystemIterator')) {
      // Set flags to known working settings.
      $flags = FilesystemIterator::CURRENT_AS_FILEINFO
        | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
        | FilesystemIterator::UNIX_PATHS;
      $flat->setFlags($flags);
    };// if defined...
    parent::__construct($flat);
  }// function constructor
  /**
   * Required method to make filter work.
   *
   * @return mixed Returns a list of files that have a filename with the correct
   * prefix, suffix, or contains the matching string.
   */
  public function accept() {
    $pathinfo = self::pathinfo_utf($this->current(), $this->piece);
    switch ($this->type) {
      case self::PREFIX:
        if (0 === strpos($pathinfo, $this->match)) {
          return TRUE;
        };
        break;
      case self::CONTAINS:
        if (FALSE !== strpos($pathinfo, $this->match)) {
          return TRUE;
        };
        break;
      case self::SUFFIX:
        $pathinfo = self::strrev_utf($pathinfo);
        $match = self::strrev_utf($this->match);
        if (0 === strpos($pathinfo, $match)) {
          return TRUE;
        };
        break;
    };// switch $this->type ...
    return FALSE;
  }// function accept
  /**
   * Get a list of file names with prefix stripped from name.
   *
   * @param string $path Contains the filesystem path to be searched.
   * @param string $match The string to search for and strip from names.
   *
   * @return string Returns a list of stripped file names.
   */
  static public function getStrippedFiles($path, $match) {
    $files = new self($path, $match);
    $fileList = array();
    if (!empty($files)) {
      foreach ($files as $file) {
        $fileList[] = str_replace($match, '',
          self::pathinfo_utf($file->getPathname(), PATHINFO_FILENAME));
      };// foreach $files ...
    };// if !empty $files ...
    return $fileList;
  }// function getStrippedFiles
  /**
   * UTF-8 compatible version of pathinfo().
   *
   * @param string $path The path being checked.
   * @param integer $options You can specify which elements are returned with
   * optional parameter options. It composes from PATHINFO_DIRNAME,
   * PATHINFO_BASENAME, PATHINFO_EXTENSION and PATHINFO_FILENAME. It defaults to
   * return all elements.
   *
   * @return mixed  The following associative array elements are returned:
   * dirname, basename, extension (if any), and filename.
   * If options is used, this function will return a string else all elements
   * are returned as an associative array.
   */
  static public function pathinfo_utf($path, $options = 0) {
    $path = str_replace(array("\\","\\\\"), DS, $path);
    // Get rid of any double '/'s that might have crept in.
    $path = str_replace('//', DS, $path);
    if (strpos($path, '/') !== FALSE) {
      $pieces = explode('/', $path);
      $basename = end($pieces);
    } else {
      return FALSE;
    };
    if (empty($basename)) {
      return FALSE;
    };
    $dirname = substr($path, 0, strlen($path) - strlen($basename) - 1);
    if (strpos($basename, '.') !== FALSE) {
      $pieces = explode('.', $path);
      $extension = end($pieces);
      $filename = substr($basename, 0, strlen($basename) - strlen($extension) - 1);
    } else {
      $extension = '';
      $filename = $basename;
    };
    switch ($options) {
      case PATHINFO_DIRNAME:
        return $dirname;
      case PATHINFO_BASENAME:
        return $basename;
      case PATHINFO_EXTENSION:
        return $extension;
      case PATHINFO_FILENAME:
        return $filename;
    };// switch $options ...
    return array(
      'dirname' => $dirname,
      'basename' => $basename,
      'extension' => $extension,
      'filename' => $filename
    );
  }// function pathinfo_utf
  /**
   * UTF-8 safe string reverse that also supports HTML type numerical entities
   *
   * @param string $str String to be reversed.
   * @param bool $preserve_numbers Whither to reverse numbers or not.
   *
   * @return string Returns $str in reverse order.
   */
  protected function strrev_utf($str, $preserve_numbers = FALSE) {
    //split string into string-portions (1 byte characters, numerical entities or numbers)
    $parts = array();
    while ($str) {
      if ($preserve_numbers && preg_match('/^([0-9]+)(.*)$/', $str, $m)) {
        // number-flow
        $parts[] = $m[1];
        $str = $m[2];
      } elseif (preg_match('/^(\&#[0-9]+;)(.*)$/',$str,$m)) {
        // numerical entity
        $parts[] = $m[1];
        $str = $m[2];
      } else {
        $parts[] = substr($str, 0, 1);
        $str = substr($str, 1);
      };
    };// while $str ...
    return implode(array_reverse($parts), '');
  }// function strrev_utf
  /**
   * Class constants use to tell which type of match to do.
   */
  const PREFIX = 'prefix';
  const CONTAINS = 'contains';
  const SUFFIX = 'suffix';
}

