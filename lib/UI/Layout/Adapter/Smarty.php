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

use Kuink\Core\Configuration;

class Smarty extends \Smarty
{
    private $themeName;
    private $appTemplate;
    private $userMessages = array();
    private $positionsHtml = array();
    private $menuItems = array();

    /**
     * @var Configuration
     */
    private $configuration;

    function __construct($themeName = "default")
    {
        parent::__construct();

        //$this->setTemplateDir ( dirname ( __FILE__ ) . '/../../../theme/' . $themeName . '/template/' );
        //$this->setCompileDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache_compiled/' );
        //$this->setCacheDir ( dirname ( __FILE__ ) . '/../../../theme/theme_cache/' );

        $this->configuration = Configuration::getInstance();



        $this->themeName = $themeName;
        $this->setThemeDirectories();

        $this->appTemplate = "1col";
        $this->assign('THEME', $themeName);
        $context = \Kuink\Core\ProcessOrchestrator::getContext();
        $this->assign('_idContext', ($context == null) ? null : $context->id);
        $this->assign('_apiUrl', '/api.php?neonfunction=');
        $this->assign('_kuinkRoot', $this->configuration->get('kuinkRoot'));
        $this->assign('_themeRoot', $this->configuration->get('themeRoot'));
        $this->assign('_streamUrl', $this->configuration->get('streamUrl'));
        $this->assign('_apiCompleteUrl', Configuration::getInstance()->web->www_root.'/api.php?neonfunction=');
        $this->assign('_imageUrl', $this->configuration->get('imageRemote'));
        $this->assign('_photoUrl', $this->configuration->get('photoRemote'));
        $this->assign('_environment', $this->configuration->get('environment'));
        $this->assign('_lang', $this->configuration->defaults->user->lang);
        $userEmail = '';
//        if (isset($this->configuration->get('auth')->user->email) && ($this->configuration->get('auth')->user->email != 'root@localhost'))
//            $userEmail = $this->configuration->get('auth')->user->email;
        $this->assign('_userEmail', $userEmail);
        //Get rid of unnecessary reporting
        $this->error_reporting = E_ERROR;//E_ALL & ~E_NOTICE;
        $this->muteExpectedErrors();
        //Do not use this in production
        $this->force_compile = true;
    }

    public function setTheme($themeName)
    {
        $this->themeName = $themeName;

        $this->setThemeDirectories();

        $this->assign('THEME', $themeName);
    }

    public function getTheme()
    {
        return ($this->themeName);
    }

    public function setAppTemplate($appTemplate)
    {
        $this->appTemplate = $appTemplate;
    }

    public function setRedirectHeader($url)
    {
        global $KUINK_CFG;

        if (Configuration::getInstance()->kuink->post_redirect_get && $_GET ['nodeguid'] == '' && $_SERVER ['REQUEST_METHOD'] == 'POST') {
            header("HTTP/1.1 303 See Other");
            header("Location: $url");
        }
    }

    /**
     * Render html to layout
     *
     * @param array $html
     *            Html parts
     * @throws \SmartyException
     */
    public function render($html)
    {
        $POSITION = array();
        // Set post redirect pattern to prevent double-click and F5
        // var_dump($_GET);
        // var_dump($_SERVER);

        $currentNode = \Kuink\Core\ProcessOrchestrator::getCurrentNode();
        $qstrForm = isset($_GET ['form']) ? $_GET ['form'] : '';
        // var_dump($currentNode);
        $redirectUrl = $currentNode->url . '&action=' . $currentNode->action . '&actionvalue=' . $currentNode->actionValue . '&form=' . $qstrForm;
        $this->setRedirectHeader($redirectUrl);

        foreach ($this->positionsHtml as $key => $value) {
            if (!empty ($key)) {
                $this->assign($key, $value);
                $POSITION [$key] = $value;
            } else {
                $this->assign('default', $value);
                $POSITION ['default'] = $value;
            }
        }

        $this->assign('POSITION', $POSITION);
        // var_dump($POSITION);

        $this->loadPlugin('smarty_block_translate');
        $this->registerPlugin('block', 'translate', 'smarty_block_translate');
        $this->assign("userMessages", $this->userMessages);
        $this->assign("appTemplate", "App_" . $this->appTemplate . ".tpl");

        $modal = isset ($_GET ['modal']) ? ( string )$_GET ['modal'] : 'false';
        $this->assign('_MODAL', $modal);

        $this->assign('menuEntries', $this->menuItems);

        // assign versions variables
        $this->assign('_frameworkVersion', $this->configuration->get('frameworkVersion'));
        $this->assign('_appsVersion', $this->configuration->get('appsVersion'));

        if ($modal != 'false')
            $this->display('Modal_' . $modal . '.tpl');
        else
            $this->display("Master.tpl");

        // $this->assign("parts",$html);
        // $this->display('html.tpl');
    }

    public function getString($params, $content, $smarty, &$repeat, $template)
    {
        $appName = $params ['application'];
        $identifier = $content;
        return \Kuink\Core\Language::getString($identifier);
    }

    public function addHtml($html, $position)
    {
        $this->positionsHtml [$position] [] = $html;
    }

