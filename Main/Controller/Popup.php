<?php
/**
 * Popup Class
 *
 * Popup-Class, handles Popups and is based on Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Controller;

/**
 * Popup Class
 *
 * Popup-Class, handles Popups and is based on Page
 * @package Ruins
 */
class Popup extends Page
{
    /**
     * Constructor - Loads the default values and initializes the attributes
     * @param Character $char Character Object we build this page for
     */
    function __construct($char=false)
    {
        parent::__construct($char);

        // Disable OutputModules for Popups
        $this->modulesenabled = false;

        $this->nav = new Nav(false, $this);
        // Disable Smarty Caching
        $this->_smarty->caching = 0;
    }

    /**
     * Redirect the main Window to another Location
     * Warning: No Nav-checking!
     * @param string $location The new Location
     */
    public function redirectParent($location)
    {
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
     * Load Information about the chosen Template
     */
    protected function _loadTemplateInformation()
    {
        parent::_loadTemplateInformation();

        // Assign the complete Path to the Base-Template
        $this->template['file']	= $this->template['name']. "/" . "index_popup.tpl";
    }

    /**
     * Check Navigation
     * @return bool true if successful, else false
     */
    protected function _checkNav()
    {
        // No nav-checking on popups!!!
        return true;
    }

    /**
     * Does some health-checks on the navigation and includes them into the template
     */
    protected function _generateNavigation()
    {
        $nav = "";
        $i = 0;

        foreach ($this->nav->getLinkList() as $linkitem) {
            if (!$linkitem['displayname']) {
                // Ignore Items with no Displayname
                continue;
            }

            $linktype = array_shift(explode("=", $linkitem['url']));

            if ($linktype == "popup") {
                // page-links and the linkpositions are ignored
                $nav .= "<li class='navid_".$i."'>";
                $nav .= "<a href='?".$linkitem['url']."&navid=".$i."'>".$linkitem['displayname']."</a>";
                $nav .= "</li>";

                $i++;
            }
        }

        $this->set("popupnav", $nav);
        if (isset($_GET['navid'])) {
            $this->set("navid", "navidsel_".$_GET['navid']."");
        } else {
            $this->set("navid", "navidsel_0");
        }
    }

    /**
     * Generate Stats
     */
    protected function _generateStats()
    {
        // No Stats on popups!!!
    }
}
?>
