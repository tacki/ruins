<?php
/**
 * Ironlance City Bank
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: citybank.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Page Content
 */
$page->set("pagetitle", "Ironlance Stadtbank");
$page->set("headtitle", "Ironlance Stadtbank");

$page->nav->add(new Link("Navigation"));

$timer = new Timer("ironlance/citybank_interest", $user->char);

if (!isset($_GET['op'])) $_GET['op']="";
switch ($_GET['op']) {

    case "":
    default:
        $page->output("Ein kleiner Mann in einem makellosen Anzug mit Lesebrille grüßt dich.`n`n");
        $page->output("\"Hallo guter Mann, mein Name ist ". $user->char->displayname .".\" grüßt du zurück, \"Kann ich meinen Kontostand an diesem wunderschönen Tag einsehen?\"`n`n");
        $page->output("Der Bankier murmelt \"Hmm, ".$user->char->displayname.", mal sehen.....\" während er die Seiten in seinem Buch ");
        $page->output("sorgfältig überfliegt.`n");

        if (BankingSystem::accountExists($user->char, "ironlance/citybank")) {
            $page->output("\"Jaa, hier steht es ja...\"`n`n");

            $balance = BankingSystem::getBalance($user->char, "ironlance/citybank");
            ModuleSystem::enableManagerModule($balance, "Money");

            if ($balance->detailed() >= 0) {
                $page->output("Du hast ein Guthaben von `b".$balance->fullDetailedWithPic()."`b", true);
            } else {
                $page->output("Du schuldest der Citybank `b".$balance."`b");
            }

            $page->nav->add(new Link("Einzahlen", "page=ironlance/citybank&op=deposit"));
            $page->nav->add(new Link("Abheben", "page=ironlance/citybank&op=withdraw"));

            $page->nav->add(new Link("Zinsen"));
             if ($resttime = $timer->get()) {
                $page->nav->add(new Link("Verfügbar in " . $resttime, $page->url));
            } else {
                $page->nav->add(new Link("Zinsen abholen", "page=ironlance/citybank&op=get_interest"));;
            }
        } else {
            $page->output("\"Tut mir sehr leid, aber Ihren Namen kann ich hier nicht finden.\" Er sieht zu dir herauf \"Wollen sie vielleicht ein Konto eröffnen?\"");
            $page->nav->add(new Link("Konto eröffnen", "page=ironlance/citybank&op=openaccount"));
        }
        break;

    case "openaccount":
        $page->output("Das Eröffnen kostet dich 10 Kupferstücke! Doch keine Angst, diese werden gleich auf das Konto eingezahlt.");
        $page->nav->add(new Link("Ja, das will ich!", "page=ironlance/citybank&op=openaccount2"));
        $page->nav->add(new Link("Nein, lieber doch nicht", "page=ironlance/citybank"));
        break;

    case "openaccount2":
        if ($user->char->money->detailed() >= 10) {
            $user->char->money->pay(10, "copper");
            BankingSystem::createAccount($user->char, "ironlance/citybank");
            BankingSystem::deposit($user->char, "ironlance/citybank", 10);
            // Set initial Interest Cycle
            $timer->set($config->get("ironlance/citybank_interestcycle", 86400));
            $page->output("Vielen Dank, das Geld ist bei uns natürlich seeehr sicher *hust* ;)");
        } else {
            $page->output("Oh, so viel Geld scheinst du garnicht zu haben?");
        }

        $page->nav->add(new Link("Zurück", "page=ironlance/citybank"));
        break;

    case "deposit":
        $page->output("Wieviel willst du denn einzahlen?");
        $page->addForm("depositform");
        $page->nav->add(new Link("", "page=ironlance/citybank&op=deposit2"));
        $page->depositform->head("depositform", "page=ironlance/citybank&op=deposit2");

        $page->depositform->setCSS("moneyform_gold");
        $page->depositform->inputText("gold", 0, false, 2);

        $page->depositform->setCSS("moneyform_silver");
        $page->depositform->inputText("silver", 0, false, 2);

        $page->depositform->setCSS("moneyform_copper");
        $page->depositform->inputText("copper", 0, false, 2);

        $page->depositform->setCSS("button");
        $page->depositform->submitButton("Einzahlen");

        $page->depositform->close();
        $page->nav->add(new Link("Zurück", "page=ironlance/citybank"));
        break;

    case "deposit2":
        $temp_wallet = 0;
        ModuleSystem::enableManagerModule($temp_wallet, "Money");

        $temp_wallet->receive(abs($_POST['gold']), "gold");
        $temp_wallet->receive(abs($_POST['silver']), "silver");
        $temp_wallet->receive(abs($_POST['copper']), "copper");

        if ($user->char->money->detailed() >= $temp_wallet->detailed()) {
            $user->char->money->pay($temp_wallet);
            BankingSystem::deposit($user->char, "ironlance/citybank", $temp_wallet);
            $page->output("`b".$temp_wallet->fullDetailedWithPic()."`b eingezahlt", true);
        } else {
            $page->output("So viel Geld hast du nicht");
            $page->nav->add(new Link("Zurück", "page=ironlance/citybank&op=deposit"));
            break;
        }
        $page->nav->add(new Link("Zurück", "page=ironlance/citybank"));
        break;

    case "withdraw":
        $page->output("Wieviel willst du denn abheben?");
        $page->addForm("withdrawform");
        $page->nav->add(new Link("", "page=ironlance/citybank&op=withdraw2"));
        $page->withdrawform->head("withdrawform", "page=ironlance/citybank&op=withdraw2");

        $page->withdrawform->setCSS("moneyform_gold");
        $page->withdrawform->inputText("gold", 0, false, 2);

        $page->withdrawform->setCSS("moneyform_silver");
        $page->withdrawform->inputText("silver", 0, false, 2);

        $page->withdrawform->setCSS("moneyform_copper");
        $page->withdrawform->inputText("copper", 0, false, 2);

        $page->withdrawform->setCSS("button");
        $page->withdrawform->submitButton("Abheben");

        $page->withdrawform->close();
        $page->nav->add(new Link("Zurück", "page=ironlance/citybank"));
        break;

    case "withdraw2":
        $temp_wallet = 0;
        ModuleSystem::enableManagerModule($temp_wallet, "Money");

        $temp_wallet->receive(abs($_POST['gold']), "gold");
        $temp_wallet->receive(abs($_POST['silver']), "silver");
        $temp_wallet->receive(abs($_POST['copper']), "copper");

        if (BankingSystem::getBalance($user->char, "ironlance/citybank") >= $temp_wallet->detailed()) {
            $user->char->money->receive($temp_wallet);
            BankingSystem::withdraw($user->char, "ironlance/citybank", $temp_wallet);
            $page->output("`b".$temp_wallet->fullDetailedWithPic()."`b abgehoben", true);
        } else {
            $page->output("So viel Geld hast du nicht");
            $page->nav->add(new Link("Zurück", "page=ironlance/citybank&op=withdraw"));
            break;
        }
        $page->nav->add(new Link("Zurück", "page=ironlance/citybank"));
        break;

    case "get_interest":
        $interest = BankingSystem::chargeInterest($user->char, "ironlance/citybank");
        // set new interest cycle (defaults to 24h)
        $timer->set($config->get("ironlance/citybank_interestcycle", 86400));

        $page->output($interest->fullDetailedWithPic() . " an Zinsen erhalten", true);
        $page->nav->add(new Link("Zurück", "page=ironlance/citybank"));
        break;
}

$page->nav->add(new Link("Allgemein"));
$page->nav->add(new Link("Zurück zum Zentrum", "page=ironlance/citysquare"));
?>
