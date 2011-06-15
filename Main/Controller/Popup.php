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
use Main\Manager;

/**
 * Popup Class
 *
 * Popup-Class, handles Popups and is based on Page
 * @package Ruins
 */
class Popup extends Page
{

    /**
     * Class Constants
     */
    const DEFAULT_PUBLIC_TEMPLATE    = 	"default";
    const DEFAULT_PRIVATE_TEMPLATE   = 	"default";

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
        $this->template['file']	= $this->template['name']. "/" . "index_popup.tpl";

        // Set the correct Template-Paths inside the Template
        // For Paths that are sent to the Client (relative webbased paths)
        $this->template['mytemplatedir'] = Manager\System::htmlpath($this->_smarty->template_dir . "/" . $this->template['name']);
        $this->set("mytemplatedir", $this->template['mytemplatedir']);
        $this->template['commontemplatedir'] = Manager\System::htmlpath($this->_smarty->template_dir . "/common");
        $this->set("commontemplatedir", $this->template['commontemplatedir']);

        // Paths that are handled inside the templategeneration progress (full filepaths)
        $this->template['myfulltemplatedir'] = $this->_smarty->template_dir . "/" . $this->template['name'];
        $this->set("myfulltemplatedir", $this->template['myfulltemplatedir']);

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
