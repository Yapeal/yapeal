<?php
/**
 * @category 	eac::Framework / HTTP Request
 * @package		eac_httprequest.curl.php
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	2009, Kevin Burkholder / EarthAsylum Consulting
 * @version 	1.0 for PHP version 5
 * @license		http://www.kevinburkholder.com/sw_license.php
 */
/* +------------------------------------------------------------------------+
   | Copyright 2009, Kevin Burkholder				www.KevinBurkholder.com |
   | Some rights reserved.													|
   |																		|
   | This work is licensed under the Creative Commons GNU Lesser General	|
   | Public License. To view a copy of this license, visit					|
   |	 http://creativecommons.org/licenses/LGPL/2.1/						|
   |																		|
   | Please see the License_LGPL_x.x.txt file for redistribution and use	|
   | restrictions. If this file was not included with the distribution of	|
   | this software, it may be found here:									|
   |	 http://www.kevinburkholder.com/sw_license.php						|
   |																		|
   | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS	|
   | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT		|
   | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR	|
   | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT	|
   | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,	|
   | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT		|
   | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,	|
   | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY	|
   | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT	|
   | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE	|
   | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.	|
   |																		|
   +------------------------------------------------------------------------+
   |					 Kevin Burkholder									|
   |					 EarthAsylum Consulting								|
   |					 KBurkholder@EarthAsylum.com						|
   +------------------------------------------------------------------------+ */
