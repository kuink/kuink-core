<?php

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.

// require_once('../../config.php');
global $KUINK_BRIDGE_CFG;

require_once ('lib/core/ProcessOrchestrator.php');

unset ( $KUINK_CFG );
$KUINK_CFG = new stdClass ();
// New neon configuration object
$wwwroot = '';
if (empty ( $KUINK_BRIDGE_CFG->loginHttps )) {
	$wwwroot = $KUINK_BRIDGE_CFG->wwwRoot;
} else {
	// This actually is not so secure ;-), 'cause we're
	// in unencrypted connection...
	$wwwroot = str_replace ( 'http://', 'https://', $KUINK_BRIDGE_CFG->wwwRoot );
}

// Initialize the contextid if it isn't set yet, important for api's
$contextId = \Kuink\Core\ProcessOrchestrator::getContextId ();

$KUINK_CFG->bridge = $KUINK_BRIDGE_CFG->bridge;
$KUINK_CFG->wwwRoot = $wwwroot;
$KUINK_CFG->dirRoot = $KUINK_BRIDGE_CFG->dirRoot;
$KUINK_CFG->kuinkRoot = $KUINK_BRIDGE_CFG->kuinkRoot; // 'mod/kuink/';
//$KUINK_CFG->themeRoot = 'kuink-core/';
$KUINK_CFG->themeRoot = $KUINK_BRIDGE_CFG->themeRoot;; //defaults to the bridge... let the bridge control the theme
$KUINK_CFG->apiUrl = $KUINK_CFG->kuinkRoot.'/api.php?idcontext=' . $contextId;
$KUINK_CFG->streamUrl = $KUINK_CFG->wwwRoot.'stream.php';
$KUINK_CFG->streamFileUrl = $KUINK_CFG->streamUrl.'?type=file&guid=';
$KUINK_CFG->apiCompleteUrl = $wwwroot . '/' . $KUINK_CFG->apiUrl . '&neonfunction=';
$KUINK_CFG->guestUrl = $wwwroot . '/mod/kuink/auth_guest.php';
$KUINK_CFG->dataRoot = $KUINK_BRIDGE_CFG->dataRoot;
$KUINK_CFG->appRoot = $KUINK_BRIDGE_CFG->appRoot;
$KUINK_CFG->uploadRoot = $KUINK_CFG->appRoot . 'files/';
$KUINK_CFG->uploadVirtualPrefix = $KUINK_BRIDGE_CFG->uploadVirtualPrefix;//Only for neon compatibility. Leave blank in a fresh install.

$KUINK_CFG->layoutCache = false;
$KUINK_CFG->externalServiceRoot = $KUINK_CFG->appRoot . 'apps/_externalServices/';
$KUINK_CFG->defaultTimezone = 'Europe/Lisbon';
$KUINK_CFG->useGlobalACL = false; //default
$KUINK_CFG->trigger = $KUINK_BRIDGE_CFG->trigger;
$KUINK_CFG->auth = $KUINK_BRIDGE_CFG->auth; //The authenticated user
$KUINK_CFG->application = $KUINK_BRIDGE_CFG->application; //Current base application
$KUINK_CFG->configuration = $KUINK_BRIDGE_CFG->configuration; //Current base application configuration

// Getting the environment configuration dev|test|prod
$fileContents = (file_exists ( $KUINK_CFG->appRoot . 'env.txt' )) ? file_get_contents ( $KUINK_CFG->appRoot . '/env.txt' ) : 'dev';
$KUINK_CFG->environment = str_replace ( array (
		"\r",
		"\n" 
), '', $fileContents );

// If imageRemote is defined, then this location is used to load image instead of local folder

switch ($KUINK_CFG->environment) {
	case 'dev' :
		$KUINK_CFG->theme = 'adminlte'; // "default" or "adminLTE" for experimental theme
		$KUINK_CFG->imageRemote = '/kuink/kuink-core/theme/' . $KUINK_CFG->theme . '/img/';		
		$KUINK_CFG->enableEmailSending = false;
		$KUINK_CFG->useGlobalACL = false;
		$KUINK_CFG->displayNativeErrors = false;		
		break;
	case 'test' :
		$KUINK_CFG->theme = 'adminlte'; // "default" or "adminLTE" for experimental theme
		$KUINK_CFG->imageRemote = '/kuink/kuink-core/theme/' . $KUINK_CFG->theme . '/img/';
		$KUINK_CFG->enableEmailSending = false;
		$KUINK_CFG->useGlobalACL = false;		
		$KUINK_CFG->displayNativeErrors = false;		
		break;
	case 'prod' :
		$KUINK_CFG->theme = 'adminlte'; // "default" or "adminLTE" for experimental theme
		$KUINK_CFG->imageRemote = '/kuink/kuink-core/theme/' . $KUINK_CFG->theme . '/img/';
		$KUINK_CFG->enableEmailSending = true;
		$KUINK_CFG->useGlobalACL = false;		
		$KUINK_CFG->displayNativeErrors = false;		
		break;
	default :
		throw new \Exception ( 'Invalid environment' . $KUINK_CFG->environment, 1 );
}

$KUINK_CFG->imageRoot = $KUINK_CFG->dirRoot .'/'. $KUINK_CFG->kuinkRoot .'/'.$KUINK_CFG->themeRoot . '/theme/' . $KUINK_CFG->theme . '/img/';

$KUINK_CFG->photoRemote = $KUINK_CFG->imageRemote . 'photo/';

// Do not let the Process Orchestrator contexts run out resources
$KUINK_CFG->allowMultipleContexts = true;
$KUINK_CFG->maxProcessOrchestratorContexts = 100;

// Try getting information about applications version and framework version
$frameworkVersionFile = dirname ( __FILE__ ) . '/version.txt';
$frameworkVersion = (file_exists ( $frameworkVersionFile )) ? file_get_contents ( $frameworkVersionFile ) : '';
$KUINK_CFG->frameworkVersion = $frameworkVersion;

$appsVersionFile = $KUINK_CFG->appRoot . 'apps/version.txt';
$appsVersion = (file_exists ( $appsVersionFile )) ? file_get_contents ( $appsVersionFile ) : '';
$KUINK_CFG->appsVersion = $appsVersion;

$KUINK_CFG->defaultDataSourceName = 'fw';

// Experimental features
$KUINK_CFG->postRedirectGet = false;
$KUINK_CFG->useNewDataAccessInfrastructure = true;


?>
