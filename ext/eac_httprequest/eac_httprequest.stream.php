<?php
/**
 * @category 	eac::Framework / HTTP Request
 * @package		eac_httprequest.stream.php
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
require_once 'eac_httprequest.curl.php';
class streamRequest extends curlRequest {
	public $Version	= "v1.0.9, (Jun 17, 2009)";
	public $Signature	= "eac_httprequest.stream.php; %s; [www.KevinBurkholder.com]";
	public $Type = "STREAM";
	/**
	 * make the http request via streams
	 *
	 * @param string $url the request url
	 * @return mixed result of request
	 */
	protected function request($url) {
		$streamParams = array(
			'http' => array('method' => $this->options['CURLOPT_CUSTOMREQUEST'])
		);
		switch ($this->options['CURLOPT_CUSTOMREQUEST']) {
			case "POST":
				if (is_array($this->options['CURLOPT_POSTFIELDS'])) {
					$this->options['CURLOPT_POSTFIELDS'] = http_build_query($this->options['CURLOPT_POSTFIELDS']);
					$this->header('Content-Type: application/x-www-form-urlencoded', true, true);
				};
				$streamParams['http']['content'] = $this->options['CURLOPT_POSTFIELDS'];
				break;
			case "PUT":
				$data = @fread($this->options['CURLOPT_INFILE'], $this->options['CURLOPT_INFILESIZE']);
				$streamParams['http']['content'] = $data;
				rewind($this->options['CURLOPT_INFILE']);
				break;
		};
		foreach ($this->options as $opt => $val) {
			switch ($opt) {
				case 'CURLOPT_SSL_VERIFYPEER':
					$streamParams['http']['verify_peer'] = $val;
					$streamParams['http']['allow_self_signed'] = !$val;
					break;
				case 'CURLOPT_USERAGENT':
					if ($val) {
					  $streamParams['http']['user_agent'] = $val;
					};
					break;
				case 'CURLOPT_MAXREDIRS':
					$streamParams['http']['max_redirects'] = $val;
					break;
				case 'CURLOPT_TIMEOUT':
					$streamParams['http']['timeout'] = (float)($val * 1.0);
					break;
				case 'CURLOPT_ENCODING':
					if ($val) {
					  $this->header("Accept-Encoding: $val", true, true);
					};
					break;
				case 'CURLOPT_REFERER':
					if ($val) {
					  $this->header("Referer: $val", true, true);
					};
					break;
			};
		};
		if (count($this->request_headers) > 0) {
		// some conflict on which way headers should be presented - may depend on PHP version and/or server configuration
		//	$streamParams['http']['header'] = $this->request_headers;					// as an array
			$streamParams['http']['header'] = implode("\r\n", $this->request_headers);	// as a string
		};
		$ctx = stream_context_create($streamParams);
		$fp = @fopen($url, 'rb', false, $ctx);
  	if ($fp) {
			if (!$this->options['CURLOPT_NOBODY']) {
				$this->lastResult = @stream_get_contents($fp);
			} else {
				$this->lastResult = false;
			};
			$http_response_header = stream_get_meta_data($fp);
			$http_response_header = $http_response_header['wrapper_data'];
			if (!$this->options['CURLOPT_ASYNCRONOUS']) {
			  @fclose($fp);
			};
		};
		// $http_response_header - see http://php.net/manual/en/reserved.variables.httpresponseheader.php
		$this->_parseHeaders($http_response_header, $url);
		unset($streamParams['http']['content'], $streamParams['http']['header']);
		$this->info['http_context'] = $streamParams['http'];
		if ($this->info['http_code'] == 401) {
			if ($this->_detectAuth($streamParams['http']['method'], $url)){
			  return $this->request($url);
			};
		};
		if ($this->options['CURLOPT_HEADER'] && substr($this->info['http_code'], 0, 1) == 2) {
			$headers = "";
			foreach ($this->response_headers as $k => $v) {
			  $headers .= $k . ': ' . $v . "\r\n";
			};
			$this->lastResult = ($this->lastResult) ? $headers . "\r\n" . $this->lastResult : $headers;
		};
		if (isset($this->options['CURLOPT_FILE']) && is_resource($this->options['CURLOPT_FILE'] && $this->lastResult)) {
			fwrite($this->options['CURLOPT_FILE'], strlen($this->lastResult));
		};
		$this->error = $php_errormsg;
		if (!$this->options['CURLOPT_RETURNTRANSFER']) {
			echo $this->lastResult;
			return true;
		};
		$this->info['request_header'] = implode("\r\n", $this->request_headers);
		return ($this->options['CURLOPT_ASYNCRONOUS']) ? $fp : $this->lastResult;
	}
	/**
	 * detect authentication
	 *
	 * @param string $method the http method
	 * @param string $url the requested url
	 * @return bool success/failure
	 */
  private function _detectAuth($method, $url) {
 		if (!@include_once('eac_httprequest.auth.php')){
		  return false;
 		};
	 	if (
			$auth = httpRequest_auth::getAuthentication($method, $url, $this->options['CURLOPT_USERPW'], $this->response_headers)) {
	 		$this->header($auth, true, !$this->options['CURLOPT_UNRESTRICTED_AUTH']);
	 		return true;
	 	};
	 	return false;
  }
	/**
	 * function for reading and processing headers
	 *
	 * @param string $response_header headers from $http_response_header or stream_get_meta_data
	 * @param string $url the requested url
	 * @return integer size of headers
	 */
	private function _parseHeaders($response_header, $url) {
		$hdrsize = 0;
		if (!is_array($response_header)) {
		  $response_header = array('HTTP/1.0 200 OK');
		};
		foreach ($response_header as $header) {
			$hdrsize += parent::parseHeader(null, $header, $url);
		};
		$this->info['header_size'] = $hdrsize;
    return $hdrsize;
	}
	/**
	 * get last result
	 *
	 * @param resource|null $fp connection resource returned when CURLOPT_ASYNCRONOUS = true
	 * @return mixed result of last request
	 */
	public function getLastResult($fp = null) {
		if (is_resource($fp)) {
			$this->lastResult = $this->_gzdecode(@stream_get_contents($fp));
			@fclose($fp);
		} else if (is_string($fp)) {
			return $fp;
		};
		return $this->lastResult;
	}
}
?>
