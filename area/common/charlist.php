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
use Main\Controller\Link,
    Main\Manager;

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
        if (isset($_GET['order']) && isset($_GET['orderDesc'])) {
            $charlist = Manager\User::getCharacterList($fields, $_GET['order'], $_GET['orderDesc'], true);
        } else {
            // Default to: sort by name, ascending
            $charlist = Manager\User::getCharacterList($fields, "name", false, true);
        }
        $newURL = clone $page->url;
        $newURL->setParameter("op", "all");
        $page->nav->addLink("Alle zeigen", $newURL);
        break;

    case "all":
        if (isset($_GET['order']) && isset($_GET['orderDesc'])) {
            $charlist = Manager\User::getCharacterList($fields, $_GET['order'], $_GET['orderDesc']);
        } else {
            // Default to: sort by name, ascending
            $charlist = Manager\User::getCharacterList($fields, "name");
        }
        $newURL = clone $page->url;
        $newURL->setParameter("op", "online");
        $page->nav->addLink("Spieler Online zeigen", $newURL);
        break;
}

foreach ($charlist as &$character) {
    $curnav						= explode("&", $character['current_nav']);
    $character['current_nav'] 	= Manager\System::translate($curnav[0], true);
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
$page->characterlist->setCSS("messagelist");
$page->characterlist->setTabAttributes(false);
$page->characterlist->addTabHeader($headers);
$page->characterlist->addListArray($charlist, "firstrow", "firstrow");
$page->characterlist->setSecondRowCSS("secondrow");
$page->characterlist->load();

// Add Navlinks for the clickable Headers
foreach ($headers as $link=>$linkname) {
    $newURL = clone $page->url;
    $newURL->setParameter("order", $link);
    if (isset($_GET['order']) && isset($_GET['orderDesc'])
        && $_GET['order'] == $link && $_GET['orderDesc'] == 0) {
        $newURL->setParameter("orderDesc", 1);
    } else {
        $newURL->setParameter("orderDesc", 0);
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
            if ($(this).attr('id') == jQuery.query.get('order') && jQuery.query.get('orderDesc') == 0) {
                location.href = Url.decode(jQuery.query.set('order', $(this).attr('id')).set('orderDesc', 1));
            } else {
                location.href = Url.decode(jQuery.query.set('order', $(this).attr('id')).set('orderDesc', 0));
            }
        });
    });
");
?>
