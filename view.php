<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of kuink
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package mod_kuink
 * @copyright 2010 Your Name
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $KUINK_INCLUDE_PATH, $KUINK_BRIDGE_CFG;

require_once ($KUINK_INCLUDE_PATH . 'kuink_includes.php');
require_once ('locallib.php');
// require_once($KUINK_INCLUDE_PATH.'test.php');

date_default_timezone_set ( 'UTC' );

$kuink_session_active = isset ( $_SESSION ['KUINK_CONTEXT'] ['KUINK_SESSION_ACTIVE'] ) ? $_SESSION ['KUINK_CONTEXT'] ['KUINK_SESSION_ACTIVE'] : 0;
if ($kuink_session_active != 1 && $_GET ['startnode'] != '')
	redirect ( $KUINK_BRIDGE_CFG->wwwroot, 0 );
	
	// Set up SINGLETON OBJECTS
$KUINK_LAYOUT = null; // Handles all the output, layouts, templates and themes
$KUINK_TRACE = array (); // Holds tracing information of the execution
$KUINK_MANUAL_TRACE = array (); // Holds manual tracing in execution
$KUINK_DATABASES = array (); // Holds all database connection objects
$KUINK_DATASOURCES = array (); // Will replace $KUINK_DATABASES
$KUINK_TRANSLATION = null; // Holds pointers to xml language files
$KUINK_APPLICATION = null; // The Application object to run

global $KUINK_CFG;

// Handling External Roles
$roles = array ();

$KUINK_LAYOUT = \Kuink\UI\Layout\Layout::getInstance ();
$KUINK_LAYOUT->setCache ( false );
$KUINK_LAYOUT->setTheme ( $KUINK_CFG->theme );

// Check to see if this is a call to a widget
// If so then the application will be given by the widget istead of the kuink configuration in moodle
$application = $KUINK_BRIDGE_CFG->application;
$configuration = $KUINK_BRIDGE_CFG->configuration;
$lang = $KUINK_BRIDGE_CFG->auth->user->lang;

//Setting the modal default to widgetContainer to display widgets correctly
$modal = isset($_GET['modal']) ? (string)$_GET['modal'] : '';
//if ($modal == '') 
//	$_GET['modal'] = 'widgetContainer';


if (isset ( $_GET ['idWidget'] )) {
	$KUINK_APPLICATION = new Kuink\Core\Application ( $application, $lang, $configuration );
	
	$idWidget = ( string ) $_GET ['idWidget'];
	$node = new \Kuink\Core\Node ( 'framework', 'widget', 'api' );
	
	$wsParams ['uuid'] = $idWidget;
	
	$wsResult = $KUINK_APPLICATION->run ( $node, 'getByGuid', $wsParams );
	$widgetData = $wsResult ['RETURN'];
	
	$application = $widgetData ['init_flow'];
	$configuration = $widgetData ['configuration'];
	
	unset ( $_GET ['idWidget'] );
	$KUINK_DATABASES = array (); // Holds all database connection objects
	$KUINK_DATASOURCES = array (); // Will replace $KUINK_DATABASES
	$KUINK_TRANSLATION = null; // Holds pointers to xml language files
	$KUINK_APPLICATION = null; // The Application object to run
}

// Creating the application
$KUINK_APPLICATION = new Kuink\Core\Application ( $application, $KUINK_BRIDGE_CFG->auth->user->lang, $configuration );
// Adding roles to the application
foreach ( $KUINK_BRIDGE_CFG->auth->roles as $role )
	$KUINK_APPLICATION->addRole ( ( string ) $role );
	
	// Run the application
$KUINK_APPLICATION->run ();

// Render the screen
$KUINK_LAYOUT->render ();

// Handling session expiration event
$_SESSION ['KUINK_CONTEXT'] ['KUINK_SESSION_ACTIVE'] = 1;
