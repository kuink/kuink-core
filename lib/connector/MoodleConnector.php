<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of RestConnector
 *
 * @author paulo.tavares
 */
class MoodleConnector extends \Kuink\Core\DataSourceConnector {
	private $response = null;
	private $responseType = "json";
	private $server = null;
	private $port = null;
	
	/**
	 * Connect using any strategy
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	function connect() {
		$this->server = $this->dataSource->getParam ( 'server', true );
		$this->port = $this->dataSource->getParam ( 'port', true );
		$server = $this->dataSource->getParam ( 'server', true );
		//var_dump ( $server );
		$responseType = $this->dataSource->getParam ( 'responseType', true );
		if ($responseType != '')
			$this->responseType = $responseType;
	}
	
	/**
	 * Wrapper to put
	 *
	 * @see \Kuink\Core\DataSourceConnector::insert()
	 */
	function insert($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to post
	 *
	 * @param unknown $params        	
	 */
	function update($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to post
	 *
	 * @param unknown $params        	
	 */
	function save($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to delete
	 *
	 * @param unknown $params        	
	 */
	function delete($params) {
		global $KUINK_TRACE;
		return;
	}
	
	/**
	 * Wrapper to any rest operation
	 */
	function execute($params) {
		global $KUINK_TRACE;
		
		$entity = ( string ) $this->getParam ( $params, '_entity', true );
		$multiple = ( string ) $this->getParam ( $params, '_multiple', false, '' );
		unset ( $params ['_entity'] );
		unset ( $params ['_pk'] );
		unset ( $params ['_multiple'] );
		
		// foreach($params as $key=>$value)
		// $requestParams[$key] = $value;
		
		$requestParams = $params;
		// print_object($requestParams);
		
		$server = ( string ) $this->dataSource->getParam ( 'server', true );
		$token = ( string ) $this->dataSource->getParam ( 'token', true );
		$restFormat = ( string ) $this->dataSource->getParam ( 'format', true );
		
		$serverUrl = $server . '?wstoken=' . $token . '&wsfunction=' . $entity;
		
		$curl = new \Kuink\Core\DataSourceConnector\Curl ();
		
		$restFormatUrl = ($restFormat == 'json') ? '&moodlewsrestformat=' . $restFormat : '';
		
		if ($multiple != '')
			$reqParams [$multiple] = $requestParams;
		else
			$reqParams = $requestParams;
		
		$NEON_TRACE [] = __CLASS__ . '::' . $entity . '::' . $serverUrl . $restFormatUrl;
		$resp = $curl->post ( $serverUrl . $restFormatUrl, $reqParams );
		// print_object($resp);
		// Check the response
		$decodedResponse = $resp;
		if ($restFormat == 'json')
			$decodedResponse = json_decode ( $resp, true );
		
		$reqParamsDebug = var_export ( $reqParams, true );
		$NEON_TRACE [] = __CLASS__ . '::' . $entity . '::' . $reqParamsDebug;
		if (isset ( $decodedResponse ['exception'] )) {
			$NEON_TRACE [] = 'EXCEPTION::' . __CLASS__ . '::' . $entity . '::' . $reqParamsDebug . '::' . $decodedResponse ['message'];
			throw new \Exception ( 'MoodleConnector::' . $entity . '::' . $reqParamsDebug . '::' . $decodedResponse ['message'] );
		}
		
		return $decodedResponse;
	}
	
	/**
	 * Wrapper to get
	 *
	 * @param unknown $params        	
	 * @return Ambigous <NULL, unknown>
	 */
	function load($params) {
		// kuink_mydebug(__CLASS__, __METHOD__);
		global $KUINK_TRACE;
	}

	public function getSchemaName($params) {
		return null;
	}
	
}

// Helper classes
class Curl {
	/** @var bool */
	public $cache = false;
	public $proxy = false;
	/** @var array */
	public $response = array ();
	public $header = array ();
	/** @var string */
	public $info;
	public $error;
	/** @var array */
	private $options;
	/** @var string */
	private $proxy_host = '';
	private $proxy_auth = '';
	private $proxy_type = '';
	/** @var bool */
	private $debug = false;
	private $cookie = false;
	private $count = 0;
	/**
	 *
	 * @param array $options        	
	 */
	public function __construct($options = array()) {
		if (! function_exists ( 'curl_init' )) {
			$this->error = 'cURL module must be enabled!';
			trigger_error ( $this->error, E_USER_ERROR );
			return false;
		}
		// the options of curl should be init here.
		$this->resetopt ();
		if (! empty ( $options ['debug'] )) {
			$this->debug = true;
		}
		if (! empty ( $options ['cookie'] )) {
			if ($options ['cookie'] === true) {
				$this->cookie = 'curl_cookie.txt';
			} else {
				$this->cookie = $options ['cookie'];
			}
		}
		if (! empty ( $options ['cache'] )) {
			if (class_exists ( 'curl_cache' )) {
				$this->cache = new curl_cache ();
			}
		}
	}
	/**
	 * Resets the CURL options that have already been set
	 */
	public function resetopt() {
		$this->options = array ();
		$this->options ['CURLOPT_USERAGENT'] = 'MoodleBot/1.0';
		// True to include the header in the output
		$this->options ['CURLOPT_HEADER'] = 0;
		// True to Exclude the body from the output
		$this->options ['CURLOPT_NOBODY'] = 0;
		// TRUE to follow any "Location: " header that the server
		// sends as part of the HTTP header (note this is recursive,
		// PHP will follow as many "Location: " headers that it is sent,
		// unless CURLOPT_MAXREDIRS is set).
		// $this->options['CURLOPT_FOLLOWLOCATION'] = 1;
		$this->options ['CURLOPT_MAXREDIRS'] = 10;
		$this->options ['CURLOPT_ENCODING'] = '';
		// TRUE to return the transfer as a string of the return
		// value of curl_exec() instead of outputting it out directly.
		$this->options ['CURLOPT_RETURNTRANSFER'] = 1;
		$this->options ['CURLOPT_BINARYTRANSFER'] = 0;
		$this->options ['CURLOPT_SSL_VERIFYPEER'] = 0;
		$this->options ['CURLOPT_SSL_VERIFYHOST'] = 2;
		$this->options ['CURLOPT_CONNECTTIMEOUT'] = 30;
	}
	/**
	 * Reset Cookie
	 */
	public function resetcookie() {
		if (! empty ( $this->cookie )) {
			if (is_file ( $this->cookie )) {
				$fp = fopen ( $this->cookie, 'w' );
				if (! empty ( $fp )) {
					fwrite ( $fp, '' );
					fclose ( $fp );
				}
			}
		}
	}
	/**
	 * Set curl options
	 *
	 * @param array $options
	 *        	If array is null, this function will
	 *        	reset the options to default value.
	 *        	
	 */
	public function setopt($options = array()) {
		if (is_array ( $options )) {
			foreach ( $options as $name => $val ) {
				if (stripos ( $name, 'CURLOPT_' ) === false) {
					$name = strtoupper ( 'CURLOPT_' . $name );
				}
				$this->options [$name] = $val;
			}
		}
	}
	/**
	 * Reset http method
	 */
	public function cleanopt() {
		unset ( $this->options ['CURLOPT_HTTPGET'] );
		unset ( $this->options ['CURLOPT_POST'] );
		unset ( $this->options ['CURLOPT_POSTFIELDS'] );
		unset ( $this->options ['CURLOPT_PUT'] );
		unset ( $this->options ['CURLOPT_INFILE'] );
		unset ( $this->options ['CURLOPT_INFILESIZE'] );
		unset ( $this->options ['CURLOPT_CUSTOMREQUEST'] );
	}
	/**
	 * Set HTTP Request Header
	 *
	 * @param array $headers        	
	 *
	 */
	public function setHeader($header) {
		if (is_array ( $header )) {
			foreach ( $header as $v ) {
				$this->setHeader ( $v );
			}
		} else {
			$this->header [] = $header;
		}
	}
	/**
	 * Set HTTP Response Header
	 */
	public function getResponse() {
		return $this->response;
	}
	/**
	 * private callback function
	 * Formatting HTTP Response Header
	 *
	 * @param mixed $ch
	 *        	Apparently not used
	 * @param string $header        	
	 * @return int The strlen of the header
	 */
	private function formatHeader($ch, $header) {
		$this->count ++;
		if (strlen ( $header ) > 2) {
			list ( $key, $value ) = explode ( " ", rtrim ( $header, "\r\n" ), 2 );
			$key = rtrim ( $key, ':' );
			if (! empty ( $this->response [$key] )) {
				if (is_array ( $this->response [$key] )) {
					$this->response [$key] [] = $value;
				} else {
					$tmp = $this->response [$key];
					$this->response [$key] = array ();
					$this->response [$key] [] = $tmp;
					$this->response [$key] [] = $value;
				}
			} else {
				$this->response [$key] = $value;
			}
		}
		return strlen ( $header );
	}
	/**
	 * Set options for individual curl instance
	 *
	 * @param object $curl
	 *        	A curl handle
	 * @param array $options        	
	 * @return object The curl handle
	 */
	private function apply_opt($curl, $options) {
		// Clean up
		$this->cleanopt ();
		// set cookie
		if (! empty ( $this->cookie ) || ! empty ( $options ['cookie'] )) {
			$this->setopt ( array (
					'cookiejar' => $this->cookie,
					'cookiefile' => $this->cookie 
			) );
		}
		// set proxy
		if (! empty ( $this->proxy ) || ! empty ( $options ['proxy'] )) {
			$this->setopt ( $this->proxy );
		}
		$this->setopt ( $options );
		// reset before set options
		curl_setopt ( $curl, CURLOPT_HEADERFUNCTION, array (
				&$this,
				'formatHeader' 
		) );
		// set headers
		if (empty ( $this->header )) {
			$this->setHeader ( array (
					'User-Agent: MoodleBot/1.0',
					'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
					'Connection: keep-alive' 
			) );
		}
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, $this->header );
		if ($this->debug) {
			echo '<h1>Options</h1>';
			var_dump ( $this->options );
			echo '<h1>Header</h1>';
			var_dump ( $this->header );
		}
		// set options
		foreach ( $this->options as $name => $val ) {
			if (is_string ( $name )) {
				$name = constant ( strtoupper ( $name ) );
			}
			curl_setopt ( $curl, $name, $val );
		}
		return $curl;
	}
	/**
	 * Download multiple files in parallel
	 *
	 * Calls {@link multi()} with specific download headers
	 *
	 * <code>
	 * $c = new curl;
	 * $c->download(array(
	 * array('url'=>'http://localhost/', 'file'=>fopen('a', 'wb')),
	 * array('url'=>'http://localhost/20/', 'file'=>fopen('b', 'wb'))
	 * ));
	 * </code>
	 *
	 * @param array $requests
	 *        	An array of files to request
	 * @param array $options
	 *        	An array of options to set
	 * @return array An array of results
	 */
	public function download($requests, $options = array()) {
		$options ['CURLOPT_BINARYTRANSFER'] = 1;
		$options ['RETURNTRANSFER'] = false;
		return $this->multi ( $requests, $options );
	}
	/*
	 * Mulit HTTP Requests
	 * This function could run multi-requests in parallel.
	 *
	 * @param array $requests An array of files to request
	 * @param array $options An array of options to set
	 * @return array An array of results
	 */
	protected function multi($requests, $options = array()) {
		$count = count ( $requests );
		$handles = array ();
		$results = array ();
		$main = curl_multi_init ();
		for($i = 0; $i < $count; $i ++) {
			$url = $requests [$i];
			foreach ( $url as $n => $v ) {
				$options [$n] = $url [$n];
			}
			$handles [$i] = curl_init ( $url ['url'] );
			$this->apply_opt ( $handles [$i], $options );
			curl_multi_add_handle ( $main, $handles [$i] );
		}
		$running = 0;
		do {
			curl_multi_exec ( $main, $running );
		} while ( $running > 0 );
		for($i = 0; $i < $count; $i ++) {
			if (! empty ( $options ['CURLOPT_RETURNTRANSFER'] )) {
				$results [] = true;
			} else {
				$results [] = curl_multi_getcontent ( $handles [$i] );
			}
			curl_multi_remove_handle ( $main, $handles [$i] );
		}
		curl_multi_close ( $main );
		return $results;
	}
	/**
	 * Single HTTP Request
	 *
	 * @param string $url
	 *        	The URL to request
	 * @param array $options        	
	 * @return bool
	 */
	protected function request($url, $options = array()) {
		// create curl instance
		$curl = curl_init ( $url );
		$options ['url'] = $url;
		$this->apply_opt ( $curl, $options );
		if ($this->cache && $ret = $this->cache->get ( $this->options )) {
			return $ret;
		} else {
			$ret = curl_exec ( $curl );
			if ($this->cache) {
				$this->cache->set ( $this->options, $ret );
			}
		}
		$this->info = curl_getinfo ( $curl );
		$this->error = curl_error ( $curl );
		if ($this->debug) {
			echo '<h1>Return Data</h1>';
			var_dump ( $ret );
			echo '<h1>Info</h1>';
			var_dump ( $this->info );
			echo '<h1>Error</h1>';
			var_dump ( $this->error );
		}
		curl_close ( $curl );
		if (empty ( $this->error )) {
			return $ret;
		} else {
			return $this->error;
			// exception is not ajax friendly
			// throw new moodle_exception($this->error, 'curl');
		}
	}
	/**
	 * HTTP HEAD method
	 *
	 * @see request()
	 *
	 * @param string $url        	
	 * @param array $options        	
	 * @return bool
	 */
	public function head($url, $options = array()) {
		$options ['CURLOPT_HTTPGET'] = 0;
		$options ['CURLOPT_HEADER'] = 1;
		$options ['CURLOPT_NOBODY'] = 1;
		return $this->request ( $url, $options );
	}
	
	/**
	 * Recursive function formating an array in POST parameter
	 * 
	 * @param array $arraydata
	 *        	- the array that we are going to format and add into &$data array
	 * @param string $currentdata
	 *        	- a row of the final postdata array at instant T
	 *        	when finish, it's assign to $data under this format: name[keyname][][]...[]='value'
	 * @param array $data
	 *        	- the final data array containing all POST parameters : 1 row = 1 parameter
	 */
	function format_array_postdata_for_curlcall($arraydata, $currentdata, &$data) {
		foreach ( $arraydata as $k => $v ) {
			$newcurrentdata = $currentdata;
			if (is_object ( $v )) {
				$v = ( array ) $v;
			}
			if (is_array ( $v )) { // the value is an array, call the function recursively
				$newcurrentdata = $newcurrentdata . '[' . urlencode ( $k ) . ']';
				$this->format_array_postdata_for_curlcall ( $v, $newcurrentdata, $data );
			} else { // add the POST parameter to the $data array
				$data [] = $newcurrentdata . '[' . urlencode ( $k ) . ']=' . urlencode ( $v );
			}
		}
	}
	/**
	 * Transform a PHP array into POST parameter
	 * (see the recursive function format_array_postdata_for_curlcall)
	 * 
	 * @param array $postdata        	
	 * @return array containing all POST parameters (1 row = 1 POST parameter)
	 */
	function format_postdata_for_curlcall($postdata) {
		if (is_object ( $postdata )) {
			$postdata = ( array ) $postdata;
		}
		$data = array ();
		foreach ( $postdata as $k => $v ) {
			if (is_object ( $v )) {
				$v = ( array ) $v;
			}
			if (is_array ( $v )) {
				$currentdata = urlencode ( $k );
				$this->format_array_postdata_for_curlcall ( $v, $currentdata, $data );
			} else {
				$data [] = urlencode ( $k ) . '=' . urlencode ( $v );
			}
		}
		$convertedpostdata = implode ( '&', $data );
		return $convertedpostdata;
	}
	/**
	 * HTTP POST method
	 *
	 * @param string $url        	
	 * @param array|string $params        	
	 * @param array $options        	
	 * @return bool
	 */
	public function post($url, $params = '', $options = array()) {
		$options ['CURLOPT_POST'] = 1;
		if (is_array ( $params )) {
			$params = $this->format_postdata_for_curlcall ( $params );
		}
		$options ['CURLOPT_POSTFIELDS'] = $params;
		return $this->request ( $url, $options );
	}
	/**
	 * HTTP GET method
	 *
	 * @param string $url        	
	 * @param array $params        	
	 * @param array $options        	
	 * @return bool
	 */
	public function get($url, $params = array(), $options = array()) {
		$options ['CURLOPT_HTTPGET'] = 1;
		if (! empty ( $params )) {
			$url .= (stripos ( $url, '?' ) !== false) ? '&' : '?';
			$url .= http_build_query ( $params, '', '&' );
		}
		return $this->request ( $url, $options );
	}
	/**
	 * HTTP PUT method
	 *
	 * @param string $url        	
	 * @param array $params        	
	 * @param array $options        	
	 * @return bool
	 */
	public function put($url, $params = array(), $options = array()) {
		$file = $params ['file'];
		if (! is_file ( $file )) {
			return null;
		}
		$fp = fopen ( $file, 'r' );
		$size = filesize ( $file );
		$options ['CURLOPT_PUT'] = 1;
		$options ['CURLOPT_INFILESIZE'] = $size;
		$options ['CURLOPT_INFILE'] = $fp;
		if (! isset ( $this->options ['CURLOPT_USERPWD'] )) {
			$this->setopt ( array (
					'CURLOPT_USERPWD' => 'anonymous: noreply@moodle.org' 
			) );
		}
		$ret = $this->request ( $url, $options );
		fclose ( $fp );
		return $ret;
	}
	/**
	 * HTTP DELETE method
	 *
	 * @param string $url        	
	 * @param array $params        	
	 * @param array $options        	
	 * @return bool
	 */
	public function delete($url, $param = array(), $options = array()) {
		$options ['CURLOPT_CUSTOMREQUEST'] = 'DELETE';
		if (! isset ( $options ['CURLOPT_USERPWD'] )) {
			$options ['CURLOPT_USERPWD'] = 'anonymous: noreply@moodle.org';
		}
		$ret = $this->request ( $url, $options );
		return $ret;
	}
	/**
	 * HTTP TRACE method
	 *
	 * @param string $url        	
	 * @param array $options        	
	 * @return bool
	 */
	public function trace($url, $options = array()) {
		$options ['CURLOPT_CUSTOMREQUEST'] = 'TRACE';
		$ret = $this->request ( $url, $options );
		return $ret;
	}
	/**
	 * HTTP OPTIONS method
	 *
	 * @param string $url        	
	 * @param array $options        	
	 * @return bool
	 */
	public function options($url, $options = array()) {
		$options ['CURLOPT_CUSTOMREQUEST'] = 'OPTIONS';
		$ret = $this->request ( $url, $options );
		return $ret;
	}
	public function get_info() {
		return $this->info;
	}
}
/**
 * This class is used by cURL class, use case:
 *
 * <code>
 *
 * $c = new curl(array('cache'=>true), 'module_cache'=>'repository');
 * $ret = $c->get('http://www.google.com');
 * </code>
 *
 * @package core
 * @subpackage file
 * @copyright 1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class curl_cache {
	/** @var string */
	public $dir = '';
	/**
	 *
	 * @param
	 *        	string @module which module is using curl_cache
	 *        	
	 */
	function __construct() {
		$this->dir = '/tmp/';
		if (! file_exists ( $this->dir )) {
			mkdir ( $this->dir, 0700, true );
		}
		$this->ttl = 1200;
	}
	/**
	 * Get cached value
	 *
	 * @param mixed $param        	
	 * @return bool|string
	 */
	public function get($param) {
		$this->cleanup ( $this->ttl );
		$filename = 'u_' . md5 ( serialize ( $param ) );
		if (file_exists ( $this->dir . $filename )) {
			$lasttime = filemtime ( $this->dir . $filename );
			if (time () - $lasttime > $this->ttl) {
				return false;
			} else {
				$fp = fopen ( $this->dir . $filename, 'r' );
				$size = filesize ( $this->dir . $filename );
				$content = fread ( $fp, $size );
				return unserialize ( $content );
			}
		}
		return false;
	}
	/**
	 * Set cache value
	 *
	 * @param mixed $param        	
	 * @param mixed $val        	
	 */
	public function set($param, $val) {
		$filename = 'u_' . md5 ( serialize ( $param ) );
		$fp = fopen ( $this->dir . $filename, 'w' );
		fwrite ( $fp, serialize ( $val ) );
		fclose ( $fp );
	}
	/**
	 * Remove cache files
	 *
	 * @param int $expire
	 *        	The number os seconds before expiry
	 */
	public function cleanup($expire) {
		if ($dir = opendir ( $this->dir )) {
			while ( false !== ($file = readdir ( $dir )) ) {
				if (! is_dir ( $file ) && $file != '.' && $file != '..') {
					$lasttime = @filemtime ( $this->dir . $file );
					if (time () - $lasttime > $expire) {
						@unlink ( $this->dir . $file );
					}
				}
			}
		}
	}
	/**
	 * delete current user's cache file
	 */
	public function refresh() {
		if ($dir = opendir ( $this->dir )) {
			while ( false !== ($file = readdir ( $dir )) ) {
				if (! is_dir ( $file ) && $file != '.' && $file != '..') {
					if (strpos ( $file, 'u_' ) !== false) {
						@unlink ( $this->dir . $file );
					}
				}
			}
		}
	}

}

?>
