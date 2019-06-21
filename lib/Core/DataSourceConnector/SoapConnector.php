<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kuink\Core\DataSourceConnector;

/**
 * Soap Client using Microsoft's NTLM Authentication.
 */
class NTLMSoapClient extends \SoapClient
{
    /**
     * cURL resource used to make the SOAP request
     *
     * @var resource
     */
    protected $ch;
    protected $user; //The connection user
		protected $passwd; //The connection password
    /**
     * WSDL url
     * @var string
     */
    protected $__wsdl_url;
    
    /**
     * WSDL dom
     * @var DOMDocument
     */
    protected $__wsdl_dom;
    
    /**
     * WSDL parsed
     * @var array 
     */
    public $__wsdl_parsed = array();
    
    /**
     * Client configuration
     * @var array 
     */
    protected $__options=array();		
    
    /**
     * Whether or not to validate ssl certificates
     *
     * @var boolean
     */
    protected $validate = false;

    function __construct($wsdl,$options=array()){
			$this->__wsdl_url = $wsdl;
			$this->__options = $options;
			parent::__construct($wsdl, $options);
	}

    /**
     * Performs a SOAP request
     *
     * @link http://php.net/manual/en/function.soap-soapclient-dorequest.php
     *
     * @param string $request the xml soap request
     * @param string $location the url to request
     * @param string $action the soap action.
     * @param integer $version the soap version
     * @param integer $one_way
     * @return string the xml soap response.
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
    	global $KUINK_TRACE;
    	$headers = array(
    			'Method: POST',
    			//'Connection: Keep-Alive',
    			'User-Agent: PHP-SOAP-CURL',
    			'Content-Type: text/xml; charset=utf-8',
    			'SOAPAction: "'.$action.'"',
    	);
    
    	$this->__last_request_headers = $headers;
    	$this->ch = curl_init($location);
    
    	curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
    	curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
    	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($this->ch, CURLOPT_POST, true );
    	curl_setopt($this->ch, CURLOPT_POSTFIELDS, trim($request));
    	curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    	curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
    	curl_setopt($this->ch, CURLOPT_USERPWD, $this->user.':'.$this->passwd);
    	curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
    	
    	$KUINK_TRACE[] = 'Request: ' . '<pre>'.htmlspecialchars($request).'</pre>';
    
    	$response = curl_exec($this->ch);
    	$headerData = curl_getinfo($this->ch);

    	$KUINK_TRACE[] = 'Response: ' . '<pre>'.htmlspecialchars($response).'</pre>';
    	$KUINK_TRACE[] = 'HttpCode: ' . $headerData['http_code'];    	
    
    	if ($response === false) {
    		$KUINK_TRACE[] = 'Exception calling webservice';
    		$KUINK_TRACE[] = 'Request: ' . $request;
    		$KUINK_TRACE[] = 'Response: ' . $response;
    		$KUINK_TRACE[] = 'Error: ' . curl_error($this->ch) . ':' . curl_errno($this->ch);
    		throw new \Exception(
    				'Curl error: ' . curl_error($this->ch),
    				curl_errno($this->ch)
    				);
    	}
    
    	curl_close($this->ch);
    	return  $response;
    }
    
    public function setCredentials($user, $passwd) {
    	$this->user = $user;
    	$this->passwd = $passwd;
    	return;
    }

    /**
     * Performs a SOAP request legacy used in old calls
     *
     * @link http://php.net/manual/en/function.soap-soapclient-dorequest.php
     *
     * @param string $request the xml soap request
     * @param string $location the url to request
     * @param string $action the soap action.
     * @param integer $version the soap version
     * @param integer $one_way
     * @return string the xml soap response.
     */
    public function __doRequestLegacy($request, $location, $action, $version, $one_way = 0, $user, $passwd)
    {
    	global $KUINK_TRACE;
    	$headers = array(
    			'Method: POST',
    			//'Connection: Keep-Alive',
    			'User-Agent: PHP-SOAP-CURL',
    			'Content-Type: text/xml; charset=utf-8',
    			'SOAPAction: "'.$action.'"',
    	);
    
    	$this->__last_request_headers = $headers;
    	$this->ch = curl_init($location);
    
    	curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
    	curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
    	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
    	curl_setopt($this->ch, CURLOPT_POST, true );
    	curl_setopt($this->ch, CURLOPT_POSTFIELDS, trim($request));
    	curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    	curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
    	curl_setopt($this->ch, CURLOPT_USERPWD, $user.':'.$passwd);
    
    	$response = curl_exec($this->ch);
    
    	// TODO: Add some real error handling.
    	// If the response if false than there was an error and we should throw
    	// an exception.
    	if ($response === false) {
    		throw new \Exception(
    				'Curl error: ' . curl_error($this->ch),
    				curl_errno($this->ch)
    				);
    	}
    	curl_close($this->ch);

    	$KUINK_TRACE[]='<pre>'.htmlspecialchars($request).'</pre>';
    	$KUINK_TRACE[]='<pre>'.$response.'</pre>';
    	 
    	$resultData=array();
    	if ($response != '' && strpos($response,'soap:Fault')===false) {
    		//Parse the response
    		$xml = simplexml_load_string(htmlspecialchars_decode($response));
    		$ns = $xml->getNamespaces(true);
    		 
    		$soap = $xml->children($ns['soap']);
    		$result1=($soap->Body->children()[0]);
    		$result2=($result1->children()[0]);
    		$rows=$result2->children();
    		$resultData=array();
    		foreach($rows as $row) {
    			$attrs = array();
    			foreach($row->attributes() as $key=>$value)
    				$attrs[(string)$key]=(string)$value;
    				$resultData[]=$attrs;
    		}
    	} else {
    		$KUINK_TRACE[] = $response;
    	}
    
    	return $resultData;
    }
    
