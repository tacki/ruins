<?php
/**
 * Page Class
 *
 * Page-Class, the Heart of the Template-System
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Main\Controller;
use Smarty;
use Ruins\Main\Manager\SystemManager;
use Ruins\Main\Manager\ItemManager;
use Ruins\Main\Manager\ModuleManager;
use Ruins\Main\Controller\Nav;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Common\Controller\BaseObject;
use Ruins\Common\Controller\BtCode;
use Ruins\Common\Controller\Error;
use Ruins\Common\Controller\Form;
use Ruins\Common\Controller\Table;
use Ruins\Common\Controller\SimpleTable;
use Ruins\Common\Controller\Registry;

/**
 * Page Class
 *
 * Page-Class, the Heart of the Template-System
 * @package Ruins
 */
class Page implements OutputObjectInterface
{
    /**
    * Class constants
    */
    const DEFAULT_PUBLIC_TEMPLATE    = "main/templates/default";
    const DEFAULT_PRIVATE_TEMPLATE   = "main/templates/default";

    /**
     * Navigation Class
     * @var nav
     */
    public $nav;

    /**
     * Page URL
     * @var Ruins\Main\Controller\URL
     */
    public $url;

    /**
     * Shortname of this Page
     * (example: common/login)
     * @var string
     */
    public $shortname;

    /**
     * Smarty Template Information
     * @var array
     */
    public $template;

    /**
     * Enable Modules
     * @var bool
     */
    public $modulesenabled;

    /**
     * Smarty Template Class
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Character Object
     * @var Character
     */
    protected $_char;

    /**
     * Scripts which are placed inside <head></head>
     * @var array
     */
    protected $_headscripts;

    /**
     * Main-Content of the Page
     * @var array
     */
    protected $_bodycontent;

    /**
     * Page Elements (chat, table, forms, ...)
     * @var array
     */
    protected $_elements;

    /**
     * Outputmodules
     * @var array
     */
    protected $_outputmodule;

    /**
     * Toolbox Items
     * @var array
     */
    protected $_toolBoxItems;

    /**
     * Unix Timestamp at the start of the Page
     * @var float
     */
    protected $_pagegenerationstarttime;

    /**
     * Created Flag
     * @var bool
     */
    protected $_isCreated;

    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param Character $char Character Object we build this page for
     */
    function __construct($char=false)
    {
        $smarty = Registry::get('smarty');

        // Set microtime to meassure the page-generation time
        $this->_pagegenerationstarttime = microtime(true);

        // Initialize Properties
        $this->template 		= array();
        $this->_char 			= $char;
        $this->_isCreated		= false;
        $this->_headscripts 	= array();
        $this->_bodycontent 	= array();
        $this->_toolBoxItems	= array();
        $this->_elements        = array();
        $this->modulesenabled	= true;

        // Initialize Navigation
        $this->nav = new Nav($char, $this);
        $this->nav->load();
        $this->nav->cacheNavigation = true;

        // Set own URL
        $this->url = new URL($this->nav->getRequestURL());
        $this->shortname = $this->url->getParameter("page");

        // Initialize the Smarty-Class
        $this->_smarty = $smarty;
    }

    /**
     * Create a new/blank Page
     */
    public function create()
    {
        // Prepare Template Information
        $this->_loadTemplateInformation();

        // Check the Navigation for correct Page-calling
        $this->_checkNav();

        // Set created-flag
        $this->_isCreated = true;
    }

    /**
     * Refresh/Reload the Page
     * @param bool $base Refresh the URL-Base instead of the current URL
     */
    public function refresh($base=false)
    {
        if ($base) {
            $this->nav->redirect($this->url->base);
        } else {
            $this->nav->redirect($this->url);
        }
    }

    /**
     * Replaces placeholders inside the main Template
     * @param string $key Name of the {$...}-placeholder
     * @param string $value Value the placeholder will be replaced with
     */
    public function set($placeholder, $value)
    {
        $this->_smarty->assign($placeholder, $value);
    }

    public function setTitle($value)
    {
        $this->set("pagetitle", $value);
        $this->set("headtitle", $value);
    }

    /**
     * Make this Page a public one
     */
    public function setPublic()
    {
        if ($this->isCreated()) {
            throw new Error("Can't set Page to Public - Page is already created!");
        }

        $this->_char = false;
    }

    /**
     * Adds a JavaScript-Section to the Header
     * @param string $script JavaScript
     */
    public function addJavaScript($script)
    {
        array_push($this->_headscripts, "<script type='text/javascript'><!-- \n" . $script . "\n --></script>");
    }

