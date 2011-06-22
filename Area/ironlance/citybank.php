<?php
/**
 * Ironlance City Bank
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Link,
    Main\Controller\Timer,
    Main\Layers\Money,
    Main\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Ironlance Stadtbank");
$page->set("headtitle", "Ironlance Stadtbank");

$page->nav->addHead("Navigation");

$timer = new Timer("ironlance/citybank_interest", $user->character);

if (!isset($_GET['op'])) $_GET['op']="";
switch ($_GET['op']) {

    case "":
    default:
        $page->output("Ein kleiner Mann in einem makellosen Anzug mit Lesebrille grüßt dich.`n`n");
        $page->output("\"Hallo guter Mann, mein Name ist ". $user->character->displayname .".\" grüßt du zurück, \"Kann ich meinen Kontostand an diesem wunderschönen Tag einsehen?\"`n`n");
        $page->output("Der Bankier murmelt \"Hmm, ".$user->character->displayname.", mal sehen.....\" während er die Seiten in seinem Buch ");
        $page->output("sorgfältig überfliegt.`n");

        if ($bankaccount = Manager\Banking::accountExists($user->character, "ironlance/citybank")) {
            $page->output("\"Jaa, hier steht es ja...\"`n`n");

            if ($bankaccount->balance->getPlain() > 0) {
                $page->output("Du hast ein Guthaben von `b".$bankaccount->balance->getAllCurrenciesWithPic()."`b", true);
            } elseif ($bankaccount->balance->getPlain() == 0) {
                $page->output("Du hast leider kein Guthaben auf deinem Konto... es ist absolut leer");
            } else {
                $page->output("Du schuldest der Citybank `b".$bankaccount->balance->getAllCurrenciesWithPic()."`b", true);
            }

            $page->nav->addLink("Einzahlen", "page=ironlance/citybank&op=deposit")
                      ->addLink("Abheben", "page=ironlance/citybank&op=withdraw");

            $page->nav->addHead("Zinsen");
             if ($resttime = $timer->get()) {
                $page->nav->addLink("Verfügbar in " . $resttime, $page->url);
            } else {
                $page->nav->addLink("Zinsen abholen", "page=ironlance/citybank&op=get_interest");
            }
        } else {
            $page->output("\"Tut mir sehr leid, aber Ihren Namen kann ich hier nicht finden.\" Er sieht zu dir herauf \"Wollen sie vielleicht ein Konto eröffnen?\"");
            $page->nav->addLink("Konto eröffnen", "page=ironlance/citybank&op=openaccount");
        }
        break;

    case "openaccount":
        $page->output("Das Eröffnen kostet dich 10 Kupferstücke! Doch keine Angst, diese werden gleich auf das Konto eingezahlt.");
        $page->nav->addLink("Ja, das will ich!", "page=ironlance/citybank&op=openaccount2")
                  ->addLink("Nein, lieber doch nicht", "page=ironlance/citybank");
        break;

    case "openaccount2":
        if ($user->character->money->getPlain() >= 10) {
            $user->character->money->pay(10, "copper");
            Manager\Banking::createAccount($user->character, "ironlance/citybank");
            Manager\Banking::deposit($user->character, "ironlance/citybank", 10);
            // Set initial Interest Cycle
            $timer->set($systemConfig->get("ironlance/citybank_interestcycle", 86400));
            $page->output("Vielen Dank, das Geld ist bei uns natürlich seeehr sicher *hust* ;)");
        } else {
            $page->output("Oh, so viel Geld scheinst du garnicht zu haben?");
        }

        $page->nav->addLink("Zurück", "page=ironlance/citybank");
        break;

    case "deposit":
        $page->output("Wieviel willst du denn einzahlen?");
        $page->addForm("deposit");
        $page->nav->addHiddenLink("page=ironlance/citybank&op=deposit2");
        $page->getForm("deposit")->head("depositform", "page=ironlance/citybank&op=deposit2");

        $page->getForm("deposit")->setCSS("moneyform_gold");
        $page->getForm("deposit")->inputText("gold", 0, false, 2);

        $page->getForm("deposit")->setCSS("moneyform_silver");
        $page->getForm("deposit")->inputText("silver", 0, false, 2);

        $page->getForm("deposit")->setCSS("moneyform_copper");
        $page->getForm("deposit")->inputText("copper", 0, false, 2);

        $page->getForm("deposit")->setCSS("button");
        $page->getForm("deposit")->submitButton("Einzahlen");

        $page->getForm("deposit")->close();
        $page->nav->addLink("Zurück", "page=ironlance/citybank");
        break;

    case "deposit2":
        $temp_wallet = new Money();

        $temp_wallet->receive(abs($_POST['gold']), "gold");
        $temp_wallet->receive(abs($_POST['silver']), "silver");
        $temp_wallet->receive(abs($_POST['copper']), "copper");

        if ($user->character->money->getPlain() >= $temp_wallet->getPlain()) {
            $user->character->money->pay($temp_wallet);
            Manager\Banking::deposit($user->character, "ironlance/citybank", $temp_wallet);
            $page->output("`b".$temp_wallet->getAllCurrenciesWithPic()."`b eingezahlt", true);
        } else {
            $page->output("So viel Geld hast du nicht");
            $page->nav->addLink("Zurück", "page=ironlance/citybank&op=deposit");
            break;
        }
        unset($temp_wallet);
        $page->nav->addLink("Zurück", "page=ironlance/citybank");
        break;

    case "withdraw":
        $page->output("Wieviel willst du denn abheben?");
        $page->addForm("withdraw");
        $page->nav->addHiddenLink("page=ironlance/citybank&op=withdraw2");
        $page->getForm("withdraw")->head("withdrawform", "page=ironlance/citybank&op=withdraw2");

        $page->getForm("withdraw")->setCSS("moneyform_gold");
        $page->getForm("withdraw")->inputText("gold", 0, false, 2);

        $page->getForm("withdraw")->setCSS("moneyform_silver");
        $page->getForm("withdraw")->inputText("silver", 0, false, 2);

        $page->getForm("withdraw")->setCSS("moneyform_copper");
        $page->getForm("withdraw")->inputText("copper", 0, false, 2);

        $page->getForm("withdraw")->setCSS("button");
        $page->getForm("withdraw")->submitButton("Abheben");

        $page->getForm("withdraw")->close();
        $page->nav->addLink("Zurück", "page=ironlance/citybank");
        break;

    case "withdraw2":
        $temp_wallet = new Money();

        $temp_wallet->receive(abs($_POST['gold']), "gold");
        $temp_wallet->receive(abs($_POST['silver']), "silver");
        $temp_wallet->receive(abs($_POST['copper']), "copper");

        if (Manager\Banking::getBalance($user->character, "ironlance/citybank")->getPlain() >= $temp_wallet->getPlain()) {
            $user->character->money->receive($temp_wallet);
            Manager\Banking::withdraw($user->character, "ironlance/citybank", $temp_wallet);
            $page->output("`b".$temp_wallet->getAllCurrenciesWithPic()."`b abgehoben", true);
        } else {
            $page->output("So viel Geld hast du nicht auf deinem Konto");
            $page->nav->addLink("Zurück", "page=ironlance/citybank&op=withdraw");
            break;
        }
        $page->nav->addLink("Zurück", "page=ironlance/citybank");
        break;

    case "get_interest":
        $interest = Manager\Banking::chargeInterest($user->character, "ironlance/citybank");
        // set new interest cycle (defaults to 24h)
        $timer->set($systemConfig->get("ironlance/citybank_interestcycle", 86400));

        $page->output($interest->getAllCurrenciesWithPic() . " an Zinsen erhalten", true);
        $page->nav->addLink("Zurück", "page=ironlance/citybank");
        break;
}

$page->nav->addHead("Allgemein")
          ->addLink("Zurück zum Zentrum", "page=ironlance/citysquare");
?>