    /**
     * Returns last SOAP request headers
     *
     * @link http://php.net/manual/en/function.soap-soapclient-getlastrequestheaders.php
     *
     * @return string the last soap request headers
     */
    public function __getLastRequestHeaders()
    {
        return implode('n', $this->__last_request_headers) . "\n";
    }

    /**
     * Sets whether or not to validate ssl certificates
     *
     * @param boolean $validate
     */
    public function validateCertificate($validate = true)
    {
        $this->validate = $validate;

        return true;
		}
		

	/* From bubble soap */

	/**
	 * The array of SOAP function prototypes, detailing only the function name 
	 * @return array Ordered array of functions names
	 */
	public function __getFunctionsNames(){
			$this->__parseWSDL('operations');
			return array_keys($this->__wsdl_parsed['operations']);
	}

	/**
	 * The array of SOAP function prototypes, detailing only the function name 
	 * @return array Ordered array of functions names
	 */
	public function __getFunctionsDocumentation(){
		$this->__parseWSDL('operations');
		//print_object($this->__wsdl_parsed['operations']);
		return $this->__wsdl_parsed['operations'];
	}


	/**
	 * Gets the parameters of the specified function
	 * @param string $method Function name
	 * @return array Array of function parameters
	 */
	public function __getParams($method){
			$this->__parseWSDL('operations');
			$params = $this->__wsdl_parsed['operations'][$method]['in'];
			if(count($params)==1 && $this->__getType(current($params))==''){
					$params = array();
			}
			return $params;
	}
	
	/**
	 * Gets the return type of the specified function
	 * @param string $method Function name
	 * @return array Array of function parameters
	 */
	public function __getReturn($method){
			$this->__parseWSDL('operations');
			return $this->__wsdl_parsed['operations'][$method]['out'];
	}
	
	/**
	 * Gets the format of the data type specified
	 * @param string $name Name of data type
	 * @return mixed array (struct), empty string (null value), int, date
	 */
	public function __getType($name){
			$this->__parseWSDL('types');
			return $this->__parseType($name);
	}
	
	/**
	 * Gets the format of the data type specified in txt
	 * @param string $name Name of data type
	 * @return mixed array (struct), empty string (null value), int, date
	 */
	public function __getTypeTxt($name){
		$this->__parseWSDL('types');
		return $this->__parseTypeTxt($name, 0);
	}

