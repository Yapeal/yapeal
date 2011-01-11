<?php
/**
 * @category 	eac::Framework / HTTP Request
 * @package		eac_httprequest.auth.php
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
/*	----------------------------------------------------------------------------------------------------
	Static class to handle authentication response for stream & socket requests
 	---------------------------------------------------------------------------------------------------- */
class httpRequest_auth {
	public static $Version = "v1.0.3, (Jun 17, 2009)";
	/**
	 * retry count
	 *
	 * @var array
	 */
	public static $retryCount = 0;
	/**
	 * retry limit
	 *
	 * @var array
	 */
	public static $retryLimit = 5;
	/**
	 * detect authentication
	 *
	 * @param string $method the http method
	 * @param string $url the requested url
	 * @param string $userpw the curlopt userpw string (user:password)
	 * @param array $response_headers assoc. array of response headers
	 * @return string|bool the authentication header or false
	 */
	public static function getAuthentication(
		$method,
		$url,
		$userpw = null,
		$response_headers = null) {
		if (!$userpw) {
			if (isset($_SERVER['SERVER_ADMIN'])) {
				$userpw = 'anonymous:' . $_SERVER['SERVER_ADMIN'];
			}	else if (isset($_SERVER['HTTP_HOST'])) {
				$userpw = 'anonymous:webmaster@' . $_SERVER['HTTP_HOST'];
			}	else {
				return false;
			};
		};
	 	if (!isset($response_headers['WWW-Authenticate'])) {
		  return false;
	 	};
	 	if (self::$retryCount > self::$retryLimit) {
		  return false;
	 	};
	 	self::$retryCount += 1;
	 	$authHeader = explode(' ', $response_headers['WWW-Authenticate'], 2);
		switch (strtolower($authHeader[0])) {
	 		case 'basic':
				return 'Authorization: Basic ' . base64_encode($userpw);
				break;
	 		case 'digest':
			 	$authHeader[1] = explode(',', str_replace(' ', '', $authHeader[1]));
			 	foreach ($authHeader[1] as $value) {
			 		$val = explode('=', $value);
			 		$authDigest[strtolower($val[0])] = $val[1];
			 	};
			 	$userpw = explode(':', $userpw);
			 	$url = parse_url($url, PHP_URL_PATH);
				$A1 = md5($userpw[0] . ':' . $authDigest['realm'] . ':' . $userpw[1]);
				$A2 = md5($method . ':' . $url);
				$response = (isset($authDigest['qop']))
					? md5($A1 . ':' . $authDigest['nonce'] . ':' . $authDigest['nc'] . ':' . $authDigest['cnonce'] . ':' . $authDigest['qop'] . ':' . $A2)
					: md5($A1 . ':' . $authDigest['nonce'] . ':' . $A2);
				$digest = (isset($authDigest['qop']))
					? "username={$userpw[0]},nonce={$authDigest['nonce']},uri={$url},nc={$authDigest['nc']},cnonce={$authDigest['cnonce']},qop={$authDigest['qop']}"
					: "username={$userpw[0]},uri={$url}";
				$digest .= ",response={$response}";
				return "Authorization: Digest " . $digest;
				break;
	 	};
	 	return false;
	}
}
?>
