/**
 * @category 	eac::Framework / HTTP Request
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


/*	----------------------------------------------------------------------------------------------------
	eac_httprequest is a set of classes that facilitate server-to-server comunications using
	various http request methods (GET, POST, PUT, DELETE, etc.). Requests are executed using CURL
	(via eac_httprequest.curl.php), STREAMS (via eac_httprequest.stream.php), or SOCKETS (via
	eac_httprequest.socket.php) as needed or as allowed in the current environment. Support for basic
	and digest authentication for streams and sockets is provided by the eac_httprequest.auth.php class
	and caching for http GET requests is provided by the eac_httprequest.cache.php class.

	eac_httprequest.class.php is a wrapper class used to load one of the curl, stream or socket classes.

	A number of examples are provided in eac_httprequest.test.php.
	----------------------------------------------------------------------------------------------------	*/

 /**
  * A little bit of history...
  * I originally wrote a wrapper class (eac_curl.class.php) to simplify GET and POST cURL functions.
  * Almost immediately, I had a situation where the curl library wasn't available, so I copied the class
  * and replicated some of the functionality using streams (eac_streams.class.php).
  *
  * This implementation is intended to combine some of the redundancy of the previous two classes by
  * using extended classes for streams and a new sockets class.
  * The basis is still that of curl and the parent class (eac_httprequest.curl.php) and the option names
  * are built around curl. Extended classes should interpret/emulate the curl functionality as best as possible.
  *
  * Changes from previous versions:
  * -	When options are passed to the http functions (get(), post(), etc.) the original options are
  * 	restored before the next request.
  * -	Internally set request headers are reset after the request. User set headers are retained.
  * -	The header() method has an optional replace argument [header($header,$replace)].
  * - 	To remove a header, add it without a value and with the $replace argument: header('User-Agent',true).
  * -	The default user-agent is set to a unique value (no longer uses $_SERVER['HTTP_USER_AGENT']).
  * -	Username and password are not automatically set to a default value but the stream and socket classes will
  * 	set a value if an authorization is requested and no value has been given.
  * -	New getRequest() method to retrieve the headers sent with the request.
  * -	New connect() method for http CONNECT used for ssl proxy tunneling.
  * -	info['request_header'] will be populated with the request headers.
  * -	The stream class includes http_context in the info array (excluding content and headers).
  * -	With a post request, the content-type header is set to "application/x-www-form-urlencoded" only if the form
  * 	fields are passed as an array. The array is encoded using  http_build_query(). If any other content is
  * 	posted (i.e. xml), the content-type header should be set with $http->header('Content-Type: text/xml').
  * 	When using the curl class, you can pass the fields array as multi-part by setting the Content-Type to
  * 	multipart/form-data with $http->header('Content-Type: multipart/form-data')
  * -	URLs are parsed for username, password, and/or port. The username/password is removed from the url.
  * -	COOKIEJAR & COOKIEFILE are no longer set to a default value. They are not supported in streams or sockets.
  * -	The callback function is passed 2 parameters $body and $headers. Previously it was $body and the curl handle.
  * -	Internally, all options are prefixed with CURLOPT_, externally HTTP_.
  * -	The authentication header is only passed on subsequent calls if HTTP_UNRESTRICTED_AUTH is set.
  * -	Authentication for streams & sockets is handled in a static class loaded when it is needed.
  * -	Cache support for GET requests via eac_httprequest.cache.php. Set HTTP_CACHE to the full directory path of the
  * 	cache folder. The folder will be created and chmod'ed if possible, otherwise create and chmod before hand.
  *
  * This class (eac_httprequest.class.php) is a simple wrapper to load the proper child class depending
  * on the value of the HTTP_PLUGIN option.
  * -	"AUTO" (the default) will use the curl class if the curl library is installed, the stream class
  * 	if safe mode is off and the stream functions available, otherwise the socket class.
  * -	"CURL" will use the curl class.
  * -	"STREAM" will use the stream class.
  * -	"SOCKET" will use the socket class.
  * -	Multiple options may be used to specify a priority preference ('AUTO' == 'CURL;STREAM;SOCKET')
  * 	i.e. 'STREAMS;CURL' will attempt to use streams, if not, then curl.
  */