	/**
	 * Returns WSDL address
	 * @return string
	 */
	public function __getWsdlUrl(){
			return $this->__wsdl_url;
	}
	
	// -----------------
	// PROTECTED METHODS
	// -----------------
	
	protected function __parseType($name){
			if(!$name) return;
			if(!isset($this->__wsdl_parsed['types'][$name])) return $name;
			$type = $this->__wsdl_parsed['types'][$name];
			if(is_object($type)){
					$list = new \stdClass();
					foreach((array)$type as $k=>$v){
							$list->$k = $this->__parseType($v);
					}
					return $list;
			}
			elseif(is_array($type)){
					$list = array();
					foreach($type as $k=>$v){
							$list[$k] = $this->__parseType($v);
					}
					return $list;
			}
			else return $type;
	}
	
	protected function __parseTypeTxt($name, $level=0){
		if(!$name) return;
		if(!isset($this->__wsdl_parsed['types'][$name])) return $name;
		$levelSpaces = '';
		for ($i=1; $i<=$level; $i++)
			$levelSpaces .= '&nbsp;&nbsp;';
		$type = $this->__wsdl_parsed['types'][$name];
		if(is_object($type)){
				$list = $levelSpaces.'struct {<br/>'; //new \stdClass();
				foreach((array)$type as $k=>$v){
						$list .= $levelSpaces.$k.' '.$this->__parseTypeTxt($v, $level+1).';<br/>';
						//$list->$k = $this->__parseType($v);
				}
				return $list.$levelSpaces.'}';
		}
		elseif(is_array($type)){
				$list = $levelSpaces.'array [<br/>'; //array();
				foreach($type as $k=>$v){
					if ($k !== 0)
						$list .= $levelSpaces.$k.' '.$this->__parseTypeTxt($v, $level+1).';<br/>';					
					else
						$list .= $levelSpaces.$this->__parseTypeTxt($v, $level+1).';<br/>';
					//$list[$k] = $this->__parseType($v);
				}
				return $list.$levelSpaces.']';
		}
		else return $type;
}

	protected function __loadWSDL(){
			if(!isset($this->__wsdl_dom)){
					if(!ini_get('allow_url_fopen')) throw new Exception('BubbleSOAP: WSDL document not loaded because "allow_url_fopen" is set to off!');
					$this->__wsdl_dom = new \DOMDocument;
					$this->__wsdl_dom->preserveWhiteSpace = false;
					$this->__wsdl_dom->load($this->__wsdl_url);
					if(!$this->__wsdl_dom->xmlVersion) throw new Exception('BubbleSOAP: WSDL document not loaded, unknown problem!');
			}
	}
	
