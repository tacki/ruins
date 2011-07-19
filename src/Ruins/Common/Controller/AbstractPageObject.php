<?php
/**
 * Abstract Page Object
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Controller;
use Ruins\Main\Controller\Nav;
use Doctrine\ORM\EntityManager;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\Request;
use Ruins\Common\Controller\SessionStore;
use Ruins\Common\Interfaces\PageObjectInterface;
use Ruins\Common\Interfaces\OutputObjectInterface;

/**
 * Abstract Page Object
 * @package Ruins
 */
abstract class AbstractPageObject implements PageObjectInterface
{
    /**
     * Default Page Title
     * @var string
     */
    public $title = 'No Title set';

    /**
     * Public flag
     * @var bool
     */
    protected $public = false;

    /**
     * No Caching flag
     * @var bool
     */
     protected $dontCache = false;

    /**
     * System Registry
     * @var Registry
     */
    protected $_registry;

    /**
     * Entity Manager
     * @var EntityManager
     */
    protected $_em;

    /**
     * Request Object
     * @var Request
     */
    protected $_request;

    /**
     * Enter description here ...
     * @var OutputObjectInterface
     */
    protected $_outputObject;

    /**
     * Initialize Object
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // Init Registry Object
        $this->_registry = new Registry;
        $this->_request  = $request;
        $this->_em       = $this->getEntityManager();

        // Initialize User
        $this->initUser();
        // Initialize Visibility ($public and $dontCache)
        $this->initVisibility();
        // Inititalize OutputObject
        $this->initOutputObject();
    }

    /**
     * Get loggedin User
     * @return Ruins\Common\Controller\Config
     */
    public function getConfig()
    {
        return $this->getRegistry()->getMainConfig();
    }

    /**
     * Get Entity Manager
     * @return Ruins\Common\Controller\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getRegistry()->getEntityManager();
    }

    /**
     * Get Output Object
     * @var OutputObjectInterface
     */
    public function getOutputObject()
    {
        return $this->_outputObject;
    }

    /**
     * Get Registry Object
     * @return Ruins\Common\Controller\Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * Get Request Object
     * @return Ruins\Common\Controller\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get loggedin User
     * @return Ruins\Common\Controller\User
     */
    public function getUser()
    {
        return $this->getRegistry()->getUser();
    }

    /**
     * Check if Page Object is Public
     * @return boolean
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * (non-PHPdoc)
     * @see Ruins\Common\Interfaces.PageObjectInterface::setTitle()
     */
    public function setTitle($title=false)
    {
        if ($title) {
            $this->title = $title;
            $this->getOutputObject()->setTitle($title);
        } else {
            $this->getOutputObject()->setTitle($this->title);
        }
    }

    /**
     * Get Title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Render the Page Object
     */
    public function render()
    {
        // Set Title of PageObject
        $this->setTitle();

        // Create QueryData and pass it to createContent()
        // QueryExtra overwrites Post overwrites Query
        $query = array_merge($this->getRequest()->getQueryAsArray(),
                             $this->getRequest()->getPostAsArray(),
                             $this->getRequest()->getRoute()->getQueryExtra());
        $this->createContent($this->getOutputObject(), $query);

        // Call Show-Method of PageObject
        $this->getOutputObject()->show("index.tpl");

        // FIXME: this has nothing to do here
        if ($nav = $this->getOutputObject()->getNavigation()) $nav->save();

        // FIXME: find better place for this
        $this->getEntityManager()->flush();
    }

    protected function initUser()
    {
        // Load User if in Session
        if ($userid = SessionStore::get('userid')) {
            $user = $this->getEntityManager()->find("Main:User",$userid);
            if ($user->settings->default_character) {
                $user->character = $user->settings->default_character;
            }

            Registry::setUser($user);
        }
    }

    protected function initVisibility()
    {
        $config = $this->getConfig();
        $routeRequest = $this->getRequest()->getRouteAsString();

        foreach ($config->get("publicpages") as $publicpage) {
            if (substr($routeRequest, 0, strlen($publicpage)) == $publicpage) {
                $this->public = true;
            }
        }

        foreach ($config->get("nocachepages") as $nocachepage) {
            if (substr($routeRequest, 0, strlen($nocachepage)) == $nocachepage) {
                $this->dontCache = true;
            }
        }
    }

    protected function initOutputObject()
    {
        $caller = $this->getRequest()->getRoute()->getCaller();

        $classname = 'Ruins\\Common\\Controller\\OutputObjects\\'.$caller;

        $this->_outputObject = new $classname;

        $this->getOutputObject()->setNavigation(new Nav());

        if ($this->dontCache) {
            $this->getOutputObject()->disableCaching();
        }

        $this->getOutputObject()->create($this->getRequest());

        if ($this->getUser() && !$this->isPublic()) {
            $user = $this->getUser();

            // Set current_nav if this is not the portal
            if (strlen($this->getOutputObject()->getUrl()) && strpos($this->getOutputObject()->getUrl(), "Page/Common/Portal") === false) {
                $user->character->current_nav = (string)$this->getOutputObject()->getUrl();
            } elseif (!$user->character->current_nav || !$this->getOutputObject()->getUrl()) {
                $user->character->current_nav = "Page/Ironlance/Citysquare";
            }
        } elseif (!$this->isPublic()) {
            // this is a private page, but no user is loaded. Force to logout
            SessionStore::set("logoutreason", "Automatischer Logout: Nicht eingeloggt!");
            $this->getOutputObject()->getNavigation()->redirect("Page/Common/Logout");
        }

        // Add Page to Registry
        Registry::set('main.output', $page);
    }
}