    /**
     * Adds a JavaScript-file as an include to the Header
     * @param string $script Script Filename
     */
    public function addJavaScriptFile($script)
    {
        $this->_headscripts[] = "<script src='".SystemManager::getWebRessourcePath("javascript/".$script, true)."' type='text/javascript'></script>";
    }

    /**
    * Adds a CSS-file as an include to the Header
    * @param string $script Script Filename (has to be inside of templates/common/styles)
    */
    public function addCSS($script)
    {
        $this->_headscripts[] = "<link href='".SystemManager::getWebRessourcePath("styles/".$script, true)."' rel='stylesheet' type='text/css' />";
    }

    /**
     * Adds a CSS-file as an include to the Header
     * @param string $script Script Filename (has to be inside of templates/common/styles)
     */
    public function addCommonCSS($script)
    {
        $this->_headscripts[] = "<link href='". SystemManager::getWebRessourcePath("common/styles/".$script, true)."' rel='stylesheet' type='text/css' />";
    }

    /**
     * Adds a CSS-file as an include to the Header
     * @param string $script Script Filename (has to be inside of templates/<templatename>/)
     */
    public function addTemplateCSS($script)
    {
        $this->_headscripts[] = "<link href='". $this->template['name'] ."/".$script."' rel='stylesheet' type='text/css' />";
    }

    /**
     * Add a tool to the ToolBox
     * @param Link $link Link-Object
     * @param string $description Description for this Tool
     * @param string $imagesrc Imagesrc before a click
     * @param string $replaceimagesrc Imagesrc after a click (optional)
     */
    public function addToolBoxItem(Link $link, $description, $imagesrc, $replaceimagesrc=false)
    {
        $boxItem = array();

        $boxItem['link'] 			= $link;
        $boxItem['description']		= $description;
        $boxItem['imagesrc']		= $imagesrc;
        if ($replaceimagesrc) {
            $boxItem['replaceimagesrc']	= $replaceimagesrc;
        } else {
            $boxItem['replaceimagesrc']	= $boxItem['imagesrc'];
        }

        $this->_toolBoxItems[] = $boxItem;
    }

    /**
     * Check if this is a public Page
     * @return bool true if this Page is public, else false
     */
    public function isPublic()
    {
        if ($this->_char === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the Page is already created
     * @return bool true if this Page is created, else false
     */
    public function isCreated()
    {
        return $this->_isCreated;
    }

    /**
     * Output-Wrapper (replaces echo)
     * @param string $text The Text you wish to output
     * @param string $showhtml Set true if you want to interpret HTML in $text
     */
    public function output($text, $showhtml=false)
    {
        if (!$showhtml) $text=htmlspecialchars($text, ENT_QUOTES, "UTF-8");

        $this->_bodycontent[] = $text;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getNavigation()
    {
        return $this->nav;
    }

    /**
     * Add a new Element to _element-array
     * @param string $type Element-Type
     * @param string $name Element-Name
     * @param object $object The Element
     * @param bool $overwrite Force overwrite of existing
     * @throws Error
     * @return object The added object
     */
    public function addElement($type, $name, $object, $overwrite=false)
    {
        if (!is_array($this->_elements[$type])) $this->_elements[$type] = array();

        if (!isset($this->_elements[$type][$name]) || $overwrite) {
            $this->_elements[$type][$name] = $object;
        } else {
            throw new Error("Element ".$type."->".$name." already exists.");
        }

        return $object;
    }

    /**
     * Return the given Element
     * @param string $name
     * @throws Error
     * @return object
     */
    public function getElement($type, $name)
    {
        if (isset($this->_elements[$type][$name])) {
            return $this->_elements[$type][$name];
        } else {
            throw new Error ("Element ".$type."->".$name." does not exist");
        }
    }

    /**
     * Close Form Object
     * @param string $name
     */
    public function deleteElement($type, $name)
    {
        if (isset($this->_elements[$type][$name])) {
            unset ($this->_elements[$type][$name]);
        } else {
            throw new Error ("Element ".$type."->".$name." does not exist");
        }
    }

    /**
     * Add a new HTMLForm to the Page
     * @param string $name Name of the HTMLForm
     * @param bool $directoutput Output directly with $page->output()
     * @param bool $overwrite Overwrite existing
     * @return Ruins\Common\Controller\Form The Form Object
     */
    public function addForm($name, $directoutput=true, $overwrite=false)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        if ($directoutput) {
            $result = $this->addElement("Form", $name, new Form($this), $overwrite);
        } else {
            $result = $this->addElement("Form", $name, new Form(), $overwrite);
        }

        return $result;
    }

    /**
     * Return given Form Object
     * @param string $name
     * @return Ruins\Common\Controller\Form The Form Object
     */
    public function getForm($name)
    {
        return $this->getElement("Form", $name);
    }

    /**
     * Close Form Object
     * @param string $name
     */
    public function closeForm($name)
    {
        return $this->deleteElement("Form", $name);
    }

    /**
     * Add a new HTMLTable to the Page
     * @param string $name Name of the HTMLTable
     * @param bool $directoutput Output directly with $page->output()
     * @param bool $overwrite Overwrite existing
     * @return Ruins\Common\Controller\Table The Table Object
     */
    public function addTable($name, $directoutput=true, $overwrite=false)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        if ($directoutput) {
            $result = $this->addElement("Table", $name, new Table($this), $overwrite);
        } else {
            $result = $this->addElement("Table", $name, new Table(), $overwrite);
        }

        return $result;
    }

    /**
     * Return given Table Object
     * @param string $name
     * @return Ruins\Common\Controller\Table The Table Object
     */
    public function getTable($name)
    {
        return $this->getElement("Table", $name);
    }

    /**
     * Close Table Object
     * @param string $name
     */
    public function closeTable($name)
    {
        return $this->deleteElement("Table", $name);
    }

    /**
     * Add a new Chat to the Page
     * @param string $name Name of the Chat
     * @return Ruins\Common\Controller\Chat The Chat Object
     */
    public function addChat($name)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        // always overwrite Chat
        $result = $this->addElement("Chat", $name, new ClassicChat($this, $name), true);

        return $result;
    }

