<?php
/**
 * Travel Gateway
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Ruins\Main\Controller\Link;
use Ruins\Main\Controller\Travel;
use Ruins\Main\Controller\TimerController as Timer;
use Ruins\Common\Controller\Error;

/**
 * Page Content
 */
$page->set("pagetitle", "Reisen");
$page->set("headtitle", "Reisen");

$page->nav->addHead("Ruins");

$timer = $em->getRepository("Main:Timer")
            ->create("travelTimer", $user->character);
$travel = new Travel;

$curSite = $em->getRepository("Main:Site")->findOneByName($_GET['return']);

if (!$curSite) throw new Error("Starting Site " . $_GET['return'] . " not found!");


switch ($_GET['op']) {

    default:
        if (isset($_GET['return'])) {
            $page->nav->addLink("Zurück", "page=" . $_GET['return']);
        } else {
            $page->output("`b`g`#25This Page needs a return-Parameter! Please fix this!`n");
            $page->nav->addLink("Zurück", "page=ironlance/citysquare");
        }

        $page->output("Wohin willst du denn reisen?`n`n");

        $page->addForm("travel");
        $newURL = clone $page->url;
        $newURL->setParameter("op", "travel");
        $newURL->setParameter("return", $_GET['return']);
        $page->getForm("travel")->head("travelform", $newURL. "");
        $page->nav->addHiddenLink($newURL);

        $connections = $travel->getConnectedSites($curSite);

        foreach ($connections as $connection) {
            $page->getForm("travel")->radio("travelto", $connection->name);
            $page->output($connection->description . "`n`n");
        }
/*
        $page->getForm("travel")->radio("travelto", "derashok/tribalcenter");
        $page->output("Derashok Stammeszentrum - Der wichtigste Treffpunkt der orkischen Clans`n`n");

        $page->getForm("travel")->radio("travelto", "ironlance/citysquare");
        $page->output("Ironlance Stadtplatz - Der Platz mitten in Ironlance, dem Stolz der Menschen`n`n");

        $page->getForm("travel")->radio("travelto", "dunsplee/trail");
        $page->output("Dunsplee Wald - Weg zum sagenumwobenen Wald`n`n");
*/
        $page->getForm("travel")->submitButton("Reise beginnen");

        $page->getForm("travel")->close();
        break;

    case "travel":
        if ($showtimer = $timer->get()) {
            // Page reload as long as the Timer is
            $page->output("Ankunft in: " . $showtimer, true);
        } elseif (!$timer->get() && isset($_GET['redirect'])) {
            // Redirect to the new Page
            $newURL = clone $page->url;
            $newURL->unsetParameter("redirect");
            $newURL->unsetParameter("travelto");
            $newURL->unsetParameter("op");
            $newURL->unsetParameter("return");
            $newURL->setParameter("page", $_GET['redirect']);
            $page->nav->addHiddenLink($newURL);
            $page->nav->redirect($newURL);
        } elseif (!$timer->get() && isset($_POST['travelto'])) {
            // First Contact to the Travelpage and every Reload
            $trgtSite = $em->getRepository("Main:Site")->findOneByName($_POST['travelto']);
            if (!$trgtSite) throw new Error("Target Site " . $_POST['travelto'] . " not found!");

            $time = $travel->calcDistance($curSite->waypoint, $trgtSite->waypoint);
            $timer->set($time);
            $newURL = clone $page->url;
            $newURL->setParameter("return", $_GET['return']);
            $newURL->setParameter("redirect", $_POST['travelto']);
            $page->nav->addHiddenLink($newURL);
            $page->nav->redirect($newURL);
        } elseif (!$timer->get() && !isset($_POST['travelto'])) {
            // No target chosen, return to select-screen
            $page->url->unsetParameter("op");
            $page->nav->redirect($page->url);
        }
        break;

}

$page->nav->addLink("Aktualisieren", $page->url);

?>
