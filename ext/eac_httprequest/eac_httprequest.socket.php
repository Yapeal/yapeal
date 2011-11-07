<?php
/**
 * @category 	eac::Framework / HTTP Request
 * @package		eac_httprequest.socket.php
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
class socketRequest extends curlRequest {
	public $Version = "v1.0.9, (Jun 17, 2009)";
	public $Signature	= "eac_httprequest.socket.php; %s; [www.KevinBurkholder.com]";
	public $Type = "SOCKET";
	/**
	 * make the http request via sockets
	 *
	 * @param string $url the request url
	 * @return mixed result of request
	 */
	protected function request($url) {
		$urlParts = parse_url($url);
		if ($urlParts['scheme'] == 'https') {
			$urlParts['host'] = 'ssl://'.$urlParts['host'];
		};
		if (!empty($urlParts['query'])) {
		  $urlParts['path'] = $urlParts['path'] . '?' . $urlParts['query'];
		};
		if (!empty($urlParts['fragment'])){
		  $urlParts['path'] = $urlParts['path'] . '#' . $urlParts['fragment'];
		};
		$socketParams = $this->options['CURLOPT_CUSTOMREQUEST'] . ' ';
		$socketParams .= $urlParts['path'] . ' ' . $_SERVER['SERVER_PROTOCOL'];
		$socketParams .= "\r\nHost: " . $urlParts['host'] . "\r\n";
		foreach ($this->options as $opt => $val) {
			switch ($opt) {
				case 'CURLOPT_USERAGENT':
					if ($val) {
					  $this->header("User-Agent: $val", true, true);
					};
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
		if (is_array($this->options['CURLOPT_POSTFIELDS'])) {
			$this->options['CURLOPT_POSTFIELDS'] = http_build_query($this->options['CURLOPT_POSTFIELDS']);
			$this->header('Content-Type: application/x-www-form-urlencoded', true, true);
		};
		$this->header("Connection: close", true, true);
		$socketParams .= implode("\r\n", $this->request_headers) . "\r\n";
		$socketParams .= "\r\n";
		$request_headers = $socketParams;
		switch ($this->options['CURLOPT_CUSTOMREQUEST']) {
			case "POST":
				$socketParams .= $this->options['CURLOPT_POSTFIELDS'];
				break;
			case "PUT":
				$data = @fread($this->options['CURLOPT_INFILE'], $this->options['CURLOPT_INFILESIZE']);
				$socketParams .= $data;
				rewind($this->options['CURLOPT_INFILE']);
				break;
		};
		$fp = fsockopen($urlParts['host'], $this->options['CURLOPT_PORT'], $errno, $errstr);
		socket_set_timeout($fp, $this->options['CURLOPT_TIMEOUT']);
		$this->lastResult = '';
		$http_response_header = '';
		if ($fp) {
			fwrite($fp, $socketParams);
			while (!feof($fp)) {
				$buf = fgets($fp, 128);
				if ($buf == "\r\n") {
				  break;
				};
				$http_response_header .= $buf;
				$this->parseHeader(null, $buf, $url);
			};
			if (isset($this->response_headers['Location']) &&
				$this->options['CURLOPT_FOLLOWLOCATION']) {
				if ($this->info['redirect_count'] <= $this->options['CURLOPT_MAXREDIRS']) {
					$location = $this->response_headers['Location'];
					unset($this->response_headers['Location']);
					@fclose($fp);
					return $this->httpRequest($location);
				};
			};
			if (!$this->options['CURLOPT_NOBODY']) {
				while (!feof($fp)) {
				  $this->lastResult .= fgets($fp, 128);
				};
			} else {
				$this->lastResult = false;
			};
			if (!$this->options['CURLOPT_ASYNCRONOUS']) {
			  @fclose($fp);
			};
		};
		if ($this->info['http_code'] == 401) {
			if ($this->_detectAuth($this->options['CURLOPT_CUSTOMREQUEST'], $url)) {
			  return $this->request($url);
			};
		};
		if ($this->options['CURLOPT_HEADER'] &&
			substr($this->info['http_code'], 0, 1) == 2) {
			$headers = "";
			foreach ($this->response_headers as $k => $v) {
			  $headers .= $k . ': ' . $v . "\r\n";
			};
			$this->lastResult = ($this->lastResult) ? $headers . "\r\n" . $this->lastResult : $headers;
		};
		if (is_resource($this->options['CURLOPT_FILE'] && $this->lastResult)) {
			fwrite($this->options['CURLOPT_FILE'], strlen($this->lastResult));
		};
		$this->error = trim($errstr . ' ' . $php_errormsg);
		if (!$this->options['CURLOPT_RETURNTRANSFER']) {
			echo $this->lastResult;
			return true;
		};
		$this->info['request_header'] = $request_headers;
		return ($this->options['CURLOPT_ASYNCRONOUS']) ? $fp : $this->lastResult;
	}
	/**
	 * detect authentication
	 *
	 * @param string $method the http method
	 * @param string $url the requested url
	 * @return bool success/failure
	 */
  private function _detectAuth($method,$url) {
 		if (!@include_once('eac_httprequest.auth.php')) {
		  return false;
 		};
	 	if ($auth = httpRequest_auth::getAuthentication($method, $url, $this->options['CURLOPT_USERPW'], $this->response_headers)) {
	 		$this->header($auth, true, !$this->options['CURLOPT_UNRESTRICTED_AUTH']);
	 		return true;
	 	};
	 	return false;
  }
	/**
	 * get last result
	 *
	 * @param resource|null $fp connection resource returned when STREAMS_ASYNCRONOUS = true
	 * @return mixed result of last request
	 */
	public function getLastResult($fp = null) {
		if (is_resource($fp)) {
			$buf = '';
			while (!feof($fp)){
			  $buf .= fgets($fp, 128);
			};
			$this->lastResult = $this->_gzdecode($buf);
			@fclose($fp);
		} else if (is_string($fp)) {
			return $fp;
		};
		return $this->lastResult;
	}
}
?>
