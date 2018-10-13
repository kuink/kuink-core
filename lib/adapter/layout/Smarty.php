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
namespace Kuink\UI\Layout\Adapter;

global $KUINK_INCLUDE_PATH;
require_once ($KUINK_INCLUDE_PATH . 'lib/tools/smarty/Smarty.class.php');
class Smarty extends \Smarty {
	private $themeName;
	private $appTemplate;
	private $userMessages = array ();
	private $positionsHtml = array ();
	private $menuItems = array ();
	function __construct($themeName = "default") {
		GLOBAL $KUINK_CFG;
		parent::__construct ();
		$this->setTemplateDir ( dirname ( __FILE__ ) . '/../../../theme/' . $themeName . '/template/' );
		$this->setCompileDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache_compiled/' );
		$this->setCacheDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache/' );
		
		$this->themeName = $themeName;
		$this->appTemplate = "1col";
		$this->assign ( 'THEME', $themeName );
		
		$context = \Kuink\Core\ProcessOrchestrator::getContext ();
		
		$this->assign ( '_idContext', ($context == null) ? null : $context->id );
		$this->assign ( '_apiUrl', $KUINK_CFG->apiUrl );
		$this->assign ( '_kuinkRoot', $KUINK_CFG->kuinkRoot );
		$this->assign ( '_themeRoot', $KUINK_CFG->themeRoot );
		$this->assign ( '_streamUrl', $KUINK_CFG->streamUrl );
		$this->assign ( '_apiCompleteUrl', $KUINK_CFG->apiCompleteUrl );
		$this->assign ( '_imageUrl', $KUINK_CFG->imageRemote );
		$this->assign ( '_photoUrl', $KUINK_CFG->photoRemote );
		$this->assign ( '_environment', $KUINK_CFG->environment );
		$this->assign ( '_lang', $KUINK_CFG->auth->user->lang );		
		$userEmail = '';
		if (isset($KUINK_CFG->auth->user->email) && ($KUINK_CFG->auth->user->email != 'root@localhost'))
			$userEmail = $KUINK_CFG->auth->user->email;
		$this->assign ( '_userEmail',  $userEmail  );		
		//Get rid of unnecessary reporting
		$this->error_reporting = E_ALL & ~E_NOTICE;
		$this->muteExpectedErrors();
		//Do not use this in production
		// $this->force_compile = true;
	}

	public function setTheme($themeName) {
		$this->setTemplateDir(dirname(__FILE__).'/../../../theme/'.$themeName.'/template/');
		$this->setCompileDir(dirname(__FILE__).'/../../../theme/theme_cache_compiled/');
		$this->setCacheDir(dirname(__FILE__).'/../../../theme/theme_cache/');
		
		$this->themeName = $themeName;
		$this->assign( 'THEME', $themeName );
		//print_object('Setting theme '. $themeName);
	}

	public function getTheme() {
		return($this->themeName);
	}	

	public function setAppTemplate($appTemplate) {
		$this->appTemplate = $appTemplate;
	}
	public function setRedirectHeader($url) {
		global $KUINK_CFG;
		
		if ($KUINK_CFG->postRedirectGet && $_GET ['nodeguid'] == '' && $_SERVER ['REQUEST_METHOD'] == 'POST') {
			header ( "HTTP/1.1 303 See Other" );
			header ( "Location: $url" );
		}
	}
	
	/**
	 * Render html to layout
	 * 
	 * @param array $html
	 *        	Html parts
	 */
	public function render($html) {
		global $KUINK_CFG;
		$POSITION = array ();
		// Set post redirect pattern to prevent double-click and F5
		// var_dump($_GET);
		// var_dump($_SERVER);
		
		$currentNode = \Kuink\Core\ProcessOrchestrator::getCurrentNode ();
		$qstrForm = isset($_GET ['form']) ? $_GET ['form'] : '';
		// var_dump($currentNode);
		$redirectUrl = $currentNode->url . '&action=' . $currentNode->action . '&actionvalue=' . $currentNode->actionValue . '&form=' . $qstrForm;
		$this->setRedirectHeader ( $redirectUrl );
		
		foreach ( $this->positionsHtml as $key => $value ) {
			if (! empty ( $key )) {
				$this->assign ( $key, $value );
				$POSITION [$key] = $value;
			} else {
				$this->assign ( 'default', $value );
				$POSITION ['default'] = $value;
			}
		}
		
		$this->assign ( 'POSITION', $POSITION );
		// var_dump($POSITION);
		
		$this->loadPlugin ( 'smarty_block_translate' );
		$this->registerPlugin ( 'block', 'translate', 'smarty_block_translate' );
		$this->assign ( "userMessages", $this->userMessages );
		$this->assign ( "appTemplate", "App_" . $this->appTemplate . ".tpl" );
		
		$modal = isset ( $_GET ['modal'] ) ? ( string ) $_GET ['modal'] : 'false';
		$this->assign ( '_MODAL', $modal );
		
		$this->assign ( 'menuEntries', $this->menuItems );
		
		// assign versions variables
		$this->assign ( '_frameworkVersion', $KUINK_CFG->frameworkVersion );
		$this->assign ( '_appsVersion', $KUINK_CFG->appsVersion );
		
		if ($modal != 'false')
			$this->display ( 'Modal_' . $modal . '.tpl' );
		else
			$this->display ( "Master.tpl" );
		
		// $this->assign("parts",$html);
		// $this->display('html.tpl');
	}
	public function getString($params, $content, $smarty, &$repeat, $template) {
		$appName = $params ['application'];
		$identifier = $content;
		return \Kuink\Core\Language::getString ( $identifier );
	}
	public function addHtml($html, $position) {
		$this->positionsHtml [$position] [] = $html;
	}
	
