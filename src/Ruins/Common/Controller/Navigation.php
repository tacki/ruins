<?php
/**
 * Navigation Class
 *
 * Navigation Class which cares about the Navigation between the Pages
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Controller;
use Ruins\Common\Interfaces\GroupInterface;
use Ruins\Common\Interfaces\NavigationInterface;
use Ruins\Common\Controller\Request;
use Ruins\Common\Manager\RequestManager;

/**
 * Navigation Class
 *
 * Navigation Class which cares about the Navigation between the Pages
 * @package Ruins
 */
class Navigation implements NavigationInterface
{
    /**
     * @var array
     */
    protected $linkList=array();

    /**
     * @var string
     */
    protected $container="main";

    /**
     * @var Request
     */
    protected $request;


    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::__construct()
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::getLinkList()
     */
    public function getLinkList()
    {
        return $this->linkList;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::setLinkList()
     */
    public function setLinkList(array $linkList)
    {
        $this->linkList = $linkList;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::setContainerName()
     */
    public function setContainerName($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::getContainerName()
     */
    public function getContainerName()
    {
        return $this->container;
    }

    /**
     * @return \Ruins\Common\Controller\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::addHead()
     */
    public function addHead($title, GroupInterface $restriction=null)
    {
        $this->addToLinkList($title, false, false, $restriction);

        return $this;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::addLink()
     */
    public function addLink($name, $url, $description="", GroupInterface $restriction=null)
    {
        $this->addToLinkList($name, $url, $description, $restriction);

        return $this;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::addHiddenLink()
     */
    public function addHiddenLink($url, GroupInterface $restriction=null)
    {
        $this->addToLinkList(false, $url, false, $restriction);

        return $this;
    }

    /**
     * @see Ruins\Common\Interfaces.NavigationInterface::addTextLink()
     */
    public function addTextLink($text, $url, $description="", GroupInterface $restriction=null)
    {
        $this->addToLinkList($text, $url, $description, $restriction);

        return $this;
    }

    /**
     * Add Link to Linklist
     * @param string $display
     * @param string $url
     * @param string $description
     * @param GroupInterface $restriction
     */
    protected function addToLinkList($display, $url=false, $description=false, GroupInterface $restriction=null)
    {
        $linktype = RequestManager::createRequest($url)->getRoute()->getCallerName();

        $this->linkList[] = array(
                                    'displayname' => $display,
                                    'url'		  => ($url?RequestManager::getWebBasePath()."/".$url:false),
                                    'position'	  => $this->getContainerName(),
                                    'description' => $description,
                                    'type'		  => $linktype,
                                    'restriction' => $restriction,
                                 );
    }
}