/*	----------------------------------------------------------------------------------------------------

	Instantiation
	----------------------------------------------------------------------------------------------------

		require_once('eac_httprequest.class.php');
		$http = new httpRequest( [ $options ] );

		As this class is only a wrapper used for selecting and loading one of the other classes, it is
		not necessary to use this class if only one child class is to be available.

		If cURL is your only option:
			require_once('eac_httprequest.curl.php');
			$http = new curlRequest( [ $options ] );
		If streams is your only option:
			require_once('eac_httprequest.stream.php');
			$http = new streamRequest( [ $options ] );
		If sockets is your only option:
			require_once('eac_httprequest.socket.php');
			$http = new socketRequest( [ $options ] );


	Options
	----------------------------------------------------------------------------------------------------

		The $options array passed in the instantiation is an associative array with any curl_setopt
		option name as the key associated with the appropriate value.
			See: http://us2.php.net/manual/en/function.curl-setopt.php

		Option names may be prefixed with HTTP_, STREAMS_, SOCKET_, or CURLOPT_. They will be translated
		and referenced internally as CURLOPT_.

		To prevent the assumption that cURL is always used, HTTP_ is the preferred prefix and the
		getOptions() method returns the array with HTTP_* option names.

		* Internally, all options have a prefix of CURLOPT_. Externally, they should have a prefix of HTTP_.

		Three additional values may also be set:
			HTTP_PLUGIN can be set to one of either 'AUTO', 'CURL', 'STREAM', 'SOCKET'. Auto will use
				cURL if the cURL module is available, streams if safe mode is off, otherwise sockets.
			HTTP_ASYNCRONOUS when set to 1 or true will attempt to emulate an asynchronous request
				by setting the time-out to 1 second and not retrieve the resulting body.
			HTTP_CACHE can be set to the cache directory to enable GET request caching. Only the
				'If-Modified-Since' method is used in a 'must-revalidate' mode.

		$options can also be a string containing one of either 'AUTO', 'CURL', 'STREAM', or 'SOCKET'.
		Use when you only need to set the method/class to use (either the default options are okay or
		you will set additional options with setOptions() or setOption()).

		Default options
			HTTP_PLUGIN 			= "AUTO";						// AUTO, CURL, STREAM, or SOCKET
			HTTP_HEADER				= 0;							// no headers in result
			HTTP_NOBODY 			= 0;							// include body in response
			HTTP_USERAGENT			= (see below);					// default user agent
			HTTP_FOLLOWLOCATION		= 1;							// allow redirection
			HTTP_MAXREDIRS			= 10;							// max redirects
			HTTP_TIMEOUT			= 120;							// max time in seconds
			HTTP_ENCODING			= "gzip";						// allow gzip compression
			HTTP_RETURNTRANSFER		= 1;							// return results as string
			HTTP_BINARYTRANSFER		= 0;							// no binary transfer
			HTTP_SSL_VERIFYPEER		= 0;							// don't verify ssl certs
			HTTP_REFERER			= (($_SERVER['HTTPS']=='on') ? 'https:' : 'http:').'//'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			HTTP_UNRESTRICTED_AUTH	= 0;							// do not pass authentication to multiple locations

			Additionally, other options specific to the request (i.e. get, post, etc.) are set as needed.
			All requests use HTTP_CUSTOMREQUEST to designate the request type.

		HTTP_PLUGIN may be set to any combination of 'CURL', 'STREAM', or 'SOCKET' with each delimited by
		a semicolon. 'AUTO' (or any other value) is equivelent to 'CURL;STREAM;SOCKET'. Using more than one
		option is setting a priority preference for which plugin to use.

		The default user agent is set to something like this:
			Mozilla/5.0 (compatible; curlRequest/1.0.2 +http://www.kevinburkholder.com/)
		If you want to change this, you must specify a new value using the HTTP_USERAGENT option.

		HTTP_ASYNCRONOUS is intended to allow for the case in which the result of the request is irrelivant
		and we need the request to go through as quickly as possible. For example, if there is no recourse in the
		event of a POST or PUT failure, there's no point in waiting for a slow connection to give us a timeout.

		In the stream & socket classes, this option will set the time out to 1 second and set the HTTP_NOBODY
		option to true. It will not read the return result from the stream but it will return the opened connection
		so that the result may be retrieved later in your script using getLastResult($connection).

		In the curl class, this option will set the time out to 1 second and set the HTTP_NOBODY option to true.
		There is no way to then retrieve the result.

		The HTTP_ASYNCRONOUS option is not sticky and must be set for each request.



	Methods
	----------------------------------------------------------------------------------------------------

		setOptions ( $options_array )
			Another way of passing the $options array - instead of or in addition to the instantiation.

		setOption ( $option_name , $option_value )
			Set a single option by passing the curl_setopt option name and the associated value.

		setCallback ( $function )
			Sets a callback function that is called when the request successfully returns a body. The
			callback function should be defined as:
				function mycallback ( $body, $headers )

		copyHeaders ( [ $header ] )
			Copies the current request headers (any $_SERVER['HTTP_*']) to be used for the http request.
			If $header is set, only that header is copied.
			'cookie','accept-encoding','user-agent','host','connection', 'referer', and 'cache-control' are
			not copied. However, referer will be used to set HTTP_REFERER.

		copyCookies ( [ $cookie ] )
			Copies the current request cookies (any $_COOKIE) to be used for the http request.
			If $cookie is set, only the cookie by that name is copied.

		header ( $header [, $replace ] )
			Adds a new http header to the request. Most often used to add a content-type header:
			$http->header('Content-Type: text/xml').
			If $replace is true any previous header by the same name is replaced.

		get ( $url [, $options ] )
			Make an http GET request.
			$url is the requested url. $options can be additional CURLOPT options.

		post ( $url [, $fields [,$options ] ] )
			Make an http POST request.
			$url is the requested url. $fields can be an associative array of field_name => field_value or
			any body that should be sent with the post request. $options can be additional CURLOPT options.
			If $fields is an array, the array is transformed into a urlencoded string and the content-type
			header is set to "Content-Type: application/x-www-form-urlencoded". If $fields is not an array,
			you probably need to set the content-type header.

		head ( $url [, $options ] )
			Make an http HEAD request.
			$url is the requested url. $options can be additional CURLOPT options.

		put ( $url, $fn_or_data [, $options ] )
			Make an http PUT request.
			$url is the requested url. $fn_or_data can be a file name or a string. In order to use a file,
			$fn_or_data must be prefixed with "@" and contain a valid file name found on the server making
			the request (i.e. @/usr/www/docroot/my_file.dat). When the put method looks for the file, it
			will use the PHP include path as well as the $_SERVER['DOCUMENT_ROOT'] path.
			$options can be additional CURLOPT options.

		delete ( $url [, $options ] )
			Make an http DELETE request.
			$url is the requested url. $options can be additional CURLOPT options.

		option ( $url [, $options ] )
			Make an http OPTION request.
			$url is the requested url (most often the domain followed by "/*").
			$options can be additional CURLOPT options.

		trace ( $url [, $options ] )
			Make an http TRACE request.
			$url is the requested url. $options can be additional CURLOPT options.

		connect ( $url [, $options ] )
			Make an http CONNECT request.
			$url is the requested url. $options can be additional CURLOPT options.

		sendLastResult ($to [, $from [, $subject [, $xheaders [, $EOL ] ] ] ])
			Sends the body of the last request in an email.
			$to is the recipient's email address. $from is the sender's address (default is either
			$_SERVER['SERVER_ADMIN'] or 'webmaster@'.$_SERVER['HTTP_HOST']). $subject is the email subject
			line. $xheaders is an array of additional headers to add to the email. $EOL is the end-of-line
			character to use (\r\n or \n - \n is the default).

		getHttpStatus ()
			Returns the http status code from the last request (i.e. 200).

		getLastResult ( [ $connection ] )
			Returns the body of the last request.
			$connection may be a connection resource returned by the stream or socket class when
			option HTTP_ASYNCRONOUS is true.

		getInfo ( [ $field ] )
			Returns the info array or a single value from the array (if $field is set).
			The info array is an associative array of information returned by the request. The array
			content will vary depending on the child class used, the type of request, and the result.
			The array will usually have, at minimum:
				[url] 						=> the resulting url (after any redirection)
				[http_code] 				=> The http status number (i.e. 200)
				[redirect_count] 			=> The number of redirections that occurred
				[download_content_length] 	=> From the Content-Length header
				[content_type] 				=> From the Content-Type header (i.e. text/html)
				[request_header]			=> The request headers as a string (delimited by "\r\n")
				[decoded_content_length] 	=> Content length If/when gzip decoding
			The streams class also includes the request context parameters similar to:
				[http_context] => Array (
					[method] => POST
					[user_agent] => Mozilla/5.0 (compatible; streamRequest/1.0.7 +http://www.kevinburkholder.com/)
					[max_redirects] => 10
					[timeout] => 120
					[verify_peer] => 0
					[allow_self_signed] => 1	)
			The curl class will return everything set by curl_getinfo().

		getInfoField ( $field )
			Returns a single value from the info array (same as getInfo($field)).

		getHeaders ( [ $header ] )
			Returns an associative array of response headers or a single header from the array (if $header is set).

		getRawHeaders ()
			Returns an indexed array of all response headers. This array will include headers from redirections
			as well as multiple headers of the same type.

		getRequest ( [ $header ] )
			Returns an indexed array of request headers or a single header from the array (if $header is set).

		getOptions ( [ $option ] )
			Returns the options array or a single option from the array (if $option is set).
			Option names/keys will use the HTTP_ prefix.


	Properties
	----------------------------------------------------------------------------------------------------

		Type - Set to one of 'CURL', 'STREAM', or 'SOCKET' by the child class being used.

		success - Set to true on successful request, false on failure.

		error - Set to the last error message returned by the request.


	CURLOPT options supported by stream & socket class
	----------------------------------------------------------------------------------------------------
		HTTP_HEADER
		HTTP_NOBODY
		HTTP_USERAGENT
		HTTP_FOLLOWLOCATION
		HTTP_MAXREDIRS
		HTTP_TIMEOUT
		HTTP_ENCODING			(gzip only)
		HTTP_RETURNTRANSFER
		HTTP_SSL_VERIFYPEER		(stream only)
		HTTP_REFERER
		HTTP_USERPWD			(BASIC or DIGEST authentication)
		HTTP_FILE
		HTTP_PORT
		HTTP_ASYNCRONOUS		(not a true CURLOPT option)
		HTTP_UNRESTRICTED_AUTH
 	---------------------------------------------------------------------------------------------------- */


/*	----------------------------------------------------------------------------------------------------
				See eac_httprequest.test.php for further documentation and examples
 	---------------------------------------------------------------------------------------------------- */
