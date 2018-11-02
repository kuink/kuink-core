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
namespace Kuink\UI\Layout;

/**
 * Main class for Kuink output rendering
 * 
 * @author ptavares
 *        
 */
use \Kuink\Core\Exception\InvalidParameters, \Kuink\Core\Exception\ClassNotFound, \Kuink\Core\Factory, \Kuink\UI\Layout\LayoutTemplate, \Kuink\UI\Layout\ILayoutElement;
use Kuink\Core\ProcessOrchestrator;

class Layout {
	
	/**
	 * Layout instance for singleton pattern
	 * 
	 * @var Kuink\UI\Layout\Layout Layout instance
	 */
	private static $instance;
	
	/**
	 * Layout adapter
	 * 
	 * @var \Kuink\UI\Layout\Adapter
	 */
	private $layoutAdapter;
	
	/**
	 *
	 * @var array Array with html $layout_html['left'] = "<h1>html text</h1>"
	 */
	private $layoutHtml;
	private $theme; // theme folder name
	public function __construct() {
		$this->layoutAdapter = Factory::getLayoutAdapter ( "Smarty" );
	}
	public function setTheme($theme) {
		$this->theme = $theme;
		$this->layoutAdapter->setTheme($theme);		
	}
	public function getTheme(){
		return $this->layoutAdapter->getTheme();
	}
	public function setAppTemplate($appTemplate) {
		$this->layoutAdapter->setAppTemplate ( $appTemplate );
	}
	public function setBaseUrl($baseurl) {
		$this->layoutAdapter->setBaseUrl ( $baseurl );
	}
	public function setLogOut($userDisplayName, $userId, $sessKey) {
		$this->layoutAdapter->setLogOut ( $userDisplayName, $userId, $sessKey );
	}
	public function setRedirectHeader($url) {
		$this->layoutAdapter->setRedirectHeader ( $url );
	}
	
	/**
	 * Get current layout html array
	 * 
	 * @return array Current template html
	 */
	public function getHtml() {
		return $this->layoutHtml;
	}
	
	/**
	 * Get current \Kuink\UI\Layout\Layout instance
	 */
	public static function getInstance() {
		if (! self::$instance)
			self::$instance = new \Kuink\UI\Layout\Layout ();
		return self::$instance;
	}
	
	/**
	 * Set or layout cache
	 * 
	 * @param boolean $cache        	
	 */
	public function setCache($cache = true) {
		$this->layoutAdapter->setCache ( $cache );
	}
	public function setScreenSource($screenSource) {
		$this->layoutAdapter->setScreenSource ( $screenSource );
	}
	public function setActionsSource($actionsSource) {
		$this->layoutAdapter->setActionsSource ( $actionsSource );
	}
	
	/**
	 * Render the html
	 */
	public function render() {
		$this->layoutAdapter->render ( $this->layoutHtml );
	}
	public function setGlobalVariable($name, $value) {
		$this->layoutAdapter->setGlobalVariable ( $name, $value );
		return;
	}
	public function addHtml($output, $position) {
		$this->layoutAdapter->addHtml ( $output, $position );
	}

	public function addControl($type, $params, $skeleton = null, $skin, $position) {
		$this->layoutAdapter->addControl($type, $params, $skeleton = null, $skin, $position);
	}

	public function addUserMessages($messages) {
		$this->layoutAdapter->addUserMessages ( $messages );
	}
	public function setAppMenu($menuEntries) {
		$this->layoutAdapter->setAppMenu ( $menuEntries );
	}
	public function setNodeMenu($menuEntries) {
		$this->layoutAdapter->setNodeMenu ( $menuEntries );
	}
	public function setAppName($appName) {
		$this->layoutAdapter->setAppName ( $appName );
	}
	public function setProcessName($processName) {
		$this->layoutAdapter->setProcessName ( $processName );
	}
	public function setNodeName($nodeName) {
		$this->layoutAdapter->setNodeName ( $nodeName );
	}
	public function setAdminMenu($menuEntries) {
		$this->layoutAdapter->setAdminMenu ( $menuEntries );
	}
	public function setBreadCrumb($breadcrumbEntries) {
		$this->layoutAdapter->setBreadCrumb ( $breadcrumbEntries );
	}
	public function setRefresh($actionUrl){
		$this->layoutAdapter->setRefresh($actionUrl);
	}
}

?>