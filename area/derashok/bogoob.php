<?php
/**
 * Thagigdash Bogoob, der Ork
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: bogoob.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Link,
    Main\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Thagigdash Bogoob");
$page->set("headtitle", "Thagigdash Bogoob");

$page->nav->add(new Link("Navigation"));
$page->nav->add(new Link("Zum Zentrum", "page=derashok/tribalcenter"));
$page->nav->add(new Link("Bogoob"));

switch ($_GET['op']) {

    default:
        $page->nav->add(new Link("Ihm etwas zeigen", "page=common/inventorychooser&return={$page->url->short}&callop=sellask"));

        $page->output("Vor dir steht ein riesiger Ork mit Pranken, die locker Bäume ausreissen könnten. Und doch
                        wirken seine Augen neugierig und keinesfalls aggressiv`n`n
                        `#52Du habe Fisch für Bogoob?`#00 hörst du es aus seinem stinkenden Maul`n`n
                        `#52Du mir gebe Fisch, dann ich dir gebe Glänzende, ok?`#00`n`n
                        Meint er damit vielleicht Geld?");

        $page->output("`n`n`n");

        $page->addChat("bogoob");
        $page->bogoob->show();
        break;

    case "sellask":
        if (isset($_GET['ask'])) {

            break;
        }

        $wanttobuy 	= array();
        $fishnames	= array();
        $price = 0;

        if (is_array($_POST['chooser'])) {
            foreach($_POST['chooser'] as $itemid) {
                $item = Manager\Item::getItem($itemid, "all");

                if ($item->owner == $user->character->id
                    && $item->class == "fish") {
                        $wanttobuy[] 	= $item;
                        $fishnames[]	= $item->name;
                        $price 			+= $item->value;
                }
            }
        }

        if (empty($wanttobuy)) {
            $page->nav->add(new Link("Etwas anderes zeigen", "page=common/inventorychooser&return={$page->url->short}&callop=sellask"));

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

            $page->addForm("sellform", true);
            $page->sellform->head("deleteform", "page=derashok/bogoob&op=sell");
            $page->nav->add(new Link("", "page=derashok/bogoob&op=sell"));
            $page->sellform->hidden("ids", implode(",", $_POST['chooser']));
            $page->sellform->hidden("price", $price);
            $page->sellform->setCSS("button");
            $page->sellform->submitButton("Ja, weg damit!");
            $page->sellform->close();
        }
        break;

    case "sell":
        $ids 	= explode(",", $_POST['ids']);
        $price 	= $_POST['price'];

        foreach($ids as $itemid) {
            $item = $em->find("Main:Items\Common", $itemid);

            $em->remove($item);
            $em->flush();
        }

        $user->character->money->receive($price);

        $page->nav->add(new Link("Ihm etwas zeigen", "page=common/inventorychooser&return={$page->url->short}&callop=sellask"));

        $page->output("`#52Ohhooo, heute isse schöne Tag!`#00`n`n
                        Mit diesen Worten widmet er sich ganz seiner neu erstandenen Ware und lässt dich einfach
                        links liegen.");

        break;

}

?>
