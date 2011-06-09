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
namespace Controller;
use Smarty,
    BaseObject,
    OutputObject,
    Module,
    ModuleSystem,
    Error,
    Form,
    Table,
    SimpleTable;

/**
 * Class Defines
 */
define("PAGE_DEFAULT_PUBLIC_TEMPLATE", 	"default");
define("PAGE_DEFAULT_PRIVATE_TEMPLATE", "default");

/**
 * Page Class
 *
 * Page-Class, the Heart of the Template-System
 * @package Ruins
 */
class Page extends BaseObject implements OutputObject
{
    /**
     * Navigation Class
     * @var nav
     */
    public $nav;

    /**
     * Page URL
     * @var string
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
        global $smarty;

        // Set microtime to meassure the page-generation time
        $this->_pagegenerationstarttime = getMicroTime();

        // Initialize Properties
        $this->template 		= array();
        $this->_char 			= $char;
        $this->_isCreated		= false;
        $this->_headscripts 	= array();
        $this->_bodycontent 	= array();
        $this->_outputmodule	= array();
        $this->_toolBoxItems	= array();
        $this->modulesenabled	= true;

        // This Class is always 'loaded'
        parent::__construct();
        $this->isloaded 	= true;

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

        // Load Page Output Modules
        if ($this->modulesenabled) {
            ModuleSystem::loadOutputModules($this);
        }

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
     * Add an Output Module (overwrite existing)
     * @param string $modulename Name of the Module
     * @param Module $outputmodule The Module itself
     */
    public function addOutputModule($modulename, Module $outputmodule)
    {
        // existing Modules are overwritten
        if ($this->modulesenabled) {
            $this->_outputmodule[$modulename] = $outputmodule;
        }
    }

    /**
     * Remove an Output Module
     * @param string $modulename Name of the Module
     */
    public function removeOutputModule($modulename)
    {
        if (isset($this->_outputmodule[$modulename])) {
            unset ($this->_outputmodule[$modulename]);
        }
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
     * @param string $script Script Filename (has to be inside of includes/javascript/
     */
    public function addJavaScriptFile($script)
    {
        $this->_headscripts[] = "<script src='".htmlpath(DIR_INCLUDES)."/javascript/".$script."' type='text/javascript'></script>";
    }

    /**
     * Adds a CSS-file as an include to the Header
     * @param string $script Script Filename (has to be inside of templates/common/styles)
     */
    public function addCommonCSS($script)
    {
        $this->_headscripts[] = "<link href='".htmlpath(DIR_TEMPLATES)."/common/styles/".$script."' rel='stylesheet' type='text/css' />";
    }

    /**
     * Adds a CSS-file as an include to the Header
     * @param string $script Script Filename (has to be inside of templates/<templatename>/)
     */
    public function addTemplateCSS($script)
    {
        $this->_headscripts[] = "<link href='".htmlpath(DIR_TEMPLATES)."/". $this->template['name'] ."/".$script."' rel='stylesheet' type='text/css' />";
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

    /**
     * Add a new HTMLForm to the Page
     * @param string $name Name of the HTMLForm
     * @param bool $directoutput Output directly with $page->output()
     * @return string The Name of the Form
     */
    public function addForm($name, $directoutput=true)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        if ($directoutput) {
            $this->addProperty($name, new Form($this), true);
        } else {
            $this->addProperty($name, new Form(), true);
        }

        return $name;
    }

    /**
     * Add a new HTMLTable to the Page
     * @param string $name Name of the HTMLTable
     * @param bool $directoutput Output directly with $page->output()
     * @return string The Name of the Table
     */
    public function addTable($name, $directoutput=true)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        if ($directoutput) {
            $this->addProperty($name, new Table($this), true);
        } else {
            $this->addProperty($name, new Table(), true);
        }

        return $name;
    }

    /**
     * Add a new Chat to the Page
     * @param string $name Name of the Chat
     * @return string The Name of the Chat
     */
    public function addChat($name)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        $this->addProperty($name, new ClassicChat($this, $name), true);

        return $name;
    }

    /**
     * Add a new simple HTMLTable to the Page
     * @param string $name Name of the simple HTMLTable
     * @param bool $directoutput Output directly with $page->output()
     * @return string The Name of the Table
     */
    public function addSimpleTable($name, $directoutput=true)
    {
        // remove whitespaces
        $name = str_replace(' ', '', $name);

        if ($directoutput) {
            $this->addProperty($name, new SimpleTable($this));
        } else {
            $this->addProperty($name, new SimpleTable());
        }

        return $name;
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
            $this->template['name'] = PAGE_DEFAULT_PUBLIC_TEMPLATE;
        } elseif ($this->_char->template) {
            // private template
            $this->template['name'] = $this->_char->template;
        } else {
            // private template not set
            $this->template['name'] = PAGE_DEFAULT_PRIVATE_TEMPLATE;
        }

        // Assign the complete Path to the Base-Template
        $this->template['file']	= $this->template['name']. "/" . "index_page.tpl";

        // Set the correct Template-Paths inside the Template
        // For Paths that are sent to the Client (relative webbased paths)
        $this->template['mytemplatedir'] = htmlpath($this->_smarty->template_dir . "/" . $this->template['name']);
        $this->set("mytemplatedir", $this->template['mytemplatedir']);
        $this->template['commontemplatedir'] = htmlpath($this->_smarty->template_dir . "/common");
        $this->set("commontemplatedir", $this->template['commontemplatedir']);

