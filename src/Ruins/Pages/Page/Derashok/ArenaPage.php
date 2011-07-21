<?php
/**
 * PvP Kampfarena
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Derashok;
use Ruins\Main\Controller\Link;
use Ruins\Common\Controller\Registry;
use Ruins\Main\Controller\BattleController;
use Ruins\Common\Controller\AbstractPageObject;

class ArenaPage extends AbstractPageObject
{
    public $title  = "Derashok Kampfarena";

    public function createContent($page, $parameters)
    {
        $page->getNavigation()->addHead("Navigation")
                  ->addLink("Aktualisieren", $page->getUrl());

        $battle = new BattleController;
        $em = Registry::getEntityManager();

        if ($em->getRepository("Main:Character")->getBattle($user->character)) {
            include (DIR_MAIN."Helpers/battle.running.php");
        } elseif ($em->getRepository("Main:Battle")->getList()) {
            $page->getNavigation()->addLink("Zurück", "Page/Derashok/Tribalcenter");
            include (DIR_MAIN."Helpers/battle.list.php");
        } else {
            $page->getNavigation()->addLink("Zurück", "Page/Derashok/Tribalcenter");

            $page->output("Zur Zeit läuft kein Kampf! Willst du einen provozieren?");
            $battle->addCreateBattleNav();
        }
    }
}