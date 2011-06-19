<?php
/**
 * MessageSystem Module
 *
 * Add the MessageSystem
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Modules\MessageSystemLink;
use Main\Controller\Page,
    Main\Controller\Link,
    Main\Manager;

/**
 * MessageSystem Module
 *
 * Add the MessageSystem
 * @package Ruins
 */
class MessageSystemLink extends \Modules\ModuleBase implements \Common\Interfaces\Module
{
    /**
     * @see Common\Interfaces.Module::getModuleName()
     */
    public function getModuleName() { return "MessageSystemLink Module"; }

    /**
     * (non-PHPdoc)
     * @see Common\Interfaces.Module::getModuleDescription()
     */
    public function getModuleDescription() { return "Enables the Messenger-Link on every Page"; }

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        global $user;

        if ($user->character && $user->character->loggedin) {
            // add the Supportlink to the toplist
            $page->nav->addLink("Messenger","popup=popup/messenger","shared")
                      ->setDescription("Ruins Messenger zum Senden und Empfangen von Nachrichten");


            $page->addJavaScriptFile("jquery.plugin.timers.js");
            $page->addJavaScript("
                 $(document).ready(function() {
                    var timerCycle 	= '60s';
                    var result 		= 0;
                    var jsonURL		= '".Manager\System::htmlpath(DIR_MAIN."/Helpers/ajax/messenger_newMessageAlert.ajax.php")."?userid=".$user->character->id."';

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
}
?>