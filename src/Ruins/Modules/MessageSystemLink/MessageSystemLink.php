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
namespace Ruins\Modules\MessageSystemLink;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Modules\ModuleBase;
use Ruins\Common\Interfaces\ModuleInterface;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Manager\RequestManager;

/**
 * MessageSystem Module
 *
 * Add the MessageSystem
 * @package Ruins
 */
class MessageSystemLink extends ModuleBase implements ModuleInterface
{
    /**
     * @see Ruins\Common\Interfaces.Module::getName()
     */
    public function getName() { return "MessageSystemLink Module"; }

    /**
     * (non-PHPdoc)
     * @see Ruins\Common\Interfaces.Module::getDescription()
     */
    public function getDescription() { return "Enables the Messenger-Link on every Page"; }

    /**
     * @see Ruins\Common\Interfaces.Module::prePageGeneration()
     */
    public function prePageGeneration(OutputObjectInterface $page)
    {
        $user = Registry::getUser();

        if ($user->character && $user->getCharacter()->loggedin) {
            // add the Supportlink to the toplist
            $page->getNavigation("shared")
                 ->addLink("Messenger","Popup/Messenger","Ruins Messenger zum Senden und Empfangen von Nachrichten");


            $page->addJavaScriptFile("jquery.plugin.timers.js");
            $page->addJavaScript("
                 $(document).ready(function() {
                    var timerCycle     = '60s';
                    var result         = 0;
                    var jsonURL        = '".RequestManager::getWebBasePath()."/"."Json/Messenger/NewMessageAlert"."';

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