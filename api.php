<?php
// This client for local_wstemplate is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC client for Moodle 2 - local_wstemplate
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @authorr Jerome Mouneyrac
 */

/// MOODLE ADMINISTRATION SETUP STEPS
// 1- Install the plugin
// 2- Enable web service advance feature (Admin > Advanced features)
// 3- Enable XMLRPC protocol (Admin > Plugins > Web services > Manage protocols)
// 4- Create a token for a specific user and for the service 'My service' (Admin > Plugins > Web services > Manage tokens)
// 5- Run this script directly from your browser: you should see 'Hello, FIRSTNAME'

//for all dates, set utc timezone. jmpatricio
date_default_timezone_set('UTC');

/*Kuink******************************************/
global $KUINK_INCLUDE_PATH;

require_once($KUINK_INCLUDE_PATH."kuink_includes.php");
/*Kuink******************************************/


function kuink_service_param_value( $wsParam ) {
    $value = isset( $_POST[ $wsParam ] ) ? $_POST[ $wsParam ] : $_GET[ $wsParam ];
    return stripslashes($value);
}

function kuink_service_param_values( $wsParams ) {
    $values = array();
    foreach ($wsParams as $wsParam) {
        $name = (string)$wsParam[ 'name' ];
        $values[ $name ] = kuink_service_param_value( $name );
    }
    return $values;
}

//Set up SINGLETON OBJECTS
$KUINK_LAYOUT 		= null; //Handles all the output, layouts, templates and themes
$KUINK_TRACE 		= array(); //Holds tracing information of the execution
$KUINK_DATABASES 	= array(); //Holds all database connection objects
$KUINK_TRANSLATION 	= null; //Holds pointers to xml language files
$KUINK_APPLICATION 	= NULL; //The Application object to run
global $KUINK_CFG;

//Validate key

$KUINK_LAYOUT = \Kuink\UI\Layout\Layout::getInstance();
$KUINK_LAYOUT->setCache(false);
$KUINK_LAYOUT->setTheme( $KUINK_CFG->theme );

$neonFunction = isset($_GET['neonfunction']) ? (string) $_GET['neonfunction'] : '';
$neonFunctionParsed = explode(',', $neonFunction);
$idcontext = (isset($_GET['idcontext'])) ? $_GET['idcontext'] : null;

$bypass = ($_SESSION['_kuink_api_security_bypass'] === true);
//$bypass = true;

$validRegisteredAPI = \Kuink\Core\ProcessOrchestrator::validRegisteredAPI($neonFunction, $idcontext, $bypass);


if (!$validRegisteredAPI) {
	//var_dump(\Kuink\Core\ProcessOrchestrator::getRegisteredAPIs($idcontext));
	throw new \Exception('No permission to execute API: '. $neonFunction );
}


if (count($neonFunctionParsed) != 4)
    throw new \Exception('Kuink Function must be application,process,library,function');

$wsApp 		= $neonFunctionParsed[ 0 ];
$wsProcess 	= $neonFunctionParsed[ 1 ];
$wsLibrary 	= $neonFunctionParsed[ 2 ];
$wsService 	= $neonFunctionParsed[ 3 ];


//Creating the application
$KUINK_APPLICATION = new \Kuink\Core\Application( $wsApp, $USER->lang, '<Configuration/>' );
$node = new \Kuink\Core\Node( $wsApp, $wsProcess, $wsLibrary );
//Get the function parametrs
$wsParams = \Kuink\Core\Reflection::getLibraryFunctionParams( $wsApp, $wsProcess, $wsLibrary, $wsService );
$wsValues = kuink_service_param_values( $wsParams );
//Validate the function params


$wsResult = $KUINK_APPLICATION->run($node, $wsService, $wsValues);

//$return = json_encode( $wsResult['RETURN'] );
$return = $wsResult['RETURN'];

$returnArray = array();
$isArrayOfStd = 0;
foreach ($return as $data) {
    if ( is_object( $data ) ) {
        $isArrayOfStd = 1;
        $returnArray[] = (array)$data;
    }
}
if ($isArrayOfStd == 0)
  $returnArray = $return;


        //var_dump( $returnArray );
header('Content-Type: application/json');
//header('Content-Type: application/x-www-form-urlencoded ');
echo json_encode( $returnArray );

//global $KUINK_TRACE;
//var_dump($KUINK_TRACE);

?>
