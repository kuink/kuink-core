<?php

namespace Kuink\Core\DataSourceConnector;

use Kuink\Core\TraceManager;
use Kuink\Core\TraceCategory;

/**
 * Used to read and write structured text files
 *
 * @author paulo.tavares
 */
class TextFileConnector extends \Kuink\Core\DataSourceConnector {
	
	var $fileName; //The filepath of the file to process
	var $isFixed; //This file is fixed or as a separator
	var $enclosure; //The list of separators
	var $delimiter; //The string delimiter char
	var $firstLineWithColumnNames;
	var $configuration; //Configuration of the file, can be an array or a json string
	var $cache; //Cache an array

	function connect() {
		$this->fileName = $this->dataSource->getParam('fileName', true);
		$isFixed = $this->dataSource->getParam('isFixed', true);
		$this->isFixed = ($isFixed == 'true') ? true : false; //Convert to boolean
		$firstLineWithColumnNames = $this->dataSource->getParam('firstLineWithColumnNames', true);
		$this->firstLineWithColumnNames = ($firstLineWithColumnNames == 'true') ? true : false; //Convert to boolean
		$enclosure = $this->dataSource->getParam('enclosure', false);
		$this->enclosure = ($enclosure=='') ? '' : $enclosure;
		$delimiter = (string) $this->dataSource->getParam('delimiter', false);
		$this->delimiter = ($delimiter=='') ? '' : $delimiter;
		$this->configuration = $this->dataSource->getParam('configuration', false);
    if ($this->configuration == '')
      $this->configuration = null;
		if (!$this->cache)
			$this->cache = array();
	}

	function execute($params) {
		throw new \Exception ( 'Not implemented' );		
	}

	function insert($params) {
		$this->connect ();		
		unset($params['_pk']);
		$this->cache[] = $params;
	}
	
	function flush($params) {
		$this->connect ();
		return $this->arrayToFile($this->cache, (string)$this->fileName, $this->isFixed, $this->firstLineWithColumnNames, $this->enclosure, $this->delimiter, $this->configuration);
	}

	function update($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}

	function delete($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}

	function load($params) {
		throw new \Exception ( 'Not implemented' );
		
		$this->connect ();
	}

	function getAll($params) {
		$this->connect ();
		return $this->fileToArray($this->fileName, $this->isFixed, $this->firstLineWithColumnNames, $this->enclosure, $this->delimiter, $this->configuration);
	}

	public function getSchemaName($params) {
		return null;
	}

