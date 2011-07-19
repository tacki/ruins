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
namespace Ruins\Pages\Page\Dunsplee;
use Ruins\Main\Controller\Link;
use Ruins\Main\Controller\TimerController as Timer;
use Ruins\Main\Controller\Dice;
use Ruins\Main\Entities\Items\Common;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class PondPage extends AbstractPageObject
{
    public $title  = "Dunsplee Weiher";

    public function createContent($page, $parameters)
    {
        $page->nav->addHead("Navigation");

        $em = Registry::getEntityManager();

        switch ($parameters['op']) {

            default:
                $page->output("Tief im Dunsplee Wald hast du diesen Weiher hier gefunden. Als du dich dem Gewässer näherst,
                                erkennst du einige kleine und große Fische munter umher schwimmen. Willst du es wagen?`n");
                $page->nav->addLink("Fischen", "Page/Dunsplee/Pond/fishask")
                          ->addLink("Zurück", "Page/Dunsplee/Trail");
                break;

            case "fishask":
                $page->output("Hastig siehst du dich um, doch scheinbar ist niemand in der Nähe. Schnell wird ein Wurm
                                vom Boden aufgelesen, ein Stück Faden an einer Schnur befestigt und ein gebogenes Eisenteil
                                am anderen Ende des Fadens. Wie lange willst du es wagen, hier zu sitzen und zu angeln?`n");
                $page->nav->addLink("Lieber doch nicht", "Page/Dunsplee/Pond")
                          ->addLink("1 Minute", "Page/Dunsplee/Pond/fish?time=1")
                          ->addLink("3 Minuten", "Page/Dunsplee/Pond/fish?time=3")
                          ->addLink("6 Minuten", "Page/Dunsplee/Pond/fish?time=6")
                          ->addLink("12 Minuten", "Page/Dunsplee/Pond/fish?time=12");
                break;

            case "fish":
                $timer = $em->getRepository("Main:Timer")
                            ->create("dunsplee_pond_fishing", $user->character);
                // Don't refresh the Page, show this Button instead
                $timer->useReplacementButton("Weiter...", $page->url);

                if (isset($parameters['wait']) && $showtimer = $timer->get()) {
                    $page->output("Du sitzt da und versuchst dich nicht zu rühren... irgendwann wird sicher was anbeissen.`n");
                    $page->output($showtimer, true);
                    break;
                } elseif (!isset($parameters['wait'])) {
                    $timer->set(0, $parameters['time']);
                    $page->nav->redirect("Page/Dunsplee/Pond/fish?time=".$parameters['time']."&wait=1");
                }

                $luckbonus = 0;
                switch ($parameters['time']) {
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
                    $fish = new Common;
                    $fish->name = $fishname;
                    $fish->class = "fish";
                    $fish->weight	= round($fishsize / 15); 	// 15 cm fish = 1 weight-unit? :D
                    $fish->value	= round($fishsize / 10);  	// 10 cm fish = 1 copper
                    $fish->location	= "backpack";
                    $fish->owner	= $user->character;

                    $em->persist($fish);

                    $page->nav->addLink("Ui, gleich nochmal!", "Page/Dunsplee/Pond/fishask");
                } else {
                    $page->output("Da bist du nun so lange hier herumgesessen und du hast nichts, aber auch garnichts
                                    gefangen! Was für eine Zeitverschwendung!");
                    $page->nav->addLink("Egal, nochmal!", "Page/Dunsplee/Pond/fishask");
                }

                $page->nav->addLink("Zurück", "Page/Dunsplee/Pond");

                $page->url->unsetParameter("time");
                $page->url->setParameter("op", "fishask");

                break;
        }

        $page->output("`n`n`n");

        $page->addChat("dunsplee_pond")->show();
    }
}