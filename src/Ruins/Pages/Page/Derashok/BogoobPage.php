<?php
/**
 * Thagigdash Bogoob, der Ork
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
use Ruins\Main\Manager\ItemManager;
use Ruins\Common\Controller\AbstractPageObject;

class BogoobPage extends AbstractPageObject
{
    public $title  = "Thagigdash Bogoob";

    public function createContent($page, $parameters)
    {
        $page->getNavigation()->addHead("Navigation")
                  ->addLink("Zum Zentrum", "Page/Derashok/Tribalcenter");

        $page->getNavigation()->addHead("Bogoob");

        $em = Registry::getEntityManager();

        switch ($parameters['op']) {

            default:
                $page->getNavigation()->addLink("Ihm etwas zeigen", "Page/Common/InventoryChooser?return={$page->getUrl()->getBase()}&callop=sellask");

                $page->output("Vor dir steht ein riesiger Ork mit Pranken, die locker Bäume ausreissen könnten. Und doch
                                    wirken seine Augen neugierig und keinesfalls aggressiv`n`n
                                    `#52Du habe Fisch für Bogoob?`#00 hörst du es aus seinem stinkenden Maul`n`n
                                    `#52Du mir gebe Fisch, dann ich dir gebe Glänzende, ok?`#00`n`n
                                    Meint er damit vielleicht Geld?");

                $page->output("`n`n`n");

                $page->addChat("bogoob")->show();
                break;

            case "sellask":
                if (isset($parameters['ask'])) {

                    break;
                }

                $wanttobuy 	= array();
                $fishnames	= array();
                $price = 0;

                if (is_array($parameters['chooser'])) {
                    foreach($parameters['chooser'] as $itemid) {
                        $item = ItemManager::getItem($itemid, "all");

                        if ($item->owner == $user->getCharacter()->id
                            && $item->class == "fish") {
                            $wanttobuy[] 	= $item;
                            $fishnames[]	= $item->name;
                            $price 			+= $item->value;
                        }
                    }
                }

                if (empty($wanttobuy)) {
                    $page->getNavigation()->addLink("Etwas anderes zeigen", "page/common/inventorychooser&return={$page->getUrl()->getBase()}&callop=sellask");

                    $page->output("`#52Neee, du nix haben was ich wolle... du verschwinden mit deine Krimskrams!`#00`n`n
                                    Unhöflich dreht sich der Ork von dir weg... scheinbar will er wirklich etwas anderes
                                    von dir?");
                } else {
                    $page->output("Neugierig und mit großen Augen sieht er auf das, was du ihm anbietest.`n`n
                                    `#52Oh oh, da, das isch habe wolle! Dafür ich dir {$price} gebe!`#00
                                    ruft er auf einmal aus und zeigt auf ", true);
                    if (count($wanttobuy) > 1) {
                        $page->output("die Fische vor ihm. Willst du sie ihm verkaufen?`n`n`n
                                        Fische, die er von dir verlangt:`n");
                    } else {
                        $page->output("den Fisch vor ihm. Willst du ihn ihm verkaufen?`n`n`n
                                        Der Fisch, den er von dir verlangt:`n");
                    }

                    $page->output(implode(", ", $fishnames)."`n`n");

                    $page->addForm("sell");
                    $page->getForm("sell")->head("deleteform", "page/derashok/bogoob/sell");
                    $page->getNavigation()->addHiddenLink("page/derashok/bogoob/sell");
                    $page->getForm("sell")->hidden("ids", implode(",", $parameters['chooser']));
                    $page->getForm("sell")->hidden("price", $price);
                    $page->getForm("sell")->setCSS("button");
                    $page->getForm("sell")->submitButton("Ja, weg damit!");
                    $page->getForm("sell")->close();
                }
                break;

            case "sell":
                $ids 	= explode(",", $parameters['ids']);
                $price 	= $parameters['price'];

                foreach($ids as $itemid) {
                    $item = $em->find("Main:Items\Common", $itemid);

                    $em->remove($item);
                    $em->flush();
                }

                $user->getCharacter()->money->receive($price);

                $page->getNavigation()->addLink("Ihm etwas zeigen", "page/common/inventorychooser&return={$page->getUrl()->getBase()}&callop=sellask");

                $page->output("`#52Ohhooo, heute isse schöne Tag!`#00`n`n
                                Mit diesen Worten widmet er sich ganz seiner neu erstandenen Ware und lässt dich einfach
                                links liegen.");

                break;

        }
    }
}