    /**
     * Set or unset cache
     *
     * @param boolean $cache
     */
    public function setCache($cache)
    {
        if (is_bool($cache))
            $this->caching = $cache;
        else
            $this->caching = true;
    }

    public function addUserMessages($messages)
    {
        $this->userMessages = $messages;
    }

    public function setBaseUrl($baseurl)
    {
        $this->assign("baseurl", $baseurl);
    }

    public function setUserInfo(
        string $firstName,
        string $lastName,
        string $id,
        string $sessionKey = null,
        bool $isGuest = false
    ) { 
        $this->assign("userDisplayName", $firstName . ' ' . $lastName);
        $this->assign("userId", $id);
        $this->assign("sessKey", $sessionKey);
        $this->assign("isGuest", $isGuest);
    }


    public function setLogOut($userDisplayName, $userId, $sessKey)
    {
        $this->assign("userDisplayName", $userDisplayName);
        $this->assign("userId", $userId);
        $this->assign("sessKey", $sessKey);
    }

    public function setAppMenu($appMenuEntries)
    {
        if (isset($appMenuEntries) && is_array($appMenuEntries))
            foreach ($appMenuEntries as $item) {
                $this->menuItems [] = $item;
            }
        // $this->assign("appMenuEntries", $appMenuEntries);
    }

    public function setNodeMenu($nodeMenuEntries)
    {
        $this->menuItems [] = $nodeMenuEntries;

        // $this->assign("nodeMenuEntries", $nodeMenuEntries);
    }

    public function setAppName($appName)
    {
        $this->assign("appName", $appName);
    }

    public function setProcessName($processName)
    {
        $this->assign("processName", $processName);
    }

    public function setNodeName($nodeName)
    {
        $this->assign("nodeName", $nodeName);
    }

    public function setAdminMenu($menuEntries)
    {
        $this->assign("hasAdminMenu", true);
        $this->assign("adminMenuEntries", $menuEntries);
    }

    public function setBreadCrumb($breadcrumbEntries)
    {
        $this->assign("breadcrumbEntries", $breadcrumbEntries);
    }

    public function setRefresh($actionUrl)
    {
        $this->assign("_refresh", $actionUrl);
    }

    public function setGlobalVariable($name, $value)
    {
        $this->assign($name, $value);
    }

    public function setScreenSource($screenSource)
    {
        $this->assign('_showSource', true);
        $this->assign('_screenSource', $screenSource);
    }

    public function setActionsSource($actionsSource)
    {
        $this->assign('_actionsSource', $actionsSource);
    }

    static function getTemplate($templateName, $data, $themeName = '')
    {
        $smarty = new \Smarty ();
        $configuration = Configuration::getInstance();

        self::setSmartyDirectories($smarty, $themeName);

        $smarty->assign($data);
        $result = $smarty->fetch($templateName . '.tpl');
        return $result;
    }

    public function getApplicationTemplate($application, $process, $templateName, $data)
    {
        global $KUINK_APPLICATION;

        $appBase = isset ($KUINK_APPLICATION) ? $KUINK_APPLICATION->appManager->getApplicationBase($application) : '';

        $smarty = new \Smarty ();

        $this->setThemeDirectories();
        $smarty->assign($data);

        $result = $smarty->fetch($templateName . '.tpl');

        return $result;
    }

    public function expandTemplate($templateCode, $data)
    {
        $smarty = new \Smarty ();
        $templateDir = $this->configuration->get('appRoot') . 'files/temp/';

        // Create template file
        $filename = time() . '-' . rand(1000, 10000) . '.tpl';
        $file = $templateDir . $filename;

        $handle = fopen($file, 'w');

        fwrite($handle, $templateCode);
        fclose($handle);

        $this->setThemeDirectories();
        $smarty->assign($data);
        $returnData = $smarty->fetch($file);
        // delete the file
        @unlink($file);
        return $returnData;
    }

    public function addControl($type, $params, $skeleton = null, $skin, $position)
    {
        $smarty = \Kuink\Core\Factory::getLayoutAdapter("Smarty");

        $smarty_params = array();
        $smarty_params ['skin'] = $skin;

        foreach ($params as $key => $value)
            $smarty_params [$key] = $value;

        $smarty->assign($smarty_params);

        $template_name = ($params['_skeleton'] == '') ? $type . '.tpl' : $type . '_' . $params['_skeleton'] . '.tpl';

        $output = $smarty->fetch(Configuration::getInstance()->paths->theme . '/ui/control/' . $template_name);

        $this->addHtml($output, $position);
    }

    protected function setThemeDirectories(): void
    {
        self::setSmartyDirectories($this, $this->themeName);
    }

    /**
     * Set directories on a smarty object
     * @param \Smarty $smartyObj
     * @param string $themeName
     */
    protected static function setSmartyDirectories(\Smarty &$smartyObj, string $themeName) {
        $configuration = Configuration::getInstance();
        $smartyObj->setTemplateDir($configuration->paths->theme . '/template/');
        $smartyObj->setCompileDir($configuration->paths->theme . '/theme_cache_compiled/');
        $smartyObj->setCacheDir($configuration->paths->theme . '/theme_cache/');
    }

}
