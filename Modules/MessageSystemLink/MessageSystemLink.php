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
use Main\Controller\Page;
use Main\Manager\SystemManager;
use Modules\ModuleBase;
use Common\Interfaces\ModuleInterface;
use Common\Controller\Registry;

/**
 * MessageSystem Module
 *
 * Add the MessageSystem
 * @package Ruins
 */
class MessageSystemLink extends ModuleBase implements ModuleInterface
{
    /**
     * @see Common\Interfaces.Module::getName()
     */
    public function getName() { return "MessageSystemLink Module"; }

    /**
     * (non-PHPdoc)
     * @see Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Enables the Messenger-Link on every Page"; }

    /**
     * @see Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(Page $page)
    {
        $user = Registry::getUser();

        if ($user->character && $user->character->loggedin) {
            // add the Supportlink to the toplist
            $page->nav->addLink("Messenger","popup=popup/messenger","shared")
                      ->setDescription("Ruins Messenger zum Senden und Empfangen von Nachrichten");


            $page->addJavaScriptFile("jquery.plugin.timers.js");
            $page->addJavaScript("
                 $(document).ready(function() {
                    var timerCycle 	= '60s';
                    var result 		= 0;
                    var jsonURL		= '".SystemManager::getOverloadedFilePath("/Helpers/ajax/messenger_newMessageAlert.ajax.php", true)."?userid=".$user->character->id."';

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