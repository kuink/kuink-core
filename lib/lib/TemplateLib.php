<?php

use Kuink\Core\NodeManager;
// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework.  If not, see <http://www.gnu.org/licenses/>.

class TemplateLib
{
	var $nodeconfiguration;
	var $msg_manager;
    function  TemplateLib($nodeconfiguration, $msg_manager) {
    	$this->nodeconfiguration = $nodeconfiguration;
    	$this->msg_manager = $msg_manager;
        return;
    }

    function GetTemplateHtml( $params ){
        $templateName = $params[0];
        $data = $params[1];
//        echo "<pre>";
//        var_dump($data);
//        echo "</pre>";
        $d =  \Kuink\UI\Layout\Adapter\Smarty::getTemplate($templateName, $data);
        return $d;
    }

    function ExecuteTemplate( $params ){
        $application = (string)$params[0];
        $process = (string)$params[1];
        $templateName = (string)$params[2];
        $data = $params[3];
        /*
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        */
        $d =  \Kuink\UI\Layout\Adapter\Smarty::getApplicationTemplate($application, $process, $templateName, $data);
        return $d;
    }


    function ExecuteStandardTemplate( $params ){
    	global $KUINK_APPLICATION;
    	
    	$name = (string)$params[0];

    	$nameParts = explode(',', $name);
    	if ( count($nameParts) != 3 )
    		throw new \Exception('Template: name must be method or appName,processName,template');

    	$application = (trim($nameParts[0]) == 'this') ? $this->nodeconfiguration[\Kuink\Core\NodeConfKey::APPLICATION] : trim($nameParts[0]);
    	$process = (trim($nameParts[1]) == 'this') ? $this->nodeconfiguration[\Kuink\Core\NodeConfKey::PROCESS] : trim($nameParts[1]);
    	$templateName = trim( $nameParts[2] );
 	 
    	
    	$lang = isset($params[1]) ? $params[1] : $KUINK_APPLICATION->getLang();
    	$data = $params[2];
    	
    	
    	
    	
    	
    	//Read the template file
    	$nodeManager = new \Kuink\Core\NodeManager($application, $process, 'templates', $templateName.'.'.$lang);
    	
    	//check if the file exists, else try to open in portuguese
    	if (!$nodeManager->exists())
    		$nodeManager = new \Kuink\Core\NodeManager($application, $process, 'templates', $templateName.'.pt');
    	
    	$nodeManager->load();

    	$keys = $nodeManager->nodeXml->xpath('/Template/*');
    	
    	$result = array();

    	//Process all the keys
    	foreach($keys as $key) {
    		$keyName = (string)$key->getName();
    		$keyValue = (isset($key['value'])) ? (string)$key['value'] : (string)$key[0];
    		$template = (isset($key['template'])) ? (string)$key['template'] : null;

    		if (count($key->children()) > 0) {
    			//Handle the child nodes
    			$childs = array();
    			foreach($key->children() as $child) {
    				$childName = $child->getName();
    				$childValue = (isset($child['value'])) ? (string)$child['value'] : (string)$child[0];
    				$eval = new \Kuink\Core\EvalExpr();
    				$childValue = $eval->e( $childValue, $data, FALSE, TRUE, FALSE); //Eval and return a value without ''
    				
    				$childs[$childName] = $childValue;
    			}
    			$parsedValue = $childs;
    		}
    		else {
	    		//If template isset then get the template from smarty
	    		if ($template)
					$parsedValue = \Kuink\UI\Layout\Adapter\Smarty::getApplicationTemplate($application, $process, $template, $data);
	    		else {
	    			$eval = new \Kuink\Core\EvalExpr();
	    			$parsedValue = $eval->e( $keyValue, $data, FALSE, TRUE, FALSE); //Eval and return a value without ''
	    		}
    		}
    		$result[$keyName] = $parsedValue;
    	}
    	
    	return $result;
    }
    
    
    function expandTemplate($params){
        $templateCode = (string)$params[0];
        $data = $params[1];
        $d = \Kuink\UI\Layout\Adapter\Smarty::expandTemplate($templateCode, $data);
        return $d;

    }

  
  
}

?>
