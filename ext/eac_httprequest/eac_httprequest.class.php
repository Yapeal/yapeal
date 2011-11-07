<?php
/**
 * @category 	eac::Framework / HTTP Request
 * @package		eac_httprequest.class.php
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
class httpRequest {
	public $Version = "v1.0.9, (Jun 17, 2009)";
	public $Signature = "eac_httprequest.class.php; %s; [www.KevinBurkholder.com]";
	public $Type = "CURL";
	/**
	 * a requester class
	 *
	 * @var object
	 */
	 private $plugin = null;
	/**
	 * class options
	 *
	 * @var array
	 */
	private $options = array();
	/**
	 * Constrtuctor method
	 *
	 * @param array $options curl_setopt options
	 */
	public function __construct($options = null) {
		$this->Signature = sprintf($this->Signature, $this->Version);
		$this->options['CURLOPT_PLUGIN'] = 'AUTO';	// AUTO, CURL, STREAM, or SOCKET
		if (is_string($options)) {
			$options = array('CURLOPT_PLUGIN', strtoupper($options));
		};
		$this->setOptions($options);
		$this->setPlugin();
	}
	/**
	 * set options
	 * Any curl_setopt optian can be used and will be passed to the plug-in.
	 * Plug-ins should ignore unsupported options.
	 *
	 * @param array $options curl_setopt options
	 * @return void
	 */
	public function setOptions($options = null) {
		if ($this->plugin) {
			$this->plugin->setOptions($options);
		} else if (is_array($options)) {
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
	 * @return void
	 */
	public function setOption($option, $value) {
		$option = str_replace(array('HTTP_', 'STREAMS_', 'SOCKET_'), 'CURLOPT_', $option);
		if ($this->plugin) {
			$this->plugin->setOption($option, $value);
		} else {
			$this->options[$option] = $value;
		};
	}
	/**
	 * set the interface plugin class
	 *
	 * @return void
	 */
	private function setPlugin() {
		if (!in_array(strtoupper($this->options['CURLOPT_PLUGIN']), array('CURL', 'STREAM', 'SOCKET'))) {
			$this->options['CURLOPT_PLUGIN'] = 'CURL;STREAM;SOCKET';
		};
		$plugins = explode(';', strtoupper($this->options['CURLOPT_PLUGIN']));
		$this->options['CURLOPT_PLUGIN'] = trim($plugins[0]);
		foreach ($plugins as $plugin) {
			switch (trim($plugin)) {
				case 'CURL':
					if (function_exists('curl_setopt')) {
						$this->options['CURLOPT_PLUGIN'] = 'CURL';
						break 2;
					};
					break;
				case 'STREAM':
					if (!@ini_get('safe_mode') && function_exists('stream_get_contents') &&
						function_exists('stream_get_wrappers') &&
						in_array('http', stream_get_wrappers())) {
						$this->options['CURLOPT_PLUGIN'] = 'STREAM';
						break 2;
					};
					break;
				case 'SOCKET':
					if (function_exists('fsockopen')) {
						$this->options['CURLOPT_PLUGIN'] = 'SOCKET';
						break 2;
					};
					break;
			};
		};
		$plugin	= strtolower($this->options['CURLOPT_PLUGIN']);
		$plugin_file = 'eac_httprequest.' . $plugin . '.php';
		$plugin_class	= $plugin . 'Request';
		$this->Type	= strtoupper($plugin);
		require_once $plugin_file;
		if (!class_exists($plugin_class)) {
			$mess = 'Plugin class ' . $plugin_class . ' does not exist';
			Logger::getLogger('yapeal')->error($mess);
            exit(2);
		};
		$this->plugin = new $plugin_class($this->options);
	}
	/**
	 * Pass method calls on to plugin
	 *
	 * @param string $method the method name
	 * @param array $args the method arguments
	 * @return mixed
	 */
	public function __call($method, $args) {
		return call_user_func_array(array($this->plugin, $method), $args);
	}
	/**
	 * get a property from the plugin
	 *
	 * @param string $varName the property name
	 * @return mixed
	 */
	public function __get($varName) {
		return $this->plugin->$varName;
	}
}
?>
