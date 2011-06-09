<?php
/**
 * Pond inside Dunsplee Forest
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Controller\Link,
    Controller\Timer;

/**
 * Page Content
 */
$page->set("pagetitle", "Dunsplee Weiher");
$page->set("headtitle", "Ein Weiher im Dunsplee Wald");

$page->nav->add(new Link("Navigation"));

switch ($_GET['op']) {

    default:
        $page->output("Tief im Dunsplee Wald hast du diesen Weiher hier gefunden. Als du dich dem Gewässer näherst,
                        erkennst du einige kleine und große Fische munter umher schwimmen. Willst du es wagen?`n");
        $page->nav->add(new Link("Fischen", "page=dunsplee/pond&op=fishask"));
        $page->nav->add(new Link("Zurück", "page=dunsplee/trail"));
        break;

    case "fishask":
        $page->output("Hastig siehst du dich um, doch scheinbar ist niemand in der Nähe. Schnell wird ein Wurm
                        vom Boden aufgelesen, ein Stück Faden an einer Schnur befestigt und ein gebogenes Eisenteil
                        am anderen Ende des Fadens. Wie lange willst du es wagen, hier zu sitzen und zu angeln?`n");
        $page->nav->add(new Link("Lieber doch nicht", "page=dunsplee/pond"));
        $page->nav->add(new Link("1 Minute", "page=dunsplee/pond&op=fish&time=1"));
        $page->nav->add(new Link("3 Minuten", "page=dunsplee/pond&op=fish&time=3"));
        $page->nav->add(new Link("6 Minuten", "page=dunsplee/pond&op=fish&time=6"));
        $page->nav->add(new Link("12 Minuten", "page=dunsplee/pond&op=fish&time=12"));
        break;

    case "fish":
        $timer = new Timer("dunsplee_pond_fishing", $user->character);
        // Don't refresh the Page, show this Button instead
        $timer->useReplacementButton("Weiter...", $page->url);

        if (isset($_GET['wait']) && $showtimer = $timer->get()) {
            $page->output("Du sitzt da und versuchst dich nicht zu rühren... irgendwann wird sicher was anbeissen.`n");
            $page->output($showtimer, true);
            break;
        } elseif (!isset($_GET['wait'])) {
            $timer->set(0, $_GET['time']);
            $page->nav->redirect("page=dunsplee/pond&op=fish&time=".$_GET['time']."&wait=1");
        }

        $luckbonus = 0;
        switch ($_GET['time']) {
            case 3:
                $luckbonus = 2;
                break;

            case 6:
                $luckbonus = 5;
                break;

            case 12:
                $luckbonus = 12;
                break;
        }

        $catchresult 	= Dice::rollD20() + $luckbonus;
        $fishsize 		= 0;

        if ($catchresult < 8) {
            $prefix		= "den";
            $fishname 	= "Zinnfisch";
            // 8 - 36
            $fishsize = Dice::rollD10(2)+6;
        } elseif ($catchresult < 10) {
            $prefix		= "die";
            $fishname 	= "Mairenke";
            // 18 - 39
            $fishsize = Dice::rollD8(3)+15;
        } elseif ($catchresult < 14) {
            $prefix		= "den";
            $fishname 	= "Güster";
            // 23 - 44
            $fishsize = Dice::rollD8(3)+20;
        } elseif ($catchresult < 16) {
            $prefix		= "die";
            $fishname 	= "Brasse";
            // 18 - 84
            $fishsize = Dice::rollD12(6)+12;
        } elseif ($catchresult < 18) {
            $prefix		= "die";
            $fishname 	= "Schleie";
            // 24 - 69
            $fishsize = Dice::rollD6(9)+15;
        } elseif ($catchresult < 20) {
            $prefix		= "den";
            $fishname 	= "Döbel";
            // 26 - 70
            $fishsize = Dice::rollD10(5)+20;
        } elseif ($catchresult < 24) {
            $prefix		= "den";
            $fishname 	= "Aland";
            // 26 - 80
            $fishsize = Dice::rollD10(6)+20;
        } elseif ($catchresult < 26) {
            $prefix		= "die";
            $fishname 	= "Barbe";
            // 28 - 100
            $fishsize = Dice::rollD10(8)+20;
        } elseif ($catchresult < 28) {
            $prefix		= "den";
            $fishname 	= "Graskarpfen";
            // 47 - 124
            $fishsize = Dice::rollD12(7)+40;
        } elseif ($catchresult <= 30) {
            $prefix		= "den";
            $fishname 	= "Karpfen";
            // 56 - 122
            $fishsize = Dice::rollD12(6)+50;
        } else {
            $prefix		= "den";
            $fishname 	= "`#44mutierten Goldfisch (!)`#00";
            // 50 - 140
            $fishsize = Dice::rollD10(10)+40;
        }

        if ($fishsize) {
            $page->output("Du hast etwas gefangen!`n`n Stolz hältst du $prefix $fishname mit einer Länge von $fishsize cm
                            in die Höhe und freust dich deines Glückes. Zu schade dass keiner hier ist, um dich
                            zu bewundern... :(`n");

            // Insert the Fish into the Database
            global $em;

            $fish = new Entities\Items\Common;
            $fish->name = $fishname;
            $fish->class = "fish";
            $fish->weight	= round($fishsize / 15); 	// 15 cm fish = 1 weight-unit? :D
            $fish->value	= round($fishsize / 10);  	// 10 cm fish = 1 copper
            $fish->location	= "backpack";
            $fish->owner	= $user->character;

            $em->persist($fish);

/*
            $fish = new Item;
            $fish->create();
            $fish->name 	= $fishname;
            $fish->class	= "fish";
            $fish->weight	= round($fishsize / 15); 	// 15 cm fish = 1 weight-unit? :D
            $fish->value	= round($fishsize / 10);  	// 10 cm fish = 1 copper
            $fish->location	= "backpack";
            $fish->owner	= $user->char->id;
            $fish->save();
*/
            $page->nav->add(new Link("Ui, gleich nochmal!", "page=dunsplee/pond&op=fishask"));
        } else {
            $page->output("Da bist du nun so lange hier herumgesessen und du hast nichts, aber auch garnichts
                            gefangen! Was für eine Zeitverschwendung!");
            $page->nav->add(new Link("Egal, nochmal!", "page=dunsplee/pond&op=fishask"));
        }

        $page->nav->add(new Link("Zurück", "page=dunsplee/pond"));

        $page->url->unsetParameter("time");
        $page->url->setParameter("op", "fishask");

        break;
}

$page->output("`n`n`n");

$page->addChat("dunsplee_pond");
$page->dunsplee_pond->show();
?>