	protected function __parseWSDL($type){
			if(isset($this->__wsdl_parsed[$type])) return;
			switch($type){
					case 'operations':
							$this->__loadWSDL();
							$operations = $this->__getFunctions();
							//print_object($operations);
							$list = array();
							foreach($operations as $op){
									$matches = array();
									if(preg_match('/^(\w[\w\d_]*) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/',$op,$matches)){
											$return = $matches[1];
											$name = $matches[2];
											$params = $matches[3];
									} 
									elseif(preg_match('/^(list\([\w\$\d,_ ]*\)) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/',$op,$matches)) {
											$return = $matches[1];
											$name = $matches[2];
											$params = $matches[3];
									}
									$paramList = array();
									$params = explode(',',$params);
									foreach($params as $param){
											list($paramType,$paramName) = explode(' ',trim($param));
											$paramName = trim($paramName,'$)');
											$paramList[$paramName] = $paramType;
									}
									$documentation = $this->__checkForDocumentation($this->__wsdl_dom, $name);

									$list[$name] = array('in'=>$paramList,'out'=>$return, 'doc'=>$documentation);
							}
							ksort($list);
							$this->__wsdl_parsed['operations'] = $list;
					break;
					case 'types':
							$types = $this->__getTypes();
							$list = array();
							foreach($types as $type){
									$parts = explode("\n", $type);
									$prefix = explode(' ', $parts[0]);
									$class = $prefix[1];
									// array
									if(substr($class,-2) == '[]'){
											$class=substr($class,0,-2);
											$list[$class] = array($prefix[0]);
											continue;
									}
									// 'ArrayOf*' types (from MS.NET, Axis etc.)
									if(substr($class,0,7) == 'ArrayOf'){
											list($type, $member) = explode(' ',trim($parts[1]));
											$list[$class] = array($type);
											continue;
									}
									$members = new \stdClass();
									for($i = 1; $i < count($parts) - 1; $i++) {
											$parts[$i] = trim($parts[$i]);
											list($type, $member) = explode(' ',substr($parts[$i],0,-1));
											if(preg_match('/^$\w[\w\d_]*$/', $member)) {
													throw new Exception('illegal syntax for member variable: ' . $member);
											}
											if(strpos($member, ':')) { // keep the last part
													$tmp = explode(':', $member, 2);
													$member = (isset($tmp[1])) ? $tmp[1] : null;
											}
											$add = true;
											foreach($members as $mem) {
													if(isset($mem['member']) && $mem['member'] == $member){
															$add = false;
													}
											}
											if($add) $members->$member = $type;
									}
									// gather enumeration values
									$values = array();
									if (count((array)$members) == 0) {
											$this->__loadWSDL();
											$values = $this->__checkForEnum($this->__wsdl_dom, $class);
											if($values){
													//$list[$class] = array($class=>$values);
													$list[$class] = 'string';
											}
											else{
													if($prefix[0]=='struct') $list[$class] = '';
													else{
															$list[$class] = $prefix[0];
													}
											}
									}
									else $list[$class] = $members;
							}
							ksort($list);
							$this->__wsdl_parsed['types'] = $list;
					break;
					case 'typesTxt':
							$types = $this->__getTypes();
							$list = array();
							foreach($types as $type){
									$parts = explode("\n", $type);
									$prefix = explode(' ', $parts[0]);
									$class = $prefix[1];
									// array
									if(substr($class,-2) == '[]'){
											$class=substr($class,0,-2);
											$list[$class] = 'array '.$prefix[0];
											continue;
									}
									// 'ArrayOf*' types (from MS.NET, Axis etc.)
									if(substr($class,0,7) == 'ArrayOf'){
											list($type, $member) = explode(' ',trim($parts[1]));
											$list[$class] = 'array '.$type;
											continue;
									}
									$members = new \stdClass();
									for($i = 1; $i < count($parts) - 1; $i++) {
											$parts[$i] = trim($parts[$i]);
											list($type, $member) = explode(' ',substr($parts[$i],0,-1));
											if(preg_match('/^$\w[\w\d_]*$/', $member)) {
													throw new Exception('illegal syntax for member variable: ' . $member);
											}
											if(strpos($member, ':')) { // keep the last part
													$tmp = explode(':', $member, 2);
													$member = (isset($tmp[1])) ? $tmp[1] : null;
											}
											$add = true;
											foreach($members as $mem) {
													if(isset($mem['member']) && $mem['member'] == $member){
															$add = false;
													}
											}
											if($add) $members->$member = $type;
									}
									// gather enumeration values
									$values = array();
									if (count((array)$members) == 0) {
											$this->__loadWSDL();
											$values = $this->__checkForEnum($this->__wsdl_dom, $class);
											if($values){
													//$list[$class] = array($class=>$values);
													$list[$class] = 'string';
											}
											else{
													if($prefix[0]=='struct') $list[$class] = '';
													else{
															$list[$class] = $prefix[0];
													}
											}
									}
									else $list[$class] = $members;
							}
							ksort($list);
							print_object($list);
							$this->__wsdl_parsed['typesTxt'] = $list;
					break;

			}
	}

