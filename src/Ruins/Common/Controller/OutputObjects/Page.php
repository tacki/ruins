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
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\Request;
use Ruins\Common\Controller\Url;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Common\Interfaces\UserInterface;
use Ruins\Common\Interfaces\NavigationInterface;
use Ruins\Main\Manager\SystemManager;

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
     * @var NavigationInterface
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

        $this->templateEngine = Registry::get('smarty');
        $templatedir          = reset($this->templateEngine->template_dir);
        $this->templateEngine->assign("mytemplatedir", SystemManager::htmlpath($templatedir));

        $this->disableCaching();

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
        $this->templateEngine->assign($placeholder, $value);
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
        $this->templateEngine->append(
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
        $this->templateEngine->append(
            'cssHeadIncludes',
            SystemManager::getWebRessourcePath('styles/'.$script, true)
        );
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
        if ($this->user) {
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

    public function setNavigation(NavigationInterface $navigation)
    {
        $this->navigation = $navigation;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::getNavigation()
     */
    public function getNavigation()
    {
        return $this->navigation;
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::output()
     */
    public function output($text, $showhtml=false)
    {
        if (!$showhtml) $text=htmlspecialchars($text, ENT_QUOTES, "UTF-8");

        $this->templateEngine->append(
            'main',
            $text
        );
    }

    /**
    * Get the Latest Generated HTML-Source
    * @return string HTML-Source
    */
    public function getLatestGenerated($template)
    {
        if ($this->isPrivate()) {
            if ($this->templateEngine->isCached($template, $this->user->character->id)) {
                return $this->templateEngine->fetch($template, $this->user->character->id);
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
        $this->templateEngine->caching = 0;
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

        if ($this->isPrivate) {
            $this->templateEngine->display($template, $this->user->character->id);
        } else {
            $this->templateEngine->display($template);
        }
    }

    /**
    * Does some health-checks on the navigation and includes them into the template
    */
    protected function generateNavigation()
    {
        $navMain = "";
        $navShared = "";;
        $boxOpen = false;

        foreach ($this->getNavigation()->getLinkList() as $linklist) {

            if ($linklist['displayname']) {

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
                    $this->templateEngine->append('navShared',
                                                  array('url' => $linklist['url'],
                                                        'title' => htmlspecialchars($linklist['description'], ENT_QUOTES),
                                                        'display' => $linklist['displayname']
                                                 ));
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
            $this->assign("navMain", $navMain);
            $this->assign("navShared", $navShared);
        }
    }
}