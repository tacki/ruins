<?php
/**
 * Inventory Chooser
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: inventorychooser.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Page Content
 */
$page->set("pagetitle", "Inventar");
$page->set("headtitle", "Inventar");

$page->nav->add(new Link("Navigation"));
if (isset($_GET['return'])) {
    $page->nav->add(new Link("Zurück", "page=" . $_GET['return']));
} else {
    $page->output("`b`g`#25This Page needs a return-Parameter! Please fix this!");
}

$page->nav->add(new Link("Inventar"));

switch ($_GET['op']) {

    default:
    case "all":
        // Show the complete Inventory
        if (isset($_GET['order']) && isset($_GET['orderDesc'])) {
            $itemlist 	= Manager\Item::getInventoryList($user->character, "all", false, $_GET['order'], $_GET['orderDesc']);
        } else {
            $itemlist 	= Manager\Item::getInventoryList($user->character, "all");
        }

        $newURL = clone $page->url;
        $newURL->setParameter("op", "backpack");
        $page->nav->add(new Link("Rucksack", $newURL));
        break;

    case "backpack":
        // Only show the backpack
        if (isset($_GET['order']) && isset($_GET['orderDesc'])) {
            $itemlist 	= Manager\Item::getInventoryList($user->character, "backpack", false, $_GET['order'], $_GET['orderDesc']);
        } else {
            $itemlist 	= Manager\Item::getInventoryList($user->character, "backpack");
        }

        $newURL = clone $page->url;
        $newURL->setParameter("op", "all");
        $page->nav->add(new Link("Alle zeigen", $newURL));
        break;

}

// The callop-attribute is a op-value that is called after the items are chosen.
// Example:
//	$_GET['return'] = ironlance/merchant
//	$_GET['callop'] = sell
// Results into call: ?page=ironlance/merchant&op=sell
if (isset($_GET['callop'])) {
    $callbackpage = "page=".$_GET['return']."&op=".$_GET['callop'];
    $page->addForm("chooser", true);
    $page->chooser->head("chooser", $callbackpage);
    $page->nav->add(new Link("", $callbackpage));
}

if (is_array($itemlist)) {
    $newItemList = array();
    foreach ($itemlist as &$item) {
        $showItem = array();

        $showItem['name'] = $item->name;
        $showItem['class'] = $item->class;
        $showItem['level'] = $item->level;
        $showItem['requirement'] = $item->requirement;
        $showItem['weight'] = $item->weight;
        $showItem['value'] = $item->value;
        $showItem['location']  = $item->location;

        if (isset($_GET['callop'])) {
            $showItem['select']		= "<input type='checkbox' name='chooser[]' value='".$item->id."'>";
        }

        $newItemList[] = $showItem;
    }
} else {
    $newItemList = array();
}

// Database Fields to sort by + Headername
$headers = array(	"name"=>"Name",
                    "class"=>"Klasse",
                    "level"=>"Level",
                    "requirement"=>"Anforderung",
                    "weight"=>"Gewicht",
                    "value"=>"Wert",
                    "location"=>"Ort"
);

$page->addTable("itemlist_armors", true);
$page->itemlist_armors->setCSS("messagelist");
$page->itemlist_armors->setTabAttributes(false);
$page->itemlist_armors->addTabHeader($headers);
$page->itemlist_armors->addListArray($newItemList, "firstrow", "firstrow");
$page->itemlist_armors->setSecondRowCSS("secondrow");
$page->itemlist_armors->load();

if (isset($_GET['callop'])) {
    $page->chooser->setCSS("delbutton");
    $page->chooser->submitButton("Weiter");
    $page->chooser->close();
}

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
    $page->nav->add(new Link("", $newURL));
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