		/**
	 * Look for enumeration
	 * @param DOM $dom
	 * @param string $class
	 * @return array
	 */
	protected function __checkForDocumentation(&$dom, $class) {
		$return = '';
		$xpath = new \DOMXPath($dom);
		$ns = $dom->documentElement->namespaceURI;
		//$query = "operation[@name='RegistarMovimentoCartao']/documentation";
		$query = "documentation";
		//$query = "documentation";
		if($ns) {
			$xpath->registerNamespace("ns", $ns);
			$nodes = $xpath->query("//ns:".$query);
		} else {
			$nodes = $xpath->query("//".$query);
		}	
		//var_dump($nodes);	
		foreach($nodes as $node) {
			if ($node->parentNode->attributes['name']->value == $class)
				$return = $node->textContent;
		} 

		return $return;
	}

	/**
	 * Look for enumeration
	 * @param DOM $dom
	 * @param string $class
	 * @return array
	 */
	protected function __checkForEnum(&$dom, $class) {
			$values = array();
			$node = $this->__findType($dom, $class);
			if (!$node) {
					return $values;
			}
			$value_list = $node->getElementsByTagName('enumeration');
			if ($value_list->length == 0) {
					return $values;
			}
			for ($i = 0; $i < $value_list->length; $i++) {
					$values[] = $value_list->item($i)->attributes->getNamedItem('value')->nodeValue;
			}
			return $values;
	}
	/**
	 * Look for a type
	 * @param DOM $dom
	 * @param string $class
	 * @return DOMNode
	 */
	protected function __findType(&$dom, $class) {
			$types_node = $dom->getElementsByTagName('types')->item(0);
			$schema_list = $types_node->getElementsByTagName('schema');
			for ($i = 0; $i < $schema_list->length; $i++) {
					$children = $schema_list->item($i)->childNodes;
					for ($j = 0; $j < $children->length; $j++) {
							$node = $children->item($j);
							if ($node instanceof DOMElement &&
											$node->hasAttributes() &&
											is_object($node->attributes->getNamedItem('name')) &&
											$node->attributes->getNamedItem('name')->nodeValue == $class) {
									return $node;
							}
					}
			}
			return null;
	}

}

/**
 * Description of SoapConnector
 *
 * @author paulo.tavares
 */
class SoapConnector extends \Kuink\Core\DataSourceConnector{
  
  var $soapClient; //The soapClient object

  function connect( $cacheWsdl=true) {
  	global $KUINK_CFG;
	//neon_mydebug(__CLASS__, __METHOD__);

     if (! $this->soapClient) {
			$wsdl = $this->dataSource->getParam('wsdl', true);
			if (substr( $wsdl, 0, 4 ) !== "http")
				$wsdl=$KUINK_CFG->appRoot.'/apps/'.$wsdl;
      $user = $this->dataSource->getParam('user', true);
			$passwd = $this->dataSource->getParam('passwd', true);

      $authParams = array('login' => $user,
      		'password' => $passwd);      
			if (!$cacheWsdl)
				$authParams['cache_wsdl'] = WSDL_CACHE_NONE;

      $this->soapClient = new NTLMSoapClient($wsdl, $authParams);
      $this->soapClient->setCredentials($user, $passwd);
     }
  }
  
  
  /**
   * @see \Kuink\Core\DataSourceConnector::insert()
   */
  function insert($params) {
  	global $KUINK_TRACE;
  	
  	$resultData=$this->execute($params);

    return $resultData; 
  }
  
  function update($params) {
  	global $KUINK_TRACE;
  	
  	$resultData=$this->execute($params);

    return $resultData; 
  }  
  
  function save($params) {
  	global $KUINK_TRACE;
  	
  	$this->execute($params);

    return; 
  }
    

  function delete($params) {
  	global $KUINK_TRACE;
  	
  	$resultData=$this->execute($params);

    return $resultData; 
  }
	
	// convert object to array
	protected function object_to_array($obj) {
		
		$arrObj = is_object ( $obj ) ? get_object_vars ( $obj ) : $obj;
		$arr=array();
    foreach ( $arrObj as $key => $val ) {
      $val = (is_array ( $val ) || is_object ( $val )) ? $this->object_to_array ( $val ) : $val;
      $arr [$key] = $val;
		}
    return $arr;
  }
  
