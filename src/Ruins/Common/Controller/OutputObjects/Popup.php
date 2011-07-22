<?php
/**
 * Standard Page Object
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Controller\OutputObjects;
use Smarty;
use Ruins\Common\Controller\BtCode;
use Ruins\Common\Controller\Navigation;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\Request;
use Ruins\Common\Controller\Url;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Common\Interfaces\UserInterface;
use Ruins\Common\Interfaces\NavigationInterface;
use Ruins\Common\Manager\HtmlElementManager;
use Ruins\Main\Manager\ItemManager;
use Ruins\Main\Manager\SystemManager;
use Ruins\Main\Manager\ModuleManager;

/**
 * Class Name
 * @package Ruins
 */
class Popup implements OutputObjectInterface
{
    /**
     * @var Url
     */
    protected $url;

    /**
     * @var \Smarty
     */
    protected $templateEngine;

    /**
     * @var Ruins\Common\Interfaces\NavigationInterface
     */
    protected $navigation=null;

    /**
     * @var int
     */
    protected $pagegenerationstarttime;

    public function __construct()
    {
        // Set microtime to meassure the page-generation time
        $this->pagegenerationstarttime = microtime(true);
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::create()
     */
    public function create(Request $request)
    {
        $this->url = new Url($request);
        $this->navigation = new Navigation($request);

        $this->templateEngine  = Registry::get('smarty');

        $this->getTemplateEngine()->caching = 0;

        $baseTemplateDir       = $this->getTemplateEngine()->getTemplateDir("default");
        $pageObjectTemplateDir = $baseTemplateDir . "/" . $request->getRoute()->getCallerName();

        $this->getTemplateEngine()->addTemplateDir($pageObjectTemplateDir, "mytemplatedir");

        $this->getTemplateEngine()->assign("basetemplatedir", SystemManager::htmlpath($baseTemplateDir));
        $this->getTemplateEngine()->assign("mytemplatedir", SystemManager::htmlpath($pageObjectTemplateDir));

        $this->addCSS("btcode.css");
        $this->addJavaScriptFile("jquery-1.5.1.min.js");
        $this->addJavaScriptFile("jquery-ui-1.8.13.custom.min.js");
        $this->addJavaScriptFile("jquery.plugin.timers.js");
        $this->addJavaScriptFile("timer.func.js");
        $this->addJavaScriptFile("global.func.js");
        $this->addJavaScriptFile("popup.func.js");
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::assign()
     */
    public function assign($placeholder, $value)
    {
        $this->getTemplateEngine()->assign($placeholder, $value);
    }

    /**
     * Adds a JavaScript-Section to the Header
     * @param string $script JavaScript
     */
    public function addJavaScript($script)
    {
        $this->output("<script type='text/javascript'><!-- \n" . $script . "\n --></script>", true);
    }

    /**
     * Adds a JavaScript-file as an include to the Header
     * @param string $script Script Filename
     */
    public function addJavaScriptFile($script)
    {
        $this->getTemplateEngine()->append(
            'jsHeadIncludes',
            SystemManager::getWebRessourcePath('javascript/'.$script, true)
        );
    }

    /**
     * Adds a CSS-file as an include to the Header
     * @param string $script Script Filename (has to be inside of templates/common/styles)
     */
    public function addCSS($script)
    {
        $this->getTemplateEngine()->append(
            'cssHeadIncludes',
            SystemManager::getWebRessourcePath('styles/'.$script, true)
        );
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::setTitle()
     */
    public function setTitle($value)
    {
        $this->assign("headtitle", $value);
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::isPrivate()
     */
    public function isPrivate()
    {
        return false;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::setPrivate()
     */
    public function setPrivate(UserInterface $user)
    {
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::getUrl()
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * (non-PHPdoc)
     * @see Ruins\Common\Interfaces.OutputObjectInterface::setNavigation()
     */
    public function setNavigation(NavigationInterface $navigation)
    {
        $this->navigation = $navigation;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::getNavigation()
     */
    public function getNavigation($container="main")
    {
        $this->navigation->setContainerName($container);

        return $this->navigation;
    }

    /**
     * Return Template Engine (smarty)
     * @return \Smarty
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::output()
     */
    public function output($text, $showhtml=false)
    {
        if (!$showhtml) $text=htmlspecialchars($text, ENT_QUOTES, "UTF-8");

        $this->getTemplateEngine()->append(
            'main',
            BtCode::decode($text)
        );
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::cacheExists()
     */
    public function cacheExists($template)
    {
        return true;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::getLatestGenerated()
     */
    public function getLatestGenerated($template)
    {
        return "";
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::disableCaching()
     */
    public function disableCaching()
    {
        $this->getTemplateEngine()->caching = 0;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::clearCache()
     */
    public function clearCache($template)
    {
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::addForm()
     */
    public function addForm($name, $overwrite=false)
    {
        return HtmlElementManager::addForm($name, $this, $overwrite);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::getForm()
     */
    public function getForm($name)
    {
        return HtmlElementManager::getForm($name);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::closeForm()
     */
    public function closeForm($name)
    {
        return HtmlElementManager::closeForm($name);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::addTable()
     */
    public function addTable($name, $overwrite=false)
    {
        return HtmlElementManager::addTable($name, $this, $overwrite);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::getTable()
     */
    public function getTable($name)
    {
        return HtmlElementManager::getTable($name);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::closeTable()
     */
    public function closeTable($name)
    {
        return HtmlElementManager::closeTable($name);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::addChat()
     */
    public function addChat($name)
    {
        return HtmlElementManager::addChat($name, $this);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::getChat()
     */
    public function getChat($name)
    {
        return HtmlElementManager::getChat($name);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::closeChat()
     */
    public function closeChat($name)
    {
        return HtmlElementManager::closeChat($name);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::addSimpleTable()
     */
    public function addSimpleTable($name, $overwrite=false)
    {
        return HtmlElementManager::addSimpleTable($name, $this, $overwrite);
    }

    /**
     * @see Ruins\Common\Manager.HtmlElementManager::getSimpleTable()
     */
    public function getSimpleTable($name)
    {
        return HtmlElementManager::getSimpleTable($name);
    }

    /**
     * @see \Ruins\Common\Manager.HtmlElementManager::closeSimpleTable()
     */
    public function closeSimpleTable($name)
    {
        return HtmlElementManager::closeSimpleTable($name);
    }

    /**
     * Redirect the main Window to another Location
     * Warning: No Nav-checking!
     * @param string $location The new Location
     */
    public function redirectParent($location)
    {
        //TODO:Navchecking?
        $this->addJavascript("opener.window.location.href = '?" . $location . "'");
    }

    /**
     * Close the Popup via Javascript
     */
    public function close()
    {
        $this->addJavaScript("self.close()");
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::show()
     */
    public function show($template)
    {
        // Generate Navigation
        $this->generateNavigation();

        // Page generation Time
        $this->assign("pagegen", round(microtime(true) - $this->pagegenerationstarttime,3) * 1000);

        // Display Page
        $this->getTemplateEngine()->display($template);
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::showLatestGenerated()
     */
    public function showLatestGenerated($template)
    {
        $this->show($template);

        return true;
    }

    /**
     * Generate Navigation
     */
    protected function generateNavigation()
    {
        foreach ($this->getNavigation()->getLinkList() as $linklist) {

            if ($linklist['displayname']) {

                $this->getTemplateEngine()->append('navMain',
                                                   array('url' => $linklist['url'],
                                                         'title' => htmlspecialchars($linklist['description'], ENT_QUOTES),
                                                         'display' => BtCode::decode($linklist['displayname']),
                                                         'type' => $linklist['type'],
                                                  ));
            }
        }
    }
}