	function fileToArray(string $fileName, bool $isFixed, bool $firstLineWithColumnNames = true, string $enclosure = null, string $delimiter = null, $configuration = null) {//json or array

		//verify file
		if(!file_exists($fileName) || !is_readable($fileName)){
			echo "file not found"; 
			return FALSE;
		}       
    	
				   
		if(isset($configuration)) {
			//__________________________________________________________Verify configuration____________________________
			//analise array for correct configuration:
			//"name1":{"start":0, "length":9},
			//"name2":{"start":9, "length":8}   
	
			if(!is_array($configuration)){
				if(!$this->isJson($configuration)){
					echo "invalid configuration";
					return false;
				}else{
					//echo "ok ready to use json";
					$configuration = json_decode($configuration,true);
				}
				
			}else{
				//echo "ok ready to use array"; 
			}
	
			if($isFixed) {//config for fixed colunm
				foreach($configuration as $column_name => $arr) {
					if (array_key_exists('start',$arr) and array_key_exists('length',$arr)){            
						$start = $arr['start'];
						$length = $arr['length'];              
						if (is_int($start) AND is_int($length)){
							//keys ok
						
						}
					}else{//nok  
					  
						
					echo "error in keys:" ;
					return false;
					}  
				}         
			}else{//config for CSV header colunm
				
	
			}
		}
		//____________________________________________________________________________________________________________    
		
		
		if($isFixed) {
			// _______________________________________type TXT column fixed_________________________________________
			if(!isset($configuration)) {
				echo "invalid configuration";
				return false;
			}
			try{
			$file1 = fopen($fileName, "r");
			$array_output = array();
			while ($line = fgets($file1)) {
				$arr_field = array();
				foreach($configuration as $column_name => $arr) {
								  
						$start = $arr['start'];
						$length = $arr['length'];
						if(isset($arr['padr']) or isset($arr['padl'])){//remove characters at left
							if(isset($arr['padl'])){
								$char = $arr['padl'];
								$rawText = substr($line, $start, $length);
								$textField = ltrim($rawText,$char);
								$textField = str_replace("\r\n","", $textField);
								$arr_field[$column_name] = str_replace("\n","", $textField);
							}else{
								$char = $arr['padr'];
								$rawText = substr($line, $start, $length);
								$textField = rtrim($rawText,$char);
								$textField = str_replace("\r\n","", $textField);
								$arr_field[$column_name] = str_replace("\n","", $textField);
	
							}
							
						}
						else{
							$rawText = substr($line, $start, $length);
							$textField = str_replace("\r\n","", $rawText);
							$arr_field[$column_name] = str_replace("\r","", $textField);
	
						}                   
								
					
				} 
				$array_output[] = $arr_field;
			
			}
			fclose($file1);
			
			return $array_output; 
			} 
			catch(Exception $e){
				echo "error processing file";
			} 
		}else{ // __________________________________________type CSV___________________________________________
			
			if (!isset($delimiter)){
				//____________________________________FIND OUT DELIMITER_______________________________
				$delimiters = array(
					'' => 1,
					';' => 0,
					',' => 0,
					"\t" => 0,
					"|" => 0
				);
				$handle = fopen($fileName, "r");
				$firstLine = fgets($handle);
				fclose($handle);
				$count = 0; 
				foreach ($delimiters as $delimiter => &$count) {
					$count = count(str_getcsv($firstLine, $delimiter));
				}   
				$delimiter = array_search(max($delimiters), $delimiters);
				}
						   
				$header = NULL;
				ini_set("auto_detect_line_endings", true);
	
				if (!isset($enclosure)){//default
					$enclosure = '"';
				}
				try{
				$array_output = array();
        $arr_col = array();
			  $file2 = fopen($fileName, 'r');
				
					$cName = 0;
					while (($row = fgetcsv($file2, 1000, $delimiter,$enclosure)) !== FALSE)                
					{					
						if(!$header){//its executes only one time
							$first_line = $row;
							if(isset($configuration)) {//use custom configuration about columns
								$header = $configuration;                      
								foreach ($header as $key => $value) {
									++$cName;
									$arr_col[] = $key;                                    
								}
								
								if (count($row)<> count($arr_col)){
									
									echo "invalid configuration: field<>array: ". count($row).','.count($arr_col);
									
								}
								$array_output[] = array_combine($arr_col, $row);//first element
							}else{
								$header = $first_line;       
								foreach ($header as $key => $value) {
									++$cName;
									$arr_col[] = $value;                                    
								}
							}
						}else{
								$array_output[] = array_combine($arr_col, $row);
						}
								   
					}
					fclose($file2);
					
					return $array_output; 
				  
				} 
				catch(Exception $e){
					echo "error processing file";
				} 
	  }
	}