	/**
	 * Set or unset cache
	 * 
	 * @param boolean $cache        	
	 */
	public function setCache($cache) {
		if (is_bool ( $cache ))
			$this->caching = $cache;
		else
			$this->caching = true;
	}
	public function addUserMessages($messages) {
		$this->userMessages = $messages;
	}
	public function setBaseUrl($baseurl) {
		$this->assign ( "baseurl", $baseurl );
	}
	public function setLogOut($userDisplayName, $userId, $sessKey) {
		$this->assign ( "userDisplayName", $userDisplayName );
		$this->assign ( "userId", $userId );
		$this->assign ( "sessKey", $sessKey );
	}
	public function setAppMenu($appMenuEntries) {
		if (isset($appMenuEntries) && is_array($appMenuEntries))
			foreach ( $appMenuEntries as $item ) {
				$this->menuItems [] = $item;
			}
		// $this->assign("appMenuEntries", $appMenuEntries);
	}
	public function setNodeMenu($nodeMenuEntries) {
		$this->menuItems [] = $nodeMenuEntries;
		
		// $this->assign("nodeMenuEntries", $nodeMenuEntries);
	}
	public function setAppName($appName) {
		$this->assign ( "appName", $appName );
	}
	public function setProcessName($processName) {
		$this->assign ( "processName", $processName );
	}
	public function setNodeName($nodeName) {
		$this->assign ( "nodeName", $nodeName );
	}
	public function setAdminMenu($menuEntries) {
		$this->assign ( "hasAdminMenu", true );
		$this->assign ( "adminMenuEntries", $menuEntries );
	}
	public function setBreadCrumb($breadcrumbEntries) {
		$this->assign ( "breadcrumbEntries", $breadcrumbEntries );
	}
	public function setRefresh($actionUrl){
		$this->assign("_refresh", $actionUrl);
	}    
	public function setGlobalVariable($name, $value) {
		$this->assign ( $name, $value );
	}
	public function setScreenSource($screenSource) {
		$this->assign ( '_showSource', true );
		$this->assign ( '_screenSource', $screenSource );
	}
	public function setActionsSource($actionsSource) {
		$this->assign ( '_actionsSource', $actionsSource );
	}
	static function getTemplate($templateName, $data) {
		$smarty = new \Smarty ();
		$smarty->setTemplateDir ( dirname ( __FILE__ ) . '/../../../theme/adminlte/template/' );
		$smarty->setCompileDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache_compiled/' );
		$smarty->setCacheDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache/' );
		$smarty->assign ( $data );
		
		$result = $smarty->fetch ( $templateName . '.tpl' ); 
		//print_object(dirname ( __FILE__ ) . '/../../../theme/default/template/'.$templateName );
		//print_object($result);
		return $result;
	}
	static function getApplicationTemplate($application, $process, $templateName, $data) {
		global $KUINK_CFG, $KUINK_APPLICATION;
		
		$appBase = isset ( $KUINK_APPLICATION ) ? $KUINK_APPLICATION->appManager->getApplicationBase ( $application ) : '';
		
		$smarty = new \Smarty ();

		$templateDir = $KUINK_CFG->appRoot . '/apps/' . $appBase . '/' . $application . '/process/' . $process . '/templates/';
		$smarty->setTemplateDir ( $templateDir );
		// print($templateDir);
		$smarty->setCompileDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache_compiled/' );
		$smarty->setCacheDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache/' );
		$smarty->assign ( $data );
		return $smarty->fetch ( $templateName . '.tpl' );
	}
	static function expandTemplate($templateCode, $data) {
		global $KUINK_CFG;
		$smarty = new \Smarty ();
		$templateDir = $KUINK_CFG->appRoot . 'files/temp/';
		
		// Create template file
		$filename = time () . '-' . rand ( 1000, 10000 ) . '.tpl';
		$file = $templateDir . $filename;
		
		$handle = fopen ( $file, 'w' );
		
		fwrite ( $handle, $templateCode );
		fclose ( $handle );
		
		$smarty->setTemplateDir ( $templateDir );
		// print($templateDir);
		$smarty->setCompileDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache_compiled/' );
		$smarty->setCacheDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache/' );
		$smarty->assign ( $data );
		$returnData = $smarty->fetch ( $file );
		// delete the file
		@unlink ( $file );
		return $returnData;
	}
}

?>