  /***
   */
  function execute($params) {
  	global $KUINK_TRACE;

  	//Never execute when this datasource is configured as bypass
  	if ($this->dataSource->bypass) {
			$KUINK_TRACE[]= $this->dataSource->name.' BYPASS=TRUE';
			$KUINK_TRACE[]= $params;
  		$KUINK_TRACE[]= 'Bypassing call to the server';
  		return null;
  	}
  	 
  	$wsFunction = $params['_entity'];
  	$this->connect(false);
  
  	//$request=$this->prepareRequestToExecute($params);
  
  	$prefix=$this->dataSource->getParam('prefix', false);
  	$location=$this->dataSource->getParam('server', true);
  	$action=$prefix.$wsFunction;
  	$version=$this->dataSource->getParam('version', true);
  	$this->user=$this->dataSource->getParam('user', true);
  	$this->passwd=$this->dataSource->getParam('passwd', true);
  	$oneWay=0;
  	 
  	$entity = (string) $params['_entity'];
  
  	unset($params['_pk']);
  	unset($params['_entity']);
  	$newParams = $this->convertParamsToString($params);

		//Inject in the request all mandatory params set in the datasource definition (param name params)
		$response = array();
  	$mandatoryParams=$this->dataSource->getParam('params', false, array());
  	foreach ($mandatoryParams as $key=>$value)
  		$newParams[$key] = (string)$value;
  	try {
			// hack to cast to array a object of objects
			$response = $this->object_to_array($this->soapClient->$entity($newParams));

  	} catch (\Exception $e) {
  		$KUINK_TRACE[]='Error calling webservice '.$this->dataSource->name.':'.$entity.':'.$e->getMessage() ;
  	}

  	return (isset($response) ? $response : null);
	}
	
  protected function convertParamsToString($params) {
		$newParams=array();
		foreach ($params as $key=>$value) {
			if (is_array($value))
				$newParams[$key] = $this->convertParamsToString($value);
			else
				$newParams[$key] = (string)$value;
		}
		return $newParams;
	}
  
  /***
  */
  function executeLegacy($params) {
  	global $KUINK_TRACE;

  	//Never execute when this datasource is configured as bypass
  	if ($this->dataSource->bypass) {
  		$KUINK_TRACE[]= $this->dataSource->name.' BYPASS=TRUE';
  		$KUINK_TRACE[]= 'Bypassing call to the server';
  		return null;
  	}
  	
  	$wsFunction = $params['_entity'];
  	$this->connect();
  	 
	$request=$this->prepareRequestToExecute($params);
	  	
  	$prefix=$this->dataSource->getParam('prefix', false);
  	$location=$this->dataSource->getParam('server', true);
  	$action=$prefix.$wsFunction;
  	$version=$this->dataSource->getParam('version', true);;
  	$user=$this->dataSource->getParam('user', true);;
  	$passwd=$this->dataSource->getParam('passwd', true);;
  	$oneWay=0;
  	
  	$KUINK_TRACE[]='Calling webservice: '.$this->dataSource->name.' : '.$params['_entity'];
		$rawXMLresponse = $this->soapClient->__doRequestLegacy($request, $location, $action, $version, $oneWay, $user, $passwd);
	
    return $rawXMLresponse; 
  }

