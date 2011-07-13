<?php
/**
 * Userlist
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Main\Controller\Link;
use Main\Manager\SystemManager;

/**
 * Page Content
 */
$page->set("pagetitle", "Spielerliste");
$page->set("headtitle", "Spieler von Ruins");

$page->nav->addHead("Navigation");
if (isset($_GET['return'])) {
    $page->nav->addLink("Zurück", "page=" . $_GET['return']);
} else {
    $page->output("`b`g`#25This Page needs a return-Parameter! Please fix this!");
}

$page->nav->addHead("Spielerliste")
          ->addLink("Aktualisieren", $page->url);


// Database Fields to get
$fields = array(	"displayname",
                    "level",
                    "race",
                    "profession",
                    "sex",
                    "current_nav",
                    "type"
                );

switch ($_GET['op']) {

    default:
    case "online":
        if (isset($_GET['order']) && isset($_GET['orderDir'])) {
            $charlist = $em->getRepository("Main:Character")->getList($fields, $_GET['order'], $_GET['orderDir'], true);
        } else {
            // Default to: sort by name, ascending
            $charlist = $em->getRepository("Main:Character")->getList($fields, "name", "ASC", true);
        }
        $newURL = clone $page->url;
        $newURL->setParameter("op", "all");
        $page->nav->addLink("Alle zeigen", $newURL);
        break;

    case "all":
        if (isset($_GET['order']) && isset($_GET['orderDir'])) {
            $charlist = $em->getRepository("Main:Character")->getList($fields, $_GET['order'], $_GET['orderDir']);
        } else {
            // Default to: sort by name, ascending
            $charlist = $em->getRepository("Main:Character")->getList($fields, "name");
        }
        $newURL = clone $page->url;
        $newURL->setParameter("op", "online");
        $page->nav->addLink("Spieler Online zeigen", $newURL);
        break;
}

foreach ($charlist as &$character) {
    $curnav						= explode("&", $character['current_nav']);
    $character['current_nav'] 	= SystemManager::translate($curnav[0], true);
    // We don't need the Character ID here
    unset ($character['id']);
}

// Database Fields to sort by + Headername
$headers = array(	"name"=>"Name",
                    "level"=>"Level",
                    "race"=>"Rasse",
                    "profession"=>"Beruf",
                    "sex"=>"Geschlecht",
                    "current_nav"=>"Ort",
                    "type"=>"Typ"
);

$page->addTable("characterlist", true);
$page->getTable("characterlist")->setCSS("messagelist");
$page->getTable("characterlist")->setTabAttributes(false);
$page->getTable("characterlist")->addTabHeader($headers);
$page->getTable("characterlist")->addListArray($charlist, "firstrow", "firstrow");
$page->getTable("characterlist")->setSecondRowCSS("secondrow");
$page->getTable("characterlist")->load();

// Add Navlinks for the clickable Headers
foreach ($headers as $link=>$linkname) {
    $newURL = clone $page->url;
    $newURL->setParameter("order", $link);
    if (isset($_GET['order']) && isset($_GET['orderDir'])
        && $_GET['order'] == $link && $_GET['orderDir'] == "ASC") {
        $newURL->setParameter("orderDir", "DESC");
    } else {
        $newURL->setParameter("orderDir", "ASC");
    }
    $page->nav->addHiddenLink($newURL);
}

// Make Tableheaders clickable
$page->addJavaScriptFile("jquery.plugin.query.js");

$page->addJavaScript("
    $(document).ready(function() {
        $('th').hover(function() {
            document.body.style.cursor='pointer';
        });
        $('th').mouseout(function() {
            document.body.style.cursor='default';
        });
        $('th').click(function() {
            if ($(this).attr('id') == jQuery.query.get('order') && jQuery.query.get('orderDir') == 'ASC') {
                location.href = Url.decode(jQuery.query.set('order', $(this).attr('id')).set('orderDir', 'DESC'));
            } else {
                location.href = Url.decode(jQuery.query.set('order', $(this).attr('id')).set('orderDir', 'ASC'));
            }
        });
    });
");
?>