/*  -----  Documentation for the eac_httprequest classes may be found in readme_httprequest.txt  -----   */
/*  -----         Additional examples and notes may be found in eac_httprequest.test.php         -----   */
class curlRequest {
	public $Version = "v1.0.9, (Jun 17, 2009)";
	public $Signature	= "eac_httprequest.curl.php; %s; [www.KevinBurkholder.com]";
	public $Type = "CURL";
	/**
	 * request headers
	 *
	 * @var indexed array
	 */
	protected $request_headers = array();
	/**
	 * Sticky request headers
	 *
	 * @var indexed array
	 */
	private $saved_headers = array();
	/**
	 * response headers
	 *
	 * @var associative array
	 */
	protected $response_headers = array();
	/**
	 * raw response headers
	 *
	 * @var indexed array
	 */
	protected $raw_headers = array();
	/**
	 * response info
	 *
	 * @var associative array
	 */
	protected $info = array();
	/**
	 * class options
	 *
	 * @var associative array
	 */
	protected $options = array();
	/**
	 * Sticky options
	 *
	 * @var associative array
	 */
	private $savedOptions = array();
	/**
	 * request result
	 *
	 * @var string
	 */
	protected $lastResult = null;
	/**
	 * request status
	 *
	 * @var bool
	 */
	public $success = null;
	/**
	 * error message
	 *
	 * @var string
	 */
	public $error = null;
	/**
	 * Optional callback function
	 *
	 * @var mixed
	 */
	private $callback = false;
	/**
	 * eac_httprequest.cache object
	 *
	 * @var object
	 */
	private $cache = null;
	/**
	 * Constrtuctor method
	 *
	 * @param array $options curl_setopt options
	 */
	public function __construct($options = null) {
		$this->Signature = sprintf($this->Signature, $this->Version);
		$this->setOptions($options);
	}
	/**
	 * set options
	 * Any curl_setopt optian can be used.
	 * Child classes should ignore unsupported options.
	 *
	 * @param array $options curl_setopt options
	 * @return void
	 */
	public function setOptions($options = null) {
		$this->request_headers = array();
		$this->saved_headers = array();
		$this->options['CURLOPT_HEADER'] = 0;// no headers in result
		$this->options['CURLOPT_NOBODY'] = 0;// include body in response
		$this->options['CURLOPT_USERAGENT']	= YAPEAL_APPLICATION_AGENT;// default user agent
		$this->options['CURLOPT_FOLLOWLOCATION'] = 1;// allow redirection
		$this->options['CURLOPT_FORBID_REUSE'] = 0;// allow connection re-use
		$this->options['CURLOPT_LOW_SPEED_LIMIT'] = 10;// min bps to be considered slow
		$this->options['CURLOPT_LOW_SPEED_TIME'] = ceil(curlRequest::TIMEOUT / 4);// how long to wait on slow connection
		$this->options['CURLOPT_MAXCONNECTS'] = 5;// max number of persistent connections to keep around.
		$this->options['CURLOPT_MAXREDIRS'] = 5;// max redirects
		$this->options['CURLOPT_CONNECTTIMEOUT'] = curlRequest::TIMEOUT / 2;// max time in seconds to wait for a new connection.
		$this->options['CURLOPT_TIMEOUT'] = curlRequest::TIMEOUT;// max time in seconds transfer is allowed to take.
		$this->options['CURLOPT_ENCODING'] = 'gzip';// allow gzip compression
		$this->options['CURLOPT_RETURNTRANSFER'] = 1;// return results as string
		$this->options['CURLOPT_BINARYTRANSFER'] = 0;// no binary transfer
        $this->options['CURLOPT_SSL_VERIFYPEER'] = 1; // verify ssl certs
        $this->options['CURLOPT_SSL_VERIFYHOST'] = 2; // verify ssl host
        $this->options['CURLOPT_CAINFO'] =  YAPEAL_CONFIG . 'eveonline.crt';
        $this->options['CURLOPT_COOKIEJAR'] = YAPEAL_CACHE . 'curl_cookies.txt';
		$this->options['CURLOPT_REFERER'] = 'http://code.google.com/p/yapeal/';
		$this->options['CURLOPT_UNRESTRICTED_AUTH'] = 1;// do not pass authentication to multiple locations

        /**
         * Some versions of cURL use NSS, others use OpenSSL. We can support either.
         */
        $curl_version = curl_version();
        $ssl_version = $curl_version['ssl_version'];
        $has_nss = (strpos($ssl_version, "NSS") > -1);

        if($has_nss) {
            $this->options['CURLOPT_SSL_CIPHER_LIST'] = 'rsa_aes_128_sha,rsa_aes_256_sha,rsa_3des_sha,rsa_rc4_128_sha,rsa_rc4_128_md5';
        } else {
            $this->options['CURLOPT_SSL_CIPHER_LIST'] = 'AES128-SHA AES256-SHA DES-CBC3-SHA RC4-SHA RC4-MD5';
        }

        $this->savedOptions = $this->options;
		if (is_array($options)) {
			foreach($options as $opt => $val) {
				$this->setOption($opt, $val);
			};
		};
	}
	/**
	 * set a single option
	 *
	 * @param string $option curl_setopt option name
	 * @param mixed $value curl_setopt value
   * @param bool $_temp
	 * @return void
	 */
	public function setOption($option, $value, $_temp = false) {
		$option = str_replace(array('HTTP_', 'STREAMS_', 'SOCKET_'), 'CURLOPT_', $option);
		$this->options[$option] = $value;
		if ($option == 'CURLOPT_ASYNCRONOUS') {
			$this->options['CURLOPT_NOBODY'] = ($value) ? 1 : 0;
			$this->options['CURLOPT_TIMEOUT'] = ($value) ? 1 : $this->options['CURLOPT_TIMEOUT'];
		} else if (!$_temp) {
			$this->savedOptions[$option] = $value;
		};
		if ($option == 'CURLOPT_CACHE') {
			if ($value && (is_dir($value) || @mkdir($value, 0777)) && @include_once('eac_httprequest.cache.php')) {
				$this->cache = new httpRequest_cache($value);
			}	else {
				$this->cache = null;
			};
		};
	}
	/**
	 * set a single temporary option
	 *
	 * @param string $option curl_setopt option name
	 * @param mixed $value curl_setopt value
	 * @return void
	 */
	private function _tempOption($option, $value) {
		$this->setOption($option, $value, true);
	}
	/**
	 * set a callback function
	 * myfunction(body, header=null) {...}
	 *
	 * @param string|array $function 'function_name' | array('class_name', 'function_name')
	 * @return void
	 */
	public function setCallback($function) {
		$this->callback = $function;
	}
	/**
	 * set default headers
	 *
	 * @param string|null $header to copy a single header
	 * @return void
	 */
	public function copyHeaders($header = null) {
		$toIgnore = array('cookie', 'accept-encoding', 'user-agent', 'host',
			'connection', 'referer', 'cache-control');
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) == "HTTP_") {
				if (substr($key, 5, 1) != "X") {
					$key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', str_replace('HTTP_', '', $key)))));
				} else {
					$key = str_replace('_', '-', str_replace('HTTP_', '', $key));
				};
				if ($header && strtolower($header) != strtolower($key)) {
					continue;
				};
				if (!in_array(strtolower($key),$toIgnore)) {
					$this->header($key . ': ' . $value);
				}	else if (strtolower($key) == 'referer' && $value) {
					$this->setOption('CURLOPT_REFERER', $value);
				};
			};
		};
	}
	/**
	 * set cookie header
	 *
	 * @param string|null $cookie to copy a single cookie
	 * @return void
	 */
	public function copyCookies($cookie = null) {
		if (!is_array($_COOKIE)) {
			return;
		};
		$cookies = array();
		foreach ($_COOKIE as $key => $item) {
			if ($cookie && $cookie != $key) {
				continue;
			};
			$cookies[] = $key . '=' . $item;
		};
		if (count($cookies) > 0) {
			$this->header('Cookie: ' . implode('; ', $cookies));
		};
	}
	/**
	 * add a request header
	 *
	 * @param string $header header (header-name: header-value)
	 * @param bool $replace true = replace header
	 * @param bool $_temp true = temporary/non-sticky (used internally)
   * @param string $_key
   * @param string $_value
	 * @return void
	 */
	public function header(
		$header, $replace = false, $_temp = false, $_key = null, $_value = null) {
		if (!$_key) {
			$this->splitHeader($header, $_key, $_value);
			$_headerArray = &$this->request_headers;
		} else {
			$_headerArray = &$this->saved_headers;
		};
		if ($replace) {
			$headers = array();
			foreach ($_headerArray as $request) {
				list ($k, $v) = explode(': ', $request, 2);
				if ($_key != $k) {
					$headers[] = $request;
				};
			};
			if ($_value) {
				$headers[] = $_key . ': ' . $_value;
			};
			$_headerArray = $headers;
		} else {
			if (!in_array($header, $_headerArray)) {
				$_headerArray[] = $header;
			};
		};
		if (!$_temp) {
			$this->header($header, $replace, true, $_key, $_value);
		};
	}
	/**
	 * http GET request
	 *
	 * @param string $url the request url
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function get($url, $options = null) {
		$this->_resetOptions($options);
		if ($this->cache && ($cache = $this->cache->isCached($url))) {
			$this->header($cache, true, true);
		};
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'GET';
		$this->options['CURLOPT_HTTPGET'] = 1;
		$result = $this->httpRequest($url);
		if ($this->success && $this->cache && $this->info['http_code'] != 304) {
			$this->cache->writeCache($url, $this->lastResult, $this->getHeaders());
		};
		return $result;
	}
	/**
	 * http POST request
	 *
	 * @param string $url the request url
	 * @param mixed $fields POST variables
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function post($url, $fields = null, $options = null) {
		$this->_resetOptions();
		if (is_array($fields) && !$this->getRequest('Content-Type')) {
			$fields = http_build_query($fields, NULL, '&');
			$this->header('Content-Type: application/x-www-form-urlencoded', true, true);
		};
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'POST';
		$this->options['CURLOPT_POST'] = 1;
		$this->options['CURLOPT_POSTFIELDS'] = $fields;
		return $this->httpRequest($url);
	}
	/**
	 * http HEAD request
	 *
	 * @param string $url the request url
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function head($url, $options = null) {
		$this->_resetOptions($options);
		$this->options['CURLOPT_HEADER'] = 1;
		$this->options['CURLOPT_NOBODY'] = 1;
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'HEAD';
		return $this->httpRequest($url);
	}
	/**
	 * http PUT request
	 *
	 * @param string $url the request url
	 * @param string $fn_or_data '@filename' or data string
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function put($url, $fn_or_data, $options = null) {
		$this->_resetOptions($options);
		$fp = fopen('php://temp', 'rw');
		if (substr($fn_or_data, 0, 1) == '@') {
			$fn_or_data = substr($fn_or_data, 1);
			$length = fwrite($fp, @file_get_contents($fn_or_data, FILE_USE_INCLUDE_PATH));
			if (!$length) {
				$fn_or_data = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'] . "/" . $fn_or_data);
				$length = fwrite($fp, @file_get_contents($fn_or_data));
			};
		} else {
			$length = fwrite($fp, $fn_or_data);
		};
		rewind($fp);
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'PUT';
		$this->options['CURLOPT_PUT'] = 1;
		$this->options['CURLOPT_INFILE'] = $fp;
		$this->options['CURLOPT_INFILESIZE'] = $length;
		$return = $this->httpRequest($url);
		fclose($fp);
		return $return;
	}
	/**
	 * http DELETE request
	 *
	 * @param string $url the request url
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function delete($url, $options = null) {
		$this->_resetOptions($options);
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'DELETE';
		return $this->httpRequest($url);
	}
	/**
	 * http OPTIONS request
	 *
	 * @param string $url the request url or '*'
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function options($url, $options = null) {
		$this->_resetOptions($options);
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'OPTIONS';
		return $this->httpRequest($url);
	}
	/**
	 * http TRACE request
	 *
	 * @param string $url the request url
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function trace($url, $options = null) {
		$this->_resetOptions($options);
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'TRACE';
		return $this->httpRequest($url);
	}
	/**
	 * http CONNECT request
	 *
	 * @param string $url the request url
	 * @param array $options additional options
	 * @return mixed result of request
	 */
	public function connect($url, $options = null) {
		$this->_resetOptions($options);
		$this->options['CURLOPT_CUSTOMREQUEST'] = 'CONNECT';
		return $this->httpRequest($url);
	}
	/**
	 * http request
	 *
	 * @param string $url the request url
	 * @return mixed result of request
	 */
	protected function httpRequest($url) {
		$this->response_headers = array();
		//$this->header('X-EAC-Request: ' . $this->Signature);
		$url = $this->_parseURL($url);
		if (isset($this->options['CURLOPT_USERPWD']) && !isset($this->options['CURLOPT_HTTPAUTH'])) {
			$this->options['CURLOPT_HTTPAUTH'] = CURLAUTH_ANY;
		};
		if (isset($this->options['CURLOPT_FILE']) && is_string($this->options['CURLOPT_FILE'])) {
			$this->options['CURLOPT_FILE'] = @fopen($this->options['CURLOPT_FILE'], 'a+');
		};
		// the request must execute the request and set the lastResult, info, and error variables.
		$result = $this->request($url);
		if (substr($this->info['http_code'], 0, 1) == 2 || $this->info['http_code'] == 304) {
			if ($this->cache && $this->info['http_code'] == 304) {
				$this->lastResult = $this->cache->readCache($url);
			};
			if (is_string($this->lastResult)) {
				if (!isset($this->response_headers['Content-Length'])) {
					$this->response_headers['Content-Length'] = strlen($this->lastResult);
					$this->info['download_content_length'] = strlen($this->lastResult);
				};
				if (isset($this->response_headers['Content-Encoding']) &&
					stripos($this->response_headers['Content-Encoding'], 'gzip') !== false) {
					$this->lastResult = $this->_gzdecode($this->lastResult);
					$this->info['decoded_content_length'] = strlen($this->lastResult);
				};
				if ($this->callback) {
					$this->lastResult = call_user_func($this->callback, $this->lastResult, $this->response_headers);
				};
			};
			$this->success = true;
		} else {
			$this->success = false;
			if (empty($this->error)) {
				$this->error = $this->getHeaders('HTTP/1.x');
			}
			$this->lastResult = $this->error;
		};
		// allow for child classes to return a resource in their request() method
		return (is_resource($result)) ? $result : $this->lastResult;
	}
	/**
	 * make the http request via curl
	 * this method should be overridden by any child class
	 *
	 * @param string $url the request url
	 * @return mixed result of request
	 */
	protected function request($url) {
		$ch = curl_init($url);
		// If POSTing a body larger than 1k, curl sends "Expect: 100-Continue" header
		// and waits for a "HTTP/1.x 100 Continue" response before sending the body.
		// To avoid this, use: $this->header('Expect:');
		if (count($this->request_headers) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->request_headers);
		};
		foreach ($this->options as $opt => &$val) {
			if (is_string($opt) && defined($opt)) {
				$opt = constant($opt);
				curl_setopt($ch, $opt, $val);
			};
		};
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, 'parseHeader'));
		$this->lastResult = curl_exec($ch);
		$this->info = array_merge($this->info, curl_getinfo($ch));
		$this->error = curl_error($ch);
		curl_close($ch);
		return $this->lastResult;
	}
	/**
	 * parse the url and set appropriate options
	 *
	 * @param string $url
	 * @return string url
	 */
	private function _parseURL($url) {
		$url = str_replace('&amp;', '&', $url);
		$urlParts = parse_url($url);
		if (!empty($urlParts['port'])) {
			$this->options['CURLOPT_PORT'] = $urlParts['port'];
		};
		if (!empty($urlParts['user'])) {
			$this->options['CURLOPT_USERPWD'] = $urlParts['user'];
			if (!empty($urlParts['pass'])) {
				$this->options['CURLOPT_USERPWD'] .= ':' . $urlParts['pass'];
			};
		};
		if (!isset($this->options['CURLOPT_PORT'])) {
			if ($urlParts['scheme'] == 'https') {
				$this->options['CURLOPT_PORT'] = 443;
			}	else {
				$this->options['CURLOPT_PORT'] = 80;
			};
		};
		$url = $urlParts['scheme'] . "://" . $urlParts['host'];
		if ($this->Type == 'STREAM' && $this->options['CURLOPT_PORT'] != 80 &&
			$this->options['CURLOPT_PORT'] != 443) {
			$url .= ':' . $this->options['CURLOPT_PORT'];
		};
		$url .= $urlParts['path'];
		if (!empty($urlParts['query'])) {
			$url .= '?' . $urlParts['query'];
		};
		if (!empty($urlParts['fragment'])) {
			$url .= '#' . $urlParts['fragment'];
		};
		return $url;
	}
	/**
	 * callback function for reading and processing a header
	 *
	 * @param object $ch the curl object
	 * @param string $header the header string
	 * @param string $url the requested url
	 * @return integer size of headers
	 */
	protected function parseHeader($ch, $header, $url = null) {
		if (!isset($this->info['url'])) {
			$this->info['url'] = $url;
		};
		if (strlen($header) > 2) {
			$hdr = $header;
			$this->splitHeader($hdr, $key, $value);
			// cURL automatically sets these values but we do it here for the child classes
			switch(strtolower($key)) {
				case 'http/1.0':
				case 'http/1.1':
					list($this->info['http_code'], $this->info['http_status']) = explode(' ', $value, 2);
					break;
				case 'content-type':
					$this->info['content_type'] = $value;
					break;
				case 'content-length':
					$this->info['download_content_length'] = $value;
					break;
				case 'location':
					if (isset($this->info['redirect_count'])) {
						++$this->info['redirect_count'];
					}	else {
						$this->info['redirect_count'] = 1;
					};
					if (!empty($url) && substr($value, 0, 4) != 'http') {
						$urlParts = parse_url($url);
						$url = $urlParts['scheme'] . '://' . $urlParts['host'];
						$url .= str_replace('//', '/', '/' . $value);
						$value = $url;
					};
					$this->info['url'] = $value;
					break;
			};
			$this->response_headers[$key] = $value;
			$this->raw_headers[] = trim($header);
		};
		return strlen($header);
	}
	/**
	 * send (email) last result
	 *
	 * @param string $to the 'to' email address
	 * @param string|bool $from the 'from' email address
	 * @param string|bool $subject the email subject
	 * @param string|array $xheaders extra headers (delimited by ';' or an array)
	 * @param string $EOL end-of-line character (\n or \r\n)
	 * @return int mail status
	 */
	public function sendLastResult($to, $from = false, $subject = false, $xheaders = array(), $EOL = "\n") {
		if (!$from) {
			if (isset($_SERVER['SERVER_ADMIN'])) {
				$from = $_SERVER['SERVER_ADMIN'];
			} else if (isset($_SERVER['HTTP_HOST'])){
				$from = 'webmaster@' . $_SERVER['HTTP_HOST'];
			};
		};
		if (!$subject) {
			$subject = array_shift(explode(';', $this->Signature)) . ' Results';
		};
		if (is_string($xheaders)) {
			$xheaders = explode(";", $xheaders);
		};
		$headers  = 'X-Mailer: ' . $this->Signature . $EOL;
		$headers .=	'From: ' . $from . $EOL;
		if (isset($this->info['content_type'])) {
			$headers .= 'Content-Type: ' . $this->info['content_type'] . $EOL;
		};
		if (is_array($xheaders)) {
			foreach ($xheaders as $header) {
				$headers .= $header . $EOL;
			};
		};
		return mail($to, $subject, $this->lastResult, $headers);
	}
	/**
	 * Get http statuscode (Tarjei)
	 *
	 * @return int http status code
	 */
	public function getHttpStatus() {
	  return $this->info['http_code'];
	}
	/**
	 * get last result
	 *
   * @param string|null $fp Ignored
	 * @return mixed result of last request
	 */
	public function getLastResult($fp = null) {
		return $this->lastResult;
	}
	/**
	 * get response info
	 *
	 * @param string|null $field to get a single info field
	 * @return mixed info value or array
	 */
	public function getInfo($field = null) {
		if ($field) {
			return $this->getInfoField($field);
		};
		return $this->info;
	}
	/**
	 * Get arbitary info field (Tarjei)
	 *
	 * @param string $field to get a single info field
	 * @return mixed info value
	 */
	public function getInfoField($field) {
		return (isset($this->info[$field])) ? $this->info[$field] : null;
	}
	/**
	 * get response headers
	 *
	 * @param string|null $header to get a single header
	 * @return array response headers
	 */
	public function getHeaders($header = null) {
		if ($header == 'HTTP/1.x') {
			if (isset($this->response_headers['HTTP/1.1'])) {
				return $this->response_headers['HTTP/1.1'];
			} else if (isset($this->response_headers['HTTP/1.0'])) {
				return $this->response_headers['HTTP/1.0'];
			};
		};
		if ($header) {
			return (isset($this->response_headers[$header])) ? $this->response_headers[$header] : null;
		};
		return $this->response_headers;
	}
	/**
	 * get raw response headers
	 *
	 * @return array response headers
	 */
	public function getRawHeaders() {
		return $this->raw_headers;
	}
	/**
	 * get request headers
	 *
	 * @param string|null $header to get a single header
	 * @return array request headers
	 */
	public function getRequest($header = null) {
		if (isset($this->info['request_header'])) {
			$request_headers = explode("\r\n", rtrim($this->info['request_header'], "\r\n"));
		}	else {
			$request_headers = $this->request_headers;
		};
		if ($header) {
			$headers = array();
			foreach ($request_headers as $request) {
				$this->splitHeader($request, $key, $value);
				$headers[$key] = $value;
			};
			return (isset($headers[$header])) ? $headers[$header] : null;
		} else {
			return $request_headers;
		};
	}
	/**
	 * get options array
	 *
	 * @param string $option to get a single option
	 * @return array options
	 */
	public function getOptions($option = null) {
		if ($option) {
			$option = str_replace(array('HTTP_', 'STREAMS_', 'SOCKET_'), 'CURLOPT_', $option);
			return (isset($this->options[$option])) ? $this->options[$option] : null;
		};
		$options = array();
		foreach ($this->options as $key => $value) {
			$options['HTTP_' . substr($key, 8)] = $value;
		};
		return $options;
	}
	/**
	 * unset function parameters
	 *
	 * @param array $options curl_setopt options
	 * @return void
	 */
	private function _resetOptions($options = null) {
		$this->request_headers = $this->saved_headers;
		if (isset($this->options['CURLOPT_ASYNCRONOUS'])) {
		  $async = $this->options['CURLOPT_ASYNCRONOUS'];
		} else {
			$async = 0;
		};
		$this->options = $this->savedOptions;
		unset($this->options['CURLOPT_HTTPGET']);
		unset($this->options['CURLOPT_POST']);
		unset($this->options['CURLOPT_POSTFIELDS']);
		unset($this->options['CURLOPT_PUT']);
		unset($this->options['CURLOPT_INFILE']);
		unset($this->options['CURLOPT_INFILESIZE']);
		unset($this->options['CURLOPT_CUSTOMREQUEST']);
		if ($async) {
			$this->setOption('CURLOPT_ASYNCRONOUS',$async);
		};
		$this->info = array();
		$this->raw_headers = array();
		if (is_array($options)) {
			foreach($options as $opt => $val) {
				$this->_tempOption($opt, $val);
			};
		};
	}
	/**
	 * split a header into key,value
	 *
	 * @param string|null $header an http header string
   * @param string $key
   * @param string $value
	 * @return array|null header_name=>header_value (updates header/key/value parameters)
	 */
	private function splitHeader(&$header, &$key, &$value) {
		if (!$header) {
			return null;
		};
		list($key, $value) = explode(' ', rtrim($header, "\r\n"), 2);
		if (substr($key, 0, 1) != "X") {
			$key = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $key))));
		};
		$key = str_replace(array('Http','Www'), array('HTTP','WWW'), rtrim($key, ':'));
		$header = $key . ': ' . trim($value);
		return array($key => $value);
	}
	/**
	 * gzdecode function (missing from PHP)
	 *
	 * @param string $data gzencoded data
	 * @return string decoded data
	 */
	protected function _gzdecode($data = null) {
		if (!$data) {
			return '';
		};
		if (strcmp(substr($data, 0, 2), "\x1f\x8b")) {
			return $data;
		};
		$flags = ord(substr($data, 3, 1));
		$headerlen = 10;
		$extralen = 0;
		$filenamelen = 0;
		if ($flags & 4) {
			$extralen = unpack('v', substr($data, 10, 2));
			$extralen = $extralen[1];
			$headerlen = $headerlen + 2 + $extralen;
		};
		// Filename
		if ($flags & 8) {
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		};
		// Comment
		if ($flags & 16) {
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		};
		// CRC at end of file
		if ($flags & 2) {
			$headerlen += 2;
		};
		$unpacked = gzinflate(substr($data, $headerlen));
		if ($unpacked === false) {
			$unpacked = $data;
		};
		$this->info['decoded_content_length'] = strlen($unpacked);
		return $unpacked;
	}
	/**
	 * Constant used in several of the connection timeout values.
	 */
	const TIMEOUT = 45;
}
?>
