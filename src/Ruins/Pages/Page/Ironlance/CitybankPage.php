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
namespace Ruins\Pages\Page\Ironlance;
use Ruins\Main\Controller\Link;
use Ruins\Main\Controller\TimerController as Timer;
use Ruins\Main\Layers\Money;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class CitybankPage extends AbstractPageObject
{
    public $title  = "Ironlance Stadtbank";

    public function createContent($page, $parameters)
    {
        $user = $this->getUser();

        $page->nav->addHead("Navigation");

        $timer = $this->getEntityManager()->getRepository("Main:Timer")
                      ->create("ironlance/citybank_interest", $user->character);

        $bankRepos   = $this->getEntityManager()->getRepository("Main:Bank");
        $bankAccount = $bankRepos->getAccount($user->character, "ironlance/citybank");

        $systemConfig = Registry::getMainConfig();

        switch ($parameters['op']) {
            case "":
            default:
                $page->output("Ein kleiner Mann in einem makellosen Anzug mit Lesebrille grüßt dich.`n`n");
                $page->output("\"Hallo guter Mann, mein Name ist ". $user->character->displayname .".\" grüßt du zurück, \"Kann ich meinen Kontostand an diesem wunderschönen Tag einsehen?\"`n`n");
                $page->output("Der Bankier murmelt \"Hmm, ".$user->character->displayname.", mal sehen.....\" während er die Seiten in seinem Buch ");
                $page->output("sorgfältig überfliegt.`n");

                if ($bankAccount) {
                    $page->output("\"Jaa, hier steht es ja...\"`n`n");

                    if ($bankAccount->balance->getPlain() > 0) {
                        $page->output("Du hast ein Guthaben von `b".$bankAccount->balance->getAllCurrenciesWithPic()."`b", true);
                    } elseif ($bankAccount->balance->getPlain() == 0) {
                        $page->output("Du hast leider kein Guthaben auf deinem Konto... es ist absolut leer");
                    } else {
                        $page->output("Du schuldest der Citybank `b".$bankAccount->balance->getAllCurrenciesWithPic()."`b", true);
                    }

                    $page->nav->addLink("Einzahlen", "Page/Ironlance/Citybank/deposit")
                              ->addLink("Abheben", "Page/Ironlance/Citybank/withdraw");

                    $page->nav->addHead("Zinsen");
                    if ($resttime = $timer->get()) {
                        $page->nav->addLink("Verfügbar in " . $resttime, $page->url);
                    } else {
                        $page->nav->addLink("Zinsen abholen", "Page/Ironlance/Citybank/get_interest");
                    }
                } else {
                    $page->output("\"Tut mir sehr leid, aber Ihren Namen kann ich hier nicht finden.\" Er sieht zu dir herauf \"Wollen sie vielleicht ein Konto eröffnen?\"");
                    $page->nav->addLink("Konto eröffnen", "Page/Ironlance/Citybank/openaccount");
                }
                break;

            case "openaccount":
                $page->output("Das Eröffnen kostet dich 10 Kupferstücke! Doch keine Angst, diese werden gleich auf das Konto eingezahlt.");
                $page->nav->addLink("Ja, das will ich!", "Page/Ironlance/Citybank/openaccount2")
                          ->addLink("Nein, lieber doch nicht", "Page/Ironlance/Citybank");
                break;

            case "openaccount2":
                if ($user->character->money->getPlain() >= 10) {
                    $user->character->money->pay(10, "copper");
                    $bankRepos->createAccount($user->character, "ironlance/citybank");
                    $bankRepos->deposit($user->character, "ironlance/citybank", 10);
                    // Set initial Interest Cycle
                    $timer->set($systemConfig->get("ironlance/citybank_interestcycle", 86400));
                    $page->output("Vielen Dank, das Geld ist bei uns natürlich seeehr sicher *hust* ;)");
                } else {
                    $page->output("Oh, so viel Geld scheinst du garnicht zu haben?");
                }

                $page->nav->addLink("Zurück", "Page/Ironlance/Citybank");
                break;

            case "deposit":
                $page->output("Wieviel willst du denn einzahlen?");
                $page->addForm("deposit");
                $page->nav->addHiddenLink("Page/Ironlance/Citybank/deposit2");
                $page->getForm("deposit")->head("depositform", "Page/Ironlance/Citybank/deposit2");

                $page->getForm("deposit")->setCSS("moneyform_gold");
                $page->getForm("deposit")->inputText("gold", 0, false, 2);

                $page->getForm("deposit")->setCSS("moneyform_silver");
                $page->getForm("deposit")->inputText("silver", 0, false, 2);

                $page->getForm("deposit")->setCSS("moneyform_copper");
                $page->getForm("deposit")->inputText("copper", 0, false, 2);

                $page->getForm("deposit")->setCSS("button");
                $page->getForm("deposit")->submitButton("Einzahlen");

                $page->getForm("deposit")->close();
                $page->nav->addLink("Zurück", "Page/Ironlance/Citybank");
                break;

            case "deposit2":
                $temp_wallet = new Money();

                $temp_wallet->receive(abs($_POST['gold']), "gold");
                $temp_wallet->receive(abs($_POST['silver']), "silver");
                $temp_wallet->receive(abs($_POST['copper']), "copper");

                if ($user->character->money->getPlain() >= $temp_wallet->getPlain()) {
                    $user->character->money->pay($temp_wallet);
                    $bankRepos->deposit($user->character, "ironlance/citybank", $temp_wallet);
                    $page->output("`b".$temp_wallet->getAllCurrenciesWithPic()."`b eingezahlt", true);
                } else {
                    $page->output("So viel Geld hast du nicht");
                    $page->nav->addLink("Zurück", "Page/Ironlance/Citybank/deposit");
                    break;
                }
                unset($temp_wallet);
                $page->nav->addLink("Zurück", "Page/Ironlance/Citybank");
                break;

            case "withdraw":
                $page->output("Wieviel willst du denn abheben?");
                $page->addForm("withdraw");
                $page->nav->addHiddenLink("Page/Ironlance/Citybank/withdraw2");
                $page->getForm("withdraw")->head("withdrawform", "Page/Ironlance/Citybank/withdraw2");

                $page->getForm("withdraw")->setCSS("moneyform_gold");
                $page->getForm("withdraw")->inputText("gold", 0, false, 2);

                $page->getForm("withdraw")->setCSS("moneyform_silver");
                $page->getForm("withdraw")->inputText("silver", 0, false, 2);

                $page->getForm("withdraw")->setCSS("moneyform_copper");
                $page->getForm("withdraw")->inputText("copper", 0, false, 2);

                $page->getForm("withdraw")->setCSS("button");
                $page->getForm("withdraw")->submitButton("Abheben");

                $page->getForm("withdraw")->close();
                $page->nav->addLink("Zurück", "Page/Ironlance/Citybank");
                break;

            case "withdraw2":
                $temp_wallet = new Money();

                $temp_wallet->receive(abs($_POST['gold']), "gold");
                $temp_wallet->receive(abs($_POST['silver']), "silver");
                $temp_wallet->receive(abs($_POST['copper']), "copper");

                if ($bankAccount->balance->getPlain() >= $temp_wallet->getPlain()) {
                    $user->character->money->receive($temp_wallet);
                    $bankRepos->withdraw($user->character, "ironlance/citybank", $temp_wallet);
                    $page->output("`b".$temp_wallet->getAllCurrenciesWithPic()."`b abgehoben", true);
                } else {
                    $page->output("So viel Geld hast du nicht auf deinem Konto");
                    $page->nav->addLink("Zurück", "Page/Ironlance/Citybank/withdraw");
                    break;
                }
                $page->nav->addLink("Zurück", "Page/Ironlance/Citybank");
                break;

            case "get_interest":
                $interest = $bankRepos->chargeInterest($user->character, "ironlance/citybank");
                // set new interest cycle (defaults to 24h)
                $timer->set($systemConfig->get("ironlance/citybank_interestcycle", 86400));

                $page->output($interest->getAllCurrenciesWithPic() . " an Zinsen erhalten", true);
                $page->nav->addLink("Zurück", "Page/Ironlance/Citybank");
                break;
            }

        $page->nav->addHead("Allgemein")
                  ->addLink("Zurück zum Zentrum", "Page/Ironlance/Citysquare");
    }
}