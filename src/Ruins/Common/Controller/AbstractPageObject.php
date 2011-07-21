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
use Doctrine\ORM\EntityManager;
use Ruins\Common\Manager\RequestManager;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\Request;
use Ruins\Common\Controller\Firewall;
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
     * @var string
     */
    protected $template = "index.tpl";

    /**
     * System Registry
     * @var Registry
     */
    protected $_registry;

    /**
     * Entity Manager
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Firewall
     */
    protected $firewall;

    /**
     * Request Object
     * @var Request
     */
    protected $request;

    /**
     * Enter description here ...
     * @var OutputObjectInterface
     */
    protected $outputObject;

    /**
     * Initialize Object
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        // Init Registry Object
        $this->registry = new Registry;
        $this->request  = $request;
        $this->em       = $this->getEntityManager();
        $this->security = new Firewall;

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
     * @return Ruins\Common\Interfaces\OutputObjectInterface
     */
    public function getOutputObject()
    {
        return $this->outputObject;
    }

    /**
     * Get Registry Object
     * @return Ruins\Common\Controller\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Get Request Object
     * @return Ruins\Common\Controller\Firewall
     */
    public function getFirewall()
    {
        return $this->security;
    }

    /**
     * Get Request Object
     * @return Ruins\Common\Controller\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get loggedin User
     * @return Ruins\Common\Interfaces\UserInterface
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
     * Check if a Page Object is Private
     * @return boolean
     */
    public function isPrivate()
    {
        return !$this->public;
    }

    /**
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
     * Set Template Filename
     * Has to be inside a Templatedirectory known to our Template Engine
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Get Template Filename
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Redirect to another Page
     * @param string $url Target of the redirect
     */
    public function redirect($url)
    {
        // Add Url to Navigation
        if ($this->isPrivate()) {
            $this->getOutputObject()->getNavigation()->addHiddenLink($url);
        }

        // Flush EntityManager
        $this->getEntityManager()->flush();

        // Redirect
        $redirect = RequestManager::getWebBasePath() . "/" . $url;

        if ($this->getConfig()->get("useManualRedirect", 0)) {
            echo "Redirecting to $url <br />";
            echo "<a href='". $redirect ."'>Continue</a>";
            exit;
        } else {
            $header = "Location: ".$redirect;
            header($header);
            exit;
        }
    }

    /**
     * Render the Page Object
     */
    public function render()
    {
        if ($this->isPrivate()) {
            // Bootstrap User
            $this->bootstrapUser();
        }

        // Set Title of PageObject
        $this->setTitle();

        // Create QueryData and pass it to createContent()
        // QueryExtra overwrites Post overwrites Query
        $query = array_merge($this->getRequest()->getQueryAsArray(),
                             $this->getRequest()->getPostAsArray(),
                             $this->getRequest()->getRoute()->getQueryExtra());
        $this->createContent($this->getOutputObject(), $query);

        // Call Show-Method of PageObject
        $this->getOutputObject()->show($this->getTemplate());

        if ($this->isPrivate()) {
            // Filter Pages that are not accessable due to Restrictions
            $allowedNavigation = $this->getFirewall()
                                      ->filterNavigationRestriction($this->getUser(), $this->getOutputObject()->getNavigation());

            // Update User Navigation
            $this->getUser()->getCharacter()->setAllowedNavigation($allowedNavigation);
        }
    }

    /**
     * Get Page Object from Cache and show it
     * @return bool
     */
    public function renderFromCache()
    {
        if ($this->getOutputObject()->cacheExists($this->getTemplate())) {
            $this->getOutputObject()->showLatestGenerated($this->getTemplate());
            return true;
        } else {
            return false;
        }
    }

    /**
     * Initialize visibility of this Page Object
     */
    private function initVisibility()
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

    /**
     * Initialize matching Output Object
     */
    private function initOutputObject()
    {
        $caller = $this->getRequest()->getRoute()->getCallerName();
        $classname = 'Ruins\\Common\\Controller\\OutputObjects\\'.$caller;

        $this->outputObject = new $classname;

        // Create OutputObject
        $this->getOutputObject()->create($this->getRequest());

        // Dont cache if we say so
        if ($this->isPublic() || $this->dontCache) {
            $this->getOutputObject()->disableCaching();
        }

        if ($this->isPrivate() && $this->getUser()) {
            // This PageObject is private, set the underlying OutputObject to private too
            $this->getOutputObject()->setPrivate($this->getUser());
        } elseif ($this->isPrivate() && !$this->getUser()) {
            // This Page Object is private, but there is no User loaded. Force to login
            SessionStore::set("logoutreason", "Nicht eingeloggt!");
            $this->redirect("Page/Common/Login");
        }

        // Add Page to Registry
        Registry::set('main.output', $this->getOutputObject());
    }

    /**
     * Bootstrap User
     */
    private function bootstrapUser()
    {
        $user = $this->getUser();

        if ($user && $this->isPrivate() && $this->getOutputObject()->isPrivate()) {
            // Page is private and we have an loggedin User

            // Check if this Request is allowed to the User
            if (!$this->dontCache && !$this->getFirewall()->checkRequestAllowed($user, $this->getRequest())) {
                if ($this->renderFromCache()) {
                    echo "~~~ From Cache (Firewall Rule) ~~~";
                    exit;
                } else {
                    $this->redirect("Page/Common/Error404");
                }
            } else {
                $this->getOutputObject()->clearCache($this->getTemplate());
            }

            // Set current_nav if this is not the portal
            if (strpos($this->getOutputObject()->getUrl(), "Page/Common/Portal") === false) {
                $user->getCharacter()->current_nav = (string)$this->getOutputObject()->getUrl();
            } elseif (!strlen($user->getCharacter()->current_nav)) {
                // Set default Navigation (should not happen)
                $user->getCharacter()->current_nav = "Page/Ironlance/Citysquare";
            }
        }
    }
}