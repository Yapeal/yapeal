<?php
/**
 * Contains YapealNetworkConnection class.
 *
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
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
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
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Wrapper for API network connection.
 *
 * @package Yapeal
 * @subpackage Network
 */
class YapealNetworkConnection {
  /**
   * @var mixed Holds API connection object.
   */
  private $con;
  /**
   * Constructor
   */
  public function __construct() {
    $accept = 'text/xml,application/xml,application/xhtml+xml;q=0.9';
    $accept .= ',text/html;q=0.8,text/plain;q=0.7,image/png;q=0.6,*/*;q=0.5';
    $headers = array(
      'Accept: ' . $accept,
      'Accept-Language: en-us;q=0.9,en;q=0.8,*;q=0.7',
      'Accept-Charset: utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
      'Keep-Alive: 300'
    );
    require_once YAPEAL_EAC_HTTPREQUEST . 'eac_httprequest.class.php';
    $this->con = Singleton::get('httpRequest');
    if (FALSE === $this->con) {
      $mess = 'Could not get a connection to use for APIs';
      throw new YapealApiException($mess, 1);
    };
    $this->con->setOptions();
    foreach ($headers as $header) {
      $this->con->header($header, TRUE);
    };
  }// function __construct
  /**
   * Will retrieve the XML from API server.
   *
   * @param string $url URL of API needed.
   * @param array $postList A list of data that will be passed to the API server.
   * example: array(UserID => '123', apiKey => 'abc123', ...)
   *
   * @return mixed Returns XML data from API or FALSE for any connection error.
   */
  public function retrieveXml($url, $postList) {
    $result = $this->con->post($url, $postList);
    if (!$this->con->success) {
      $mess = 'API connection error: ' . $this->con->error;
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };
    return $result;
  }// function retrieveXml
}
?>
