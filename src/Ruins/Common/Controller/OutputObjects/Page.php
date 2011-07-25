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
class Page implements OutputObjectInterface
{
    /**
     * @var UserInterface
     */
    protected $user=null;

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

    /**
     * @var array
     */
    protected $toolBoxItems = array();

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
     * Adds a JavaScript-file Template
     * WARNING: JavaScript Templates use {{ and }} as delimiters!
     * @param string $script Script Template Filename
     */
    public function addJavaScriptTemplate($script)
    {
        $path = SystemManager::getWebRessourcePath('javascript/'.$script);

        $jstpl = $this->getTemplateEngine()->createTemplate($path);
        $jstpl->left_delimiter = "{{";
        $jstpl->right_delimiter = "}}";
        $this->addJavaScript($jstpl->fetch());
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
     * Add a tool to the ToolBox
     * @param string $url Url
     * @param string $description Description for this Tool
     * @param string $imagesrc Imagesrc before a click
     * @param string $replaceimagesrc Imagesrc after a click (optional)
     */
    public function addToolBoxItem($name, $url, $description, $imagesrc, $replaceimagesrc=false)
    {
        $boxItem = array();

        $boxItem['name']            = $name;
        $boxItem['url']             = $url;
        $boxItem['description']     = $description;
        $boxItem['imagesrc']        = $imagesrc;
        if ($replaceimagesrc) {
            $boxItem['replaceimagesrc']    = $replaceimagesrc;
        } else {
            $boxItem['replaceimagesrc']    = $boxItem['imagesrc'];
        }

        $this->toolBoxItems[] = $boxItem;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::setTitle()
     */
    public function setTitle($value)
    {
        $this->assign("pagetitle", $value);
        $this->assign("headtitle", $value);
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::isPrivate()
     */
    public function isPrivate()
    {
        if (isset($this->user)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::setPrivate()
     */
    public function setPrivate(UserInterface $user)
    {
        $this->user = $user;
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
        if ($this->isPrivate()) {
            if ($this->getTemplateEngine()->isCached($template, $this->user->character->id)) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::getLatestGenerated()
     */
    public function getLatestGenerated($template)
    {
        if ($this->isPrivate()) {
            if ($this->getTemplateEngine()->isCached($template, $this->user->character->id)) {
                return $this->getTemplateEngine()->fetch($template, $this->user->character->id);
            } else {
                return false;
            }
        }

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
        if ($this->isPrivate()) {
            $this->getTemplateEngine()->clearCache($template, $this->user->getCharacter()->id);
        } else {
            $this->getTemplateEngine()->clearCache($template);
        }
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
     * @see Ruins\Common\Interfaces.OutputObjectInterface::show()
     */
    public function show($template)
    {
        ModuleManager::callModule(ModuleManager::EVENT_PRE_PAGEGENERATION, $this);

        // Generate Navigation
        $this->generateNavigation();
        // Generate ToolBox
        $this->generateToolBox();
        // Generate Character Stats (if private)
        $this->generateStats();
        // Generate Characters Near List (if private)
        $this->generateCharactersNear();
        // Generate Character List (if not private)
        $this->generateCharacterList();

        // Page generation Time
        $this->assign("pagegen", round(microtime(true) - $this->pagegenerationstarttime,3) * 1000);

        ModuleManager::callModule(ModuleManager::EVENT_POST_PAGEGENERATION, $this);

        if ($this->isPrivate()) {
            $this->clearCache($template);
            $this->getTemplateEngine()->display($template, $this->getUser()->getCharacter()->id);
        } else {
            $this->getTemplateEngine()->display($template);
        }
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::showLatestGenerated()
     */
    public function showLatestGenerated($template)
    {
        if ($this->isPrivate()) {
            if ($this->getTemplateEngine()->isCached($template, $this->getUser()->getCharacter()->id)) {
                $this->getTemplateEngine()->display($template, $this->getUser()->getCharacter()->id);
                return true;
            }
        }

        return false;
    }

    /**
     * Get User
     * @return Ruins\Common\Interfaces\UserInterface
     */
    protected function getUser()
    {
        return $this->user;
    }

    /**
     * Generate Navigation
     */
    protected function generateNavigation()
    {
        foreach ($this->getNavigation()->getLinkList() as $linklist) {

            if ($linklist['displayname']) {

                if ($linklist['position'] == "main") {
                    $this->getTemplateEngine()->append('navMain',
                                                       array('url' => $linklist['url'],
                                                             'title' => htmlspecialchars($linklist['description'], ENT_QUOTES),
                                                             'display' => BtCode::decode($linklist['displayname']),
                                                             'type' => $linklist['type'],
                                                      ));
                } elseif ($linklist['position'] == "shared") {
                    $this->getTemplateEngine()->append('navShared',
                                                       array('url' => $linklist['url'],
                                                             'title' => htmlspecialchars($linklist['description'], ENT_QUOTES),
                                                             'display' => BtCode::decode($linklist['displayname']),
                                                             'type' => $linklist['type'],
                                                      ));
                }
            }
        }
    }

    /**
     * Generate the ToolBox (small tools)
     */
    protected function generateToolBox()
    {
        $this->assign("toolBox", $this->toolBoxItems);
    }

    /**
     * Generate Stats
     */
    protected function generateStats()
    {
        if (!$this->isPrivate()) {
            return false;
        }
        $this->assign("user", $this->getUser());
        $this->assign("weapon", ItemManager::getEquippedItem($this->getUser()->getCharacter(), "weapon"));
        $this->assign("money", $this->getUser()->getCharacter()->money);
    }

    /**
     * Generate List of Characters near the active one
     */
    protected function generateCharactersNear()
    {
        if (!$this->isPrivate()) {
            return false;
        }

        $characterlist = Registry::getEntityManager()->getRepository("Main:Character")
                                                     ->getListAtPlace($this->getUrl()->getBase());

        foreach ($characterlist as &$charactername) {
            $charactername = BtCode::decode($charactername);
        }

        $this->assign("charactersNear", $characterlist);
    }

    /**
     * Generate Character list for the Frontpage
     */
    protected function generateCharacterList()
    {
        if ($this->isPrivate()) {
            return false;
        }

        $characterlist = Registry::getEntityManager()->getRepository("Main:Character")
                                                     ->getList(array('displayname'), 'id', 'ASC', true);

        $this->assign("charactersOnline", $characterlist);
    }
}