        // Paths that are handled inside the templategeneration progress (full filepaths)
        $this->template['myfulltemplatedir'] = $this->_smarty->template_dir . "/" . $this->template['name'];
        $this->set("myfulltemplatedir", $this->template['myfulltemplatedir']);

    }

    /**
     * Create a new Template Snippet
     * @return Smarty Smarty Instance for the new Snippet
     */
    public function createTemplateSnippet()
    {
        $snippet = new Smarty();
        $snippet->template_dir 		= $this->_smarty->template_dir . $this->template['name'];
        $snippet->compile_dir 		= $this->_smarty->compile_dir;
        $snippet->cache_dir 		= $this->_smarty->cache_dir;
        $snippet->config_dir 		= $this->_smarty->config_dir;
        $snippet->assign("mytemplatedir", htmlpath($snippet->template_dir));
        $snippet->assign("commontemplatedir", htmlpath($this->_smarty->template_dir . "/common"));
        $snippet->assign("myfulltemplatedir", $snippet->template_dir);

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
        array_unshift($this->_headscripts, "<script src='".htmlpath(DIR_INCLUDES)."/javascript/jquery-1.3.2.min.js' type='text/javascript'></script>");
        $this->addJavaScriptFile("jquery-ui-1.7.2.custom.min.js");
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

        foreach ($this->nav->export() as $linklist) {

            if ($linklist['displayname']) {
                // Get linktype (page or popup)
                $linktype = array_shift(explode("=", $linklist['url']));

                switch ($linktype) {

                    default:
                    case "page":
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
                            $navShared .= "<a 	href='?".$linklist['url']."'
                                                title='".htmlspecialchars($linklist['description'], ENT_QUOTES)."'>" .
                                            $linklist['displayname'] .
                                        "</a>";
                        }
                        break;

                    case "popup":
                        //generate the topbar
                        if ($linklist['position'] == "shared") {
                            $navShared .= "<a 	href='?".$linklist['url']."'
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
                                      url: '".htmlpath(DIR_INCLUDES)."/helpers/ajax/".$toolItem['link']->url."',
                                      dataType: 'script'
                                    });
                                    $(this).replaceWith('<img src=".$toolItem['replaceimagesrc']." />');
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
                        $snippet->assign($value, $this->_char->money->getCurrency($value));
                        break;

                    case "weaponname":
                        if ($weapon = \Manager\Item::getEquippedItem($this->_char, "weapon")) {
                            $snippet->assign($value, $weapon->name);
                        } else {
                            $snippet->assign($value, "N/A");
                        }
                        break;

                    case "weapondamage":
                        if ($weapon = \Manager\Item::getEquippedItem($this->_char, "weapon")) {
                            $snippet->assign($value, $weapon->showDamage(false));
                        } else {
                            $snippet->assign($value, "N/A");
                        }
                        break;
                }
            }

            // get users currently here
            $userlist = "";
            foreach (\Manager\User::getCharactersAt($this->shortname) as $char) {
                $userlist .= $char['displayname'] . " ";
            }

            $snippet->assign("characters_here", BtCode::decode($userlist));

            // generate the result
            $output = $snippet->fetch("snippet_stats.tpl");

            // put the result into the page
            $this->set("stats", $output);

        }

    }

    /**
     * Generate Userlist
     */
    protected function _generateUserList()
    {
        $userlist = \Manager\User::getCharactersOnline();
        $usercount = count($userlist);

        $output = "<div class=\"userbox\">";
        $output .= "<h3>" . $usercount . " User Online</h3>";

        foreach ($userlist as $username) {
            $output .= "<div class=\"item\">
                        <div class= \"username\">" . BtCode::decode($username) . "</div>
                        </div>";
        }

        $output .= "</div>";

        $this->set("userlist", $output);
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
     * Call the Navigation Module of an Outputmodule
     */
    protected function _callOutputModuleNavigation()
    {
        if (is_array($this->_outputmodule)) {
            // call NavigationModule of each Module
            foreach ($this->_outputmodule as $module) {
                $module->callNavModule($this->nav);
            }
        }
    }

    /**
     * Call the Text Module of an Outputmodule
     */
    protected function _callOutputModuleText()
    {
        if (is_array($this->_outputmodule)) {
            // call NavigationModule of each Module
            foreach ($this->_outputmodule as $module) {
                $module->callTextModule($this->_bodycontent);
            }
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
            $pagegentime = round(getMicroTime() - $this->_pagegenerationstarttime,3) * 1000;
        } else {
            $pagegentime = "failed";
        }

        $this->set("pagegen", $pagegentime);
    }

    /**
     * Collect all Data and compile the Page
     * WARNING: CHARACTERCHANGES HERE ARE NOT AUTOMATICALLY SAVED!
     * 			$user->save() and $user->char->save() are called
     * 			before $page->show()
     */
    public function show()
    {
        $this->_addJQuerySupport();
        $this->_generateToolBox();

        $this->_generateHeadScripts();

        $this->_callOutputModuleNavigation();
        $this->_generateNavigation();

        $this->_callOutputModuleText();
        $this->_generateBodyContent();

        $this->_generatePagegenTime();

        $this->set("version", "");
        $this->set("copyright", "");

        if ($this->isPublic()) {
            // public page - generate the UserList
            $this->_generateUserlist();
            $this->set("stats", "");
        } else {
            // private page - generate Userstats
            $this->_generateStats();
            $this->set("userlist", "");
        }

        if ($this->_smarty->caching) {
            $this->_smarty->display($this->template['file'], $this->_char->id);
        } else {
            $this->_smarty->display($this->template['file']);
        }
    }

}

?>
