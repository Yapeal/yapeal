<?php
/**
 * CurlRequest class
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
 * Wrapper class for CURL
 *
 * Original idea took from post by roman dot ivasyuk at gmail dot com at
 * {@link http://us3.php.net/manual/en/function.curl-exec.php#80442 php net}
 *
 * @package Yapeal
 * @subpackage cURL
 */
class CurlRequest {
  /**
   * @var object Handle to CURL object.
   */
  private $ch;
  /**
   * Init curl session in constructor.
   *
   * $params=array(
   *   'method' => '',
   *   'timeout' => 0,
   *   'url' => '',
   *   ['content' => '',]
   *   ['cookie' => '',]
   *   ['header' => '',]
   *   ['host' => '',]
   *   ['login' => '',]
   *   ['password' => '',]
   *   ['referer' => '',]
   *   ['user_agent' => '']
   * );
   *
   * @param array $params An array of params that are used to set curl options
   *
   * @return void
   */
  public function __construct($params) {
    $this->ch = curl_init();
    $curl = curl_version();
    $user_agent = 'Yapeal/'. YAPEAL_VERSION . YAPEAL_STABILITY . ' (' . PHP_OS .
      ' ' . php_uname('m') . ') libcurl/' . $curl['version'];
    $header = array(
      "Accept: text/xml,application/xml,application/xhtml+xml;q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;q=0.6,*/*;q=0.5",
      "Accept-Language: en-us;q=0.9,en;q=0.8,*;q=0.7",
      "Accept-Charset: utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6",
      "Keep-Alive: 300"
    );
    $options = array(
      CURLOPT_ENCODING => '',
      CURLOPT_FOLLOWLOCATION => FALSE,
      CURLOPT_HEADER => TRUE,
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_SSL_VERIFYHOST => FALSE,
      CURLOPT_SSL_VERIFYPEER => FALSE,
      CURLOPT_VERBOSE => FALSE
    );
    if (isset($params['user_agent']) && $params['user_agent']) {
      $options[CURLOPT_USERAGENT] = $params['user_agent'];
    } else {
      $options[CURLOPT_USERAGENT] = $user_agent;
    };
    // Set unchanging curl options as a block.
    curl_setopt_array($this->ch, $options);
    // Add optional user params to preset header.
    if (isset($params['host']) && $params['host']) {
      $header[] = "Host: " . $params['host'];
    };
    if (isset($params['header']) && $params['header']) {
      $header[] = $params['header'];
    };
    @curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
    // Set optional user params.
    if (isset($params['referer'])) {
      @curl_setopt($this->ch, CURLOPT_REFERER, $params['referer']);
    };
    if (isset($params['cookie'])) {
      @curl_setopt($this->ch, CURLOPT_COOKIE, $params['cookie']);
    };
    if (isset($params['login']) && isset($params['password'])) {
      @curl_setopt($this->ch, CURLOPT_USERPWD, $params['login'] . ':' . $params['password']);
    };
    // Set any method dependent params.
    switch ($params['method']) {
      case 'HEAD':
        @curl_setopt($this->ch, CURLOPT_NOBODY, 1);
      break;
      case 'POST':
        @curl_setopt($this->ch, CURLOPT_POST, TRUE);
        @curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params['content']);
      break;
      case 'GET':
      break;
    };
    // Set required user params.
    @curl_setopt($this->ch, CURLOPT_TIMEOUT, $params['timeout']);
    @curl_setopt($this->ch, CURLOPT_URL, $params['url']);
  }
  /**
   * Make curl request.
   *
   * @return array  'header','body','curl_error','http_code','last_url'
   */
  public function exec() {
    $response = curl_exec($this->ch);
    $error = curl_error($this->ch);
    $result = array(
      'header' => '',
      'body' => '',
      'curl_error' => '',
      'http_code' => '',
      'last_url' => ''
    );
    if ($error != "") {
      $result['curl_error'] = $error;
      return $result;
    };
    $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
    $result['header'] = substr($response, 0, $header_size);
    $result['body'] = substr($response, $header_size);
    $result['http_code'] = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    $result['last_url'] = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
    return $result;
  }
  /**
   * Close curl connection before exiting.
   *
   * @return void
   */
  public function __destruct() {
    curl_close($this->ch);
  }
}
?>
