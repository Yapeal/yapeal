<?php
/**
 * @category 	eac::Framework / HTTP Request
 * @package		eac_httprequest.cache.php
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
	Class to handle HTTP GET request caching.
	Only uses/supports If-Modified-Since in an assumed 'must-revalidate' mode (but it's a start).
 	---------------------------------------------------------------------------------------------------- */
class httpRequest_cache {
	public $Version = "v1.0.1, (Jun 17, 2009)";
	/**
	 * the cache directory
	 *
	 * @var string
	 */
	public $cacheDir = null;
	/**
	 * Constrtuctor method
	 *
	 * @param string $cacheDir the cache directory
	 */
	public function __construct($cacheDir) {
		$this->cacheDir = $cacheDir;
	}
	/**
	 * Do we have a cached version
	 *
	 * @param string $url the requested url
	 * @return int|bool the 'if-modified-since' header or false
	 */
	public function isCached($url) {
		$cacheFile = $this->_cacheName($url);
		if (file_exists($cacheFile)) {
		  return 'If-Modified-Since: ' . gmdate('D, d M Y H:i:s \G\M\T', filemtime($cacheFile));
		}	else {
		 	return false;
		};
	}
	/**
	 * Write a new cache version
	 *
	 * @access public
	 * @param string $url the requested url
	 * @param string $data the data
	 * @param array $response_headers assoc. array of response headers
	 * @return int|bool bytes written or false
	 */
  public function writeCache($url, $data, $response_headers) {
		$cacheFile = $this->_cacheName($url);
		if (isset($response_headers['Cache-Control'])) {
			if (stripos($response_headers['Cache-Control'], 'no-cache') !== false ||
				stripos($response_headers['Cache-Control'], 'max-age=0') !== false) {
				return false;
			};
		};
		if (isset($response_headers['Pragma'])) {
			if (stripos($response_headers['Pragma'], 'no-cache') !== false) {
				return false;
			};
		};
		return file_put_contents($cacheFile, gzcompress($data, 6));
	}
	/**
	 * Read a cache version
	 *
	 * @access public
	 * @param string $url the requested url
	 * @return string|bool data read or false
	 */
  public function readCache($url) {
		$cacheFile = $this->_cacheName($url);
		return (is_readable($cacheFile)) ? gzuncompress(file_get_contents($cacheFile)) : false;
	 }
	/**
	 * get the cache file name
	 *
	 * @access private
	 * @param string $url the requested url
	 * @return string the file name
	 */
	private function _cacheName($url) {
		return $this->cacheDir . "/@http_" . md5($url) . ".cache";
	}
}
?>