  function getEntities($params) {
  	global $KUINK_TRACE;
  	 
  	$this->connect( false );

		$result = $this->soapClient->__getFunctionsDocumentation();
		//print_object($this->soapClient->__getFunctions());


  	$entities = array();
		//$result = $this->soapClient->__getFunctions();
			//$typesParsed = $this->getParsedTypesTxt();
		//var_dump($types);

  	foreach ($result as $function=>$attributes) {
  		//$comp=explode('(', $function);
  		//$comp2=explode(' ',$comp[0]);
  		$entity=array();
			$entity['entity']=$function;
			$params = $this->soapClient->__getParams($function);
			$returnType = $this->soapClient->__getReturn($function);
			$return = $this->soapClient->__getType($returnType);

			//print_object($name);
			$inputType = $this->soapClient->__getTypeTxt($attributes['in']['parameters']);
			$outputType = $this->soapClient->__getTypeTxt($attributes['out']);
			//kuink_mydebug($attributes['in']['parameters'], $inputType);

			//print_object($return);
			//$inputType = str_replace(' $parameters)', '', $comp[1]);
			$entity['input'] = '';//$inputType;
			$entity['inputType'] = $inputType;//$typesParsed[$attributes['in']['parameters']];
			$entity['output'] = '';//$comp2[0];
			$entity['outputType'] = $outputType;//$typesParsed[$attributes['out']];//$this->soapClient->__getType($attributes['out']);//$typesParsed[$attributes['out']];
			$entity['doc'] = (string) $attributes['doc'];
  		$entities[$function] = $entity;
  	} 
  	return $entities;
  }

  function getAttributes($params) {
  	global $KUINK_TRACE;
  
  	$type=$params['_entity'];
  	$this->connect();
  	 
  	$result = $this->parseTypes($this->soapClient->__getTypes());

  	return isset($result[$type]) ? $result[$type] : null;
  }
  
  private function parseTypes($types) {
  	$attributes=array();
  	foreach ($types as $type) {
  	 $comp=explode('{', $type);
  	 $typeName = trim(str_replace('struct ', '', $comp[0]));
  	 $cleanType=str_replace('; }', '', $comp[1]);
  	 $cleanType=str_replace(' }', '', $cleanType);
  	 $comp2=explode(';', $cleanType);
  	 $attrs=array();
  	 foreach($comp2 as $attribute) {
  	 	$comp3=explode(' ', $attribute);
  	 	//print_object($comp3);
  	 	$attr=array();
  	 	$attr['type'] = isset($comp3[1]) ? $comp3[1] : null;
  	 	$attr['name'] = isset($comp3[2]) ? $comp3[2] : null;
  	 	if (isset($comp3[2]) && $comp3[2]!='')
  	 		$attrs[]=$attr;
  	 }
  	 //		$entity=array();
  	 //		$entity['entity']=$comp2[1];
  	 //		$entity['input']=str_replace(' $parameters)', '', $comp[1]);
  	//$entity['output']=$comp2[0];
  	
  	//$entities[]=$entity;
  	 $attributes[$typeName] = $attrs;
  	 //print_object($comp2);
  	 
  	}
  	//print_object($attributes);
  	return $attributes;
  	  
  } 
  
  function load($params) {
  	global $KUINK_TRACE;
  	
  	$resultData=$this->execute($params);

    return isset($resultData[0])?$resultData[0]:null; 
  }
  
  function getAll($params) {
  	global $KUINK_TRACE;
  	
  	$resultData=$this->execute($params);

    return $resultData; 
  }
  
  /**
   * This will receive the params of the statement and will transform
   * the preparedStatementXml into a PDO ready string
   * @param unknown $params
   * @return unknown
   */
  private function prepareRequestToExecute($params) {
  	$prefix=$this->dataSource->getParam('prefix', false);
  	$entity=$params['_entity'];
  	$call = '<'.$entity.' xmlns="'.$prefix.'">';
  	
  	unset($params['_pk']);
  	unset($params['_entity']);
  	
  	$soapParams='';
  	foreach ($params as $paramName=>$paramValue) {
        if (is_array($paramValue)) {
            $value = '';
            foreach ($paramValue as $pKey => $pValue)
                $value .= '<' . $pKey . '>' . $pValue . '</' . $pKey . '>';
        } else {
            $value = $paramValue;
        }
        $soapParams .= '<' . $paramName . '>' . $value . '</' . $paramName . '>';
    }
  	$call .= $soapParams.'</'.$entity.'>';
  		
  	$envelope='
  		<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
		  <soap:Body>'.$call.'
		  </soap:Body>
		</soap:Envelope>';
  
  	return $envelope;
	}  
	
	public function getSchemaName($params) {
		return null;
	}
}

?>
