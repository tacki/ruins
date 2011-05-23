<?php
/**
 * Travel Gateway
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id$
 * @package Ruins
 */

/**
 * Page Content
 */
$page->set("pagetitle", "Reisen");
$page->set("headtitle", "Reisen");

$page->nav->add(new Link("Ruins"));

$timer = new Timer("travelTimer", $user->char);

switch ($_GET['op']) {

    default:
        if (isset($_GET['return'])) {
            $page->nav->add(new Link("Zurück", "page=" . $_GET['return']));
        } else {
            $page->output("`b`g`#25This Page needs a return-Parameter! Please fix this!`n");
            $page->nav->add(new Link("Zurück", "page=ironlance/citysquare"));
        }

        $page->output("Wohin willst du denn reisen?`n`n");

        $page->addForm("travelform", true);
        $newURL = clone $page->url;
        $newURL->setParameter("op", "travel");
        $page->travelform->head("travelform", $newURL. "");
        $page->nav->add(new Link("", $newURL));

        $page->travelform->radio("travelto", "derashok/tribalcenter");
        $page->output("Derashok Stammeszentrum - Der wichtigste Treffpunkt der orkischen Clans`n`n");

        $page->travelform->radio("travelto", "ironlance/citysquare");
        $page->output("Ironlance Stadtplatz - Der Platz mitten in Ironlance, dem Stolz der Menschen`n`n");

        $page->travelform->radio("travelto", "dunsplee/trail");
        $page->output("Dunsplee Wald - Weg zum sagenumwobenen Wald`n`n");

        $page->travelform->submitButton("Reise beginnen");

        $page->travelform->close();
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
            $newURL->setParameter("page", $_GET['redirect']);
            $page->nav->add(new Link("", $newURL));
            $page->nav->redirect($newURL);
        } elseif (!$timer->get() && isset($_POST['travelto'])) {
            // First Contact to the Travelpage and every Reload
            $timer->set(10);
            $newURL = clone $page->url;
            $newURL->unsetParameter("return");
            $newURL->setParameter("redirect", $_POST['travelto']);
            $page->nav->add(new Link("", $newURL));
            $page->nav->redirect($newURL);
        } elseif (!$timer->get() && !isset($_POST['travelto'])) {
            // No target chosen, return to select-screen
            $page->url->unsetParameter("op");
            $page->nav->redirect($page->url);
        }
        break;

}

$page->nav->add(new Link("Aktualisieren", $page->url));

?>
