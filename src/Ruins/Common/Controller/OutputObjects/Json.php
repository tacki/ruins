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
class Json implements OutputObjectInterface
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
     * @see Ruins\Common\Interfaces.OutputObjectInterface::create()
     */
    public function create(Request $request)
    {
        $this->url = new Url($request);

        $this->templateEngine  = Registry::get('smarty');

        $this->getTemplateEngine()->caching = 0;

        $baseTemplateDir       = reset($this->getTemplateEngine()->template_dir);
        $pageObjectTemplateDir = $baseTemplateDir . "/" . $request->getRoute()->getCallerName();

        $this->getTemplateEngine()
             ->addTemplateDir($pageObjectTemplateDir);

        $this->getTemplateEngine()->assign("basetemplatedir", SystemManager::htmlpath($baseTemplateDir));
        $this->getTemplateEngine()->assign("mytemplatedir", SystemManager::htmlpath($pageObjectTemplateDir));
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::assign()
     */
    public function assign($placeholder, $value)
    {
        $this->getTemplateEngine()->assign($placeholder, $value);
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::setTitle()
     */
    public function setTitle($value)
    {
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
     * @see Ruins\Common\Interfaces.OutputObjectInterface::setNavigation()
     */
    public function setNavigation(NavigationInterface $navigation)
    {
    }

    /**
     * @see Ruins\Common\Interfaces.OutputObjectInterface::getNavigation()
     */
    public function getNavigation($container="main")
    {
        return null;
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
        $this->getTemplateEngine()->append(
            'main',
            json_encode($text)
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
     * @see Ruins\Common\Interfaces.OutputObjectInterface::show()
     */
    public function show($template)
    {
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
}