	function arrayToFile($inputArray, string $fileName, int $isFixed, bool $firstLineWithColumnNames = true, string $enclosure = '"', string $delimiter = ";", $configuration = null) {
		
		if(!is_array($inputArray)){ 
			if(!$this->isJson($inputArray)){
				echo "invalid array";
				return false;
			}else{
				//echo "ok ready to use json";
				$inputArray = json_decode($inputArray,true);
			}    
			
		}
	
	
		//verify config           
		if(isset($configuration)) {
			//__________________________________________________________Verify configuration____________________________
			//analise array for correct configuration:
			//"name1":{"start":0, "length":9},
			//"name2":{"start":9, "length":8, "pad":0}   
	
			if(!is_array($configuration)){
				if(!$this->isJson($configuration)){
					echo "invalid configuration";
					return false;
				}else{
					//echo "ok ready to use json";
					$configuration = json_decode($configuration,true);
				}
				
			}else{
				//echo "ok ready to use array"; 
			}
			
			if($isFixed) {  //is txt              
				foreach ($configuration as $key => $value) {  
					if (isset($configuration[$key]['start'])){
						if (isset($configuration[$key]['length'])){
						   //ok
						}    
					}               
					
														
				}
				
				// config and input must to be the same size:
				if(!count($configuration)==count($inputArray)) {
					echo "differents size in config";
					return false;
				}            
			}
		}
	
		//____________________________________________________________________________________________________________    
	   
		
		
		if($isFixed) {
			// _______________________________________type TXT column fixed_________________________________________
			if(!isset($configuration)) {
				echo "invalid configuration";
				return false;
			}
			try{
	
			//verify file    
			$dir_name = dirname($fileName);
			if (file_exists("$dir_name")){
				
			$fileOutput = fopen($fileName, "w") or die("error creating file: ".$fileName); //openfile
				
			
			}else{
				echo "path not found: ".$dir_name; 
				return false;
			}
			//_____________________________________________________________
			
	
				foreach($inputArray as $kline => $vline) {
					$arr_line = array();
					foreach ($inputArray[$kline] as $kvalue => $vvalue) {
						
						//array_push($arr_line, 'value' => $xxx);
						$arr_line[] = $vvalue;
					}
					$c_item = 0;
					$line_string = '';
					//echo json_encode($configuration);
					foreach ($configuration as $kconfig => $vconfig) {  
						$char_fill = " ";//default
						$side_pad = STR_PAD_LEFT;
						$line_start = $configuration[$kconfig]['start'];
						$line_length = $configuration[$kconfig]['length'];
						$newstring = substr($arr_line[$c_item],0,$line_length);
						if (isset($configuration[$kconfig]['padr'])){
							$char_fill = $configuration[$kconfig]['padr']; 
							$side_pad = STR_PAD_RIGHT;                       
						}
						if (isset($configuration[$kconfig]['padl'])){
							$char_fill = $configuration[$kconfig]['padl'];
							$side_pad = STR_PAD_LEFT;                        
						}
	
						if (strlen($newstring)>= $line_length){
							$line_string = $line_string.substr($newstring,0,$line_length);
						}else{
							$line_string = $line_string.str_pad($newstring, $line_length, $char_fill, $side_pad);
							
						}
			   
						
						++$c_item;
							   
					}
					
					fwrite($fileOutput, $line_string. "\n");
	
				} 
			   
				fclose($fileOutput);
				return true;
			 
			} 
			catch(Exception $e){
				echo "error processing file";
			} 
		}else{ // __________________________________________type CSV___________________________________________
			if (!isset($enclosure)){
				$enclosure = '"';
			}
	
			if (!isset($delimiter)){//default
				$delimiter = ';';
			}
	
			try{
	
				//verify file    
				$dir_name = dirname($fileName);
				if (file_exists("$dir_name")){
					
				$fileOutput = fopen($fileName, "w") or die("error creating file: ".$fileName); //openfile
				//fputs($fileOutput, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));    
				
				}else{
					echo "path not found: ".$dir_name; 
					return false;
				}
				//_____________________________________________________________
				$header = NULL;
				$header_list = array();
				


				foreach($inputArray as $kline => $vline) {
					$list_line = array();
					foreach($inputArray[$kline] as $field => $fvalue) {
						$header_list[] = $field;
						$list_line[] = $inputArray[$kline][$field];
					}
					if(!$header){//its executes only one time
						if ($firstLineWithColumnNames){
							fputcsv($fileOutput,$header_list,$delimiter,$enclosure); //headers
						}else{
							$list[] = $list_line;
						}
						$header = true;
					}else{
							$list[] = $list_line;
					}
					
				
					
				}
				
				foreach ($list as $line) {
					
					fputcsv($fileOutput,$line,$delimiter,$enclosure);
				  }
				
				 
				fclose($fileOutput);
				return true;
	
				 
				} 
				
				catch(Exception $e){
					echo "error processing file";
				} 
	
			}
	
	
	  
	}

	function isJson($string) {
		// decode the JSON data
		$result = json_decode($string);
	
		// switch and check possible JSON errors
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				$error = ''; // JSON is valid // No error has occurred
				break;
			case JSON_ERROR_DEPTH:
				$error = 'The maximum stack depth has been exceeded.';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Invalid or malformed JSON.';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Control character error, possibly incorrectly encoded.';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON.';
				break;
			// PHP >= 5.3.3
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
				break;
			// PHP >= 5.5.0
			case JSON_ERROR_RECURSION:
				$error = 'One or more recursive references in the value to be encoded.';
				break;
			// PHP >= 5.5.0
			case JSON_ERROR_INF_OR_NAN:
				$error = 'One or more NAN or INF values in the value to be encoded.';
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$error = 'A value of a type that cannot be encoded was given.';
				break;
			default:
				$error = 'Unknown JSON error occured.';
				break;
		}
	
		if ($error !== '') {
			// throw the Exception or exit // or whatever :)
			return false;
		}
	
		// everything is OK
		return true;
	}


}

?>