    /**
     * Return given Chat Object
     * @param string $name
     * @return Ruins\Common\Controller\Chat The Chat Object
     */
    public function getChat($name)
    {
        return $this->getElement("Chat", $name);
    }

    /**
     * Close Chat Object
     * @param string $name
     */
    public function closeChat($name)
    {
        return $this->deleteElement("Chat", $name);
    }

    /**
     * Add a new simple HTMLTable to the Page
     * @param string $name Name of the simple HTMLTable
     * @param bool $directoutput Output directly with $page->output()
     * @param bool $overwrite Overwrite existing
     * @return Ruins\Common\Controller\SimpleTable The Table Object
     */
    public function addSimpleTable($name, $directoutput=true, $overwrite=false)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        if ($directoutput) {
            $result = $this->addElement("SimpleTable", $name, new SimpleTable($this), $overwrite);
        } else {
            $result = $this->addElement("SimpleTable", $name, new SimpleTable(), $overwrite);
        }

        return $result;
    }

    /**
     * Return given SimpleTable Object
     * @param string $name
     * @return Ruins\Common\Controller\SimpleTable The Chat Object
     */
    public function getSimpleTable($name)
    {
        return $this->getElement("SimpleTable", $name);
    }

    /**
     * Close SimpleTable Object
     * @param string $name
     */
    public function closeSimpleTable($name)
    {
        return $this->deleteElement("SimpleTable", $name);
    }

    /**
     * Load Information about the chosen Template
     * @access private
     */
    protected function _loadTemplateInformation()
    {
        // Load Template Name
        if ($this->_char === false) {
            // public default template
            $this->template['name'] = self::DEFAULT_PUBLIC_TEMPLATE;
        } elseif ($this->_char->template) {
            // private template
            $this->template['name'] = $this->_char->template;
        } else {
            // private template not set
            $this->template['name'] = self::DEFAULT_PRIVATE_TEMPLATE;
        }

        // Assign the complete Path to the Base-Template
        $this->template['file']	= "index_page.tpl";

        // Set the correct Template-Paths inside the Template
        // For Paths that are sent to the Client (relative webbased paths)
        $this->template['mytemplatedir'] = SystemManager::getWebRessourcePath($this->template['name'], true);
        $this->set("mytemplatedir", $this->template['mytemplatedir']);
        $this->template['commontemplatedir'] = SystemManager::getWebRessourcePath("common");
        $this->set("commontemplatedir", $this->template['commontemplatedir']);

        // Paths that are handled inside the templategeneration progress (full filepaths)
        $this->template['myfulltemplatedir'] = DIR_WEB . $this->template['name'];
        $this->set("myfulltemplatedir", $this->template['myfulltemplatedir']);

    }

    /**
     * Create a new Template Snippet
     * @return Smarty Smarty Instance for the new Snippet
     */
    public function createTemplateSnippet($template=self::DEFAULT_PUBLIC_TEMPLATE)
    {
        $snippet = new \Smarty();
        $snippet->template_dir 		= $this->_smarty->template_dir;
        $snippet->compile_dir 		= $this->_smarty->compile_dir;
        $snippet->cache_dir 		= $this->_smarty->cache_dir;
        $snippet->config_dir 		= $this->_smarty->config_dir;
        $snippet->assign("mytemplatedir", SystemManager::getWebRessourcePath($template, true));
        $snippet->assign("commontemplatedir", SystemManager::getWebRessourcePath("common"));
        $snippet->assign("myfulltemplatedir", DIR_WEB . $template);

        return $snippet;
    }

    /**
     * Check Navigation
     * @access private
     * @return bool true if successful, else false
     */
    protected function _checkNav()
    {
        if ($this->isPublic()) {
            // page is a public one
//			if ($this->_smarty->isCached($this->template['file'], $this->_char->id)) {
                // page is public, so we don't enable caching and keep our old cache
//				$this->_smarty->caching = 0;
//			} else {
                // page is public and there is no cachefile. make sure that the cache is completly empty
//				$this->_smarty->clear_cache(null, $this->_char->id);
//			}
            $this->disableCaching();

            $this->nav->clear();
            return true;
        }
        if ($this->nav->checkRequestURL()) {
            // page is valid, erase cache so we can create a new one
            if ($this->_smarty->caching) {
                $this->_smarty->clearCache($this->template['file'], $this->_char->id);
            }

            $this->nav->clear();
            return true;
        } else {
            // this is a invalid page, load last cache
            if ($this->_char !== false && $this->_smarty->isCached($this->template['file'], $this->_char->id)) {
                echo "-~ cached version of {$this->url} ~-";
                $this->_smarty->display($this->template['file'], $this->_char->id);
                exit;
            } else {
                // cache doesn't exist
                // this shouldn't happen... ever!
                echo "i would call badnav :(";
                //$this->nav->redirect("badnav.php");

                return false;
            }
        }
    }

    /**
     * Get the Latest Generated HTML-Source
     * @return string HTML-Source
     */
    public function getLatestGenerated()
    {
        if ($this->_char->id) {
            // Prepare Template Information
            $this->_loadTemplateInformation();

            if ($this->_smarty->isCached($this->template['file'], $this->_char->id)) {
                return $this->_smarty->fetch($this->template['file'], $this->_char->id);
            } else {
                return false;
            }
        }
    }

    public function cacheExists()
    {
        if ($this->_char->id) {
            // Prepare Template Information
            $this->_loadTemplateInformation();

            if ($this->_smarty->isCached($this->template['file'], $this->_char->id)
                && $this->_char->allowednavs_cache) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Disable Caching for this page
     */
    public function disableCaching()
    {
        $this->nav->cacheNavigation	= false;
        $this->_smarty->caching  	= 0;
    }

    /**
     * Add jQuery Support
     * @access private
     */
    protected function _addJQuerySupport()
    {
        array_unshift($this->_headscripts, "<script src='".SystemManager::getWebRessourcePath("javascript/jquery-1.5.1.min.js", true)."' type='text/javascript'></script>");
        $this->addJavaScriptFile("jquery-ui-1.8.13.custom.min.js");
        $this->addJavaScriptFile("jquery.plugin.timers.js");
    }

    /**
     * Does some health-checks on the headscripts and includes them into the template
     * @access private
     */
    protected function _generateHeadScripts()
    {
        if (is_array($this->_headscripts)) {
            // remove duplicates
            $this->_headscripts = array_unique($this->_headscripts);

            // insert the headscripts into the template
            $this->set("headscript", implode("\n", $this->_headscripts));
        }
    }

    /**
     * Does some health-checks on the navigation and includes them into the template
     * @access private
     */
    protected function _generateNavigation()
    {
        $navMain = "";
        $navShared = "";;
        $boxOpen = false;

        foreach ($this->nav->getLinkList() as $linklist) {

            if ($linklist['displayname']) {

                switch ($linklist['type']) {

                    default:
                    case "page":
                    case "Page":
                        if ($linklist['position'] == "main") {
                            // generating the leftbar
                            if (!$boxOpen && !$linklist['url']) {
                                // First NavHead
                                $navMain .= "<div class='navbox'>
                                                <h3>".$linklist['displayname']."</h3>
                                                <div class='links'>";
                                $boxOpen = true;
                            } else if ($boxOpen && !$linklist['url']) {
                                // New NavHead (not the first)
                                $navMain .= "</div></div>" .
                                            "<div class='navbox'>
                                                <h3>".$linklist['displayname']."</h3>
                                                <div class='links'>";
                            } else if ($boxOpen) {
                                // Standard Link
                                $snippet = $this->createTemplateSnippet();
                                $snippet->assign("linktarget", $linklist['url']);
                                $snippet->assign("linkname", $linklist['displayname']);
                                $snippet->assign("description", htmlspecialchars($linklist['description'], ENT_QUOTES));
                                $navMain .= $snippet->fetch("snippet_navigation.tpl");
                            } else {
                                // No NavHead
                                $navMain .= "<div class='navbox'>
                                                <h3>NavHead fehlt</h3>";
                                $snippet = $this->createTemplateSnippet();
                                $snippet->assign("linktarget", $linklist['url']);
                                $snippet->assign("linkname", $linklist['displayname']);
                                $snippet->assign("description", htmlspecialchars($linklist['description'], ENT_QUOTES));
                                $navMain .= $snippet->fetch("snippet_navigation.tpl");
                                $boxOpen = true;
                            }
                        } else if ($linklist['position'] == "shared") {
                            // generate the topbar
                            $navShared .= "<a 	href='".$linklist['url']."'
                                                title='".htmlspecialchars($linklist['description'], ENT_QUOTES)."'>" .
                                            $linklist['displayname'] .
                                        "</a>";
                        }
                        break;

                    case "popup":
                    case "Popup":
                        //generate the topbar
                        if ($linklist['position'] == "shared") {
                            $navShared .= "<a 	href='".$linklist['url']."'
                                                title='".htmlspecialchars($linklist['description'], ENT_QUOTES)."'
                                                onclick='return popup(this)'>" .
                                            $linklist['displayname'] .
                                        "</a>";
                        }
                        break;

                    // if links don't appear, check the position if it set correctly
                }
            }
        }
        // close the div box in the leftbar
        // 1st div: closes div 'links'
        // 2nd div: closes div 'navbox'
        $navMain .= "</div></div>";
        $boxOpen = false;
        if (is_array($this->_bodycontent)) {
            // insert the bodycontent into the template
            $this->set("navMain", $navMain);
            $this->set("navShared", $navShared);
        }
    }

    /**
     * Generate the ToolBox (small tools)
     */
    protected function _generateToolBox()
    {
        $toolBoxHTML 	= "";
        $toolBoxJS		= "$(document).ready(function(){
                                $('.toolboxitem').hover(function() {
                                    document.body.style.cursor='pointer';
                                });
                                $('.toolboxitem').mouseout(function() {
                                    document.body.style.cursor='default';
                                });";

        foreach ($this->_toolBoxItems as $toolItem) {
            $toolBoxHTML 	.= "<img 	id='".$toolItem['link']->displayname."'
                                        class='toolboxitem'
                                        src='".$toolItem['imagesrc']."'
                                        title='".$toolItem['description']."' />";

            $toolBoxJS 		.= "$('#".$toolItem['link']->displayname."').click(function() {
                                    $.ajax({
                                      type: 'GET',
                                      url: '".SystemManager::getOverloadedFilePath("/Helpers/ajax/".$toolItem['link']->url, true)."',
                                      dataType: 'script'
                                    });
                                    $(this).replaceWith('<img src=\"".$toolItem['replaceimagesrc']."\" />');
                                });";
        }

        $toolBoxJS		.= "});";

        $this->addJavaScript($toolBoxJS);
        $this->set("toolbox", $toolBoxHTML);
    }

    /**
     * Generate Stats
     */
    protected function _generateStats()
    {
        $em = Registry::getEntityManager();

        // This are all Stats available for the template
        $statitems = array(	// Character
                            "Name"			=>	"displayname",
                            "Level"			=>	"level",
                            "Gesundheit"	=>	"healthpoints",
                            "Lebenspunkte"	=>	"lifepoints",
                            "Stärke"		=> 	"strength",
                            "Beweglichkeit"	=>	"dexterity",
                            "Konstitution"	=>	"constitution",
                            "Intelligenz"	=>	"intelligence",
                            "Weisheit"		=>	"wisdom",
                            "Charisma"		=>	"charisma",
                            "Rasse"			=>  "race",
                            "Beruf"			=>	"profession",
                            "Geschlecht"	=>	"sex",

                            // Posession
                            "Waffe"			=>	"weaponname",
                            "Waffenschaden"	=>	"weapondamage",
                            "Kupfer"		=>	"copper",
                            "Silber"		=>	"silver",
                            "Gold"			=> 	"gold",

                            );


        // Create a new Smarty-Snippet and
        // use the global Smarty-Settings
        // to initialize the new class
        $snippet = $this->createTemplateSnippet();

        if ($this->_char) {
            foreach ($statitems as $value) {
                // assign the values inside the snippet
                switch ($value) {

                    default:
                        $snippet->assign($value, BtCode::decode($this->_char->$value));
                        break;

                    case "copper":
                    case "silver":
                    case "gold":
                        $snippet->assign($value, $this->_char->money->getCurrencyWithPic($value));
                        break;

                    case "weaponname":
                        if ($weapon = ItemManager::getEquippedItem($this->_char, "weapon")) {
                            $snippet->assign($value, $weapon->name);
                        } else {
                            $snippet->assign($value, "N/A");
                        }
                        break;

                    case "weapondamage":
                        if ($weapon = ItemManager::getEquippedItem($this->_char, "weapon")) {
                            $snippet->assign($value, $weapon->showDamage(false));
                        } else {
                            $snippet->assign($value, "N/A");
                        }
                        break;
                }
            }

            // get users currently here
            $userlist = implode(", ", $em->getRepository("Main:Character")->getListAtPlace($this->shortname));

            $snippet->assign("characters_here", BtCode::decode($userlist));

            // generate the result
            $output = $snippet->fetch("snippet_stats.tpl");

            // put the result into the page
            $this->set("stats", $output);

        }

    }

    /**
     * Generate Character list
     */
    protected function _generateCharacterList()
    {
        $em = Registry::getEntityManager();

        $characterlist = $em->getRepository("Main:Character")
                            ->getList(array("displayname"), "displayname", "ASC", true);

        $output = "<div class=\"characterbox\">";
        $output .= "<h3>" . count($characterlist) . " User Online</h3>";

        foreach ($characterlist as $character) {
            $output .= "<div class=\"item\">
                        <div class= \"character\">" . BtCode::decode($character['displayname']) . "</div>
                        </div>";
        }

        $output .= "</div>";

        $this->set("characterlist", $output);
    }

    /**
     * Does some health-checks on the bodytext and includes them into the template
     * @access private
     */
    protected function _generateBodyContent()
    {
        if (is_array($this->_bodycontent)) {
            // decode bodycontent
            foreach ($this->_bodycontent as &$line) {
                $line = BtCode::decode($line);
            }
            // merge array to string
            $text = implode("\n", $this->_bodycontent);

            // insert the bodycontent into the template
            $this->set("main", $text);
        }
    }

    /**
     * Calculates the Pagegeneration Time
     * @access private
     * @return bool true if successful, else false
     */
    protected function _generatePagegenTime()
    {
        if (is_float($this->_pagegenerationstarttime)) {
            $pagegentime = round(microtime(true) - $this->_pagegenerationstarttime,3) * 1000;
        } else {
            $pagegentime = "failed";
        }

        $this->set("pagegen", $pagegentime);
    }

    /**
     * Collect all Data and compile the Page
     */
    public function show()
    {
        if ($this->modulesenabled) ModuleManager::callModule(ModuleManager::EVENT_PRE_PAGEGENERATION, $this);

        $this->_addJQuerySupport();
        $this->_generateToolBox();

        $this->_generateHeadScripts();

        $this->_generateNavigation();

        $this->_generateBodyContent();

        $this->_generatePagegenTime();

        $this->set("version", "");
        $this->set("copyright", "");

        if ($this->isPublic()) {
            // public page - generate the CharacterList
            $this->_generateCharacterList();
            $this->set("stats", "");
        } else {
            // private page - generate Userstats
            $this->_generateStats();
            $this->set("characterlist", "");
        }

        if ($this->modulesenabled) ModuleManager::callModule(ModuleManager::EVENT_POST_PAGEGENERATION, $this);

        if ($this->_smarty->caching) {
            $this->_smarty->display($this->template['file'], $this->_char->id);
        } else {
            $this->_smarty->display($this->template['file']);
        }
    }

}

?>
