<?php
/**
 * Messengerlink Module
 *
 * Add a Messengerlink to every Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: messengerlink.mod.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Messengerlink Module
 *
 * Add a Messengerlink to every Page
 * @package Ruins
 */
class MessengerLink extends Output
{
    /**
     * Module Name
     * @see includes/classes/Module#getModuleName()
     */
    public function getModuleName() { return "Messengerlink Module"; }

    /**
     * Module Description
     * @see includes/classes/Module#getModuleDescription()
     */
    public function getModuleDescription() { return "Module to add a Messenger Link to each Page"; }

    /**
     * Module Initialization
     */
    public function init()
    {
        // Initialize Parent
        parent::init();

        global $user;

        if (isset($user->char) && $user->char->loggedin) {
            $this->outputObject->addJavaScriptFile("jquery.plugin.timers.js");
            $this->outputObject->addJavaScript("
                 $(document).ready(function() {
                    var timerCycle 	= '60s';
                    var result 		= 0;
                    var jsonURL		= 'includes/helpers/ajax/messenger_newMessageAlert.ajax.php?userid=".$user->char->id."';

                    $.getJSON(jsonURL, function(json) {
                        if (json > 0) {
                            $(\"a[title=Messenger]\").append(\" <font color='red'>(new)</font>\");
                            $(document).stopTime('MessageChecker');
                        }
                    });

                    $(document).everyTime(timerCycle, 'MessageChecker' ,function() {

                        $.getJSON(jsonURL, function(json) {
                            if (json > 0) {
                                $(\"a[title=Messenger]\").append(\" <font color='red'>(new)</font>\");
                                $(document).stopTime('MessageChecker');
                            }
                        });

                    }, 0);
                });
            ");
        }
    }

    /**
     * Call Navigation Module
     * @param Nav $nav The Navigation-Object
     */
    public function callNavModule(Nav &$nav)
    {
        global $user;

        if (isset($user) && $user->loggedin) {
            // add the Supportlink to the toplist
            $nav->add(new Link("Messenger",
                                "popup=popup/messenger",
                                "shared",
                                "Ruins Messenger zum Senden und Empfangen von Nachrichten"));
        }
    }

    /**
     * Call Text Module
     * @param array $body The Content of the Page-Body
     */
    public function callTextModule(&$body)
    {
    }

}
?>
