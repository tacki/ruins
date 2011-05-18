<?php
/**
 * Inventory
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: equipment.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Page Content
 */
$page->set("pagetitle", "Ausrüstung");
$page->set("headtitle", "Ausrüstung");

$page->nav->add(new Link("Navigation"));
if (isset($_GET['return'])) {
    $page->nav->add(new Link("Zurück", "page=" . $_GET['return']));
} else {
    $page->output("`b`g`25This Page needs a return-Parameter! Please fix this!");
}
$page->nav->add(new Link("Aktualisieren", $page->url));

// --------
$page->addJavaScript("
    $(function() {

        var elements = ['weapon','armor_head','armor_chest','armor_arms','armor_legs','armor_feet'];

        jQuery.each (elements, function () {
            $('#'+this+'_equipped').val($('#'+this+' div.equippedbox p.itemid').text());
        });

        $('.equippedbox').dblclick(function () {
            $(this).attr('class', 'equippedbox');
              $(this).text('');

             jQuery.each (elements, function () {
                $('#'+this+'_equipped').val($('#'+this+' div.equippedbox p.itemid').text());
            });
        });

        $('.backpackbox').draggable({
            addClasses: false,
            revert: true,
            revertDuration: 0,
            drag: function() {
                $(this).addClass('backpackboxdrag');
            },
            stop: function() {
                $(this).removeClass('backpackboxdrag');
            },
        });

        $('.equippedbox').droppable({
            addClasses: false,
            over: function() {
                $(this).addClass('backpackboxhover');
            },
            out: function() {
                $(this).removeClass('backpackboxhover');
            },
            accept: function(draggable) {
                if (draggable.parent().attr('id') == $(this).parent().attr('id')) {
                    return true;
                } else {
                    return false;
                }
            },
              drop: function(event, ui) {
                  $(this).addClass('backpackboxchanged');
                  $(this).attr('value', ui.draggable.attr('value'));
                $(this).html(ui.draggable.html());

                jQuery.each (elements, function () {
                    $('#'+this+'_equipped').val($('#'+this+' div.equippedbox p.itemid').text());
                });
              },
        });
    });
");

// ---------

if ($_GET['op'] == 'change' && isset($_POST['equipped'])) {

    foreach ($_POST['equipped'] as $itemclass => $itemid) {

        if (is_numeric($itemid)) {
            // Replaced by a new Item or still old item
            $item = ItemSystem::getItemObject($itemid);

            // Check if the item fits the slot it's placed into
            if (strncmp($item->class, $itemclass, strlen($itemclass)) === 0) {
                // Get the old equipped item
                $olditem = ItemSystem::getEquippedItem($user->char, $itemclass);

                // First check if the newitem is different
                if ($olditem && $olditem->id != $item->id) {
                    // Replace the old item from ITEMSYSTEM_LOCATION_EQUIPMENT
                    // with the new one
                    $olditem->location 	= ITEMSYSTEM_LOCATION_BACKPACK;
                    $olditem->save();
                    unset($olditem);

                    // Move the new item to the equipment
                    $item->location 	= ITEMSYSTEM_LOCATION_EQUIPMENT;
                    $item->save();
                } elseif (!$olditem) {
                    // There was no old item
                    $item->location 	= ITEMSYSTEM_LOCATION_EQUIPMENT;
                    $item->save();
                } else {
                    // Old and new item are the same - no action
                }
            }

            unset($item);
        } else {
            // Removed item
            $olditem = ItemSystem::getEquippedItem($user->char, $itemclass);
            if ($olditem) {
                $olditem->location 	= ITEMSYSTEM_LOCATION_BACKPACK;
                $olditem->save();
            }
        }
    }

}

// ---------

$page->addForm("inventoryform", true);
$newURL = clone $page->url;
$newURL->setParameter("op", "change");
$page->inventoryform->head("inventoryform", $newURL);
$page->nav->add(new Link("", $newURL));

$itemclasses = array ('weapon','armor_head','armor_chest','armor_arms','armor_legs','armor_feet');

foreach ($itemclasses as $itemclass) {
//	$equipped = ItemSystem::getInventoryList($user->char, "equipment", $itemclass);
     $equipped = array_shift(ItemSystem::getInventoryListAsObjects($user->char, "equipment", $itemclass));
    $backpack = ItemSystem::getInventoryListAsObjects($user->char, false, $itemclass);

    $page->output("<div id='{$itemclass}'>", true);

    $page->output("`b`g`c".EnvironmentSystem::translate($itemclass) . "`g`b");

    $page->output("<div class='equippedbox'>", true);
    if (isset($equipped)) {
        $page->output("<p class='itemid hidden'>".$equipped->itemid."</p>", true);
        $page->output("<p class='itemname'>".$equipped->name."</p>", true);
        $page->output("<p class='itemdata'>".EnvironmentSystem::translate($equipped->class)."</p>", true);
        if ($itemclass == "weapon") {
            $page->output("<p class='itemdata'>Schaden: ".$equipped->showDamage(false)."</p>", true);
        } else {
            $page->output("<p class='itemdata'>Rüstung: ".$equipped->showArmorClass(false)."</p>", true);
        }
        $page->output("<p class='itemdata'>Level ".$equipped->level."</p>", true);
    }
    $page->output("</div>", true);
    $page->output("<input type='hidden' id='{$itemclass}_equipped' name='equipped[{$itemclass}]' value=''>", true);

    $page->output("<div class='floatclear'></div>", true);

    foreach ($backpack as $backpackitem) {
        $page->output("<div class='backpackbox'>", true);
        $page->output("<p class='itemid hidden'>".$backpackitem->itemid."</p>", true);
        $page->output("<p class='itemname'>".$backpackitem->name."</p>", true);
        $page->output("<p class='itemdata'>".EnvironmentSystem::translate($backpackitem->class)."</p>", true);
        if ($itemclass == "weapon") {
            $page->output("<p class='itemdata'>Schaden: ".$backpackitem->showDamage(false)."</p>", true);
        } else {
            $page->output("<p class='itemdata'>Rüstung: ".$backpackitem->showArmorClass(false)."</p>", true);
        }
        $page->output("<p class='itemdata'>Level ".$backpackitem->level."</p>", true);
        $page->output("</div>", true);
    }

    $page->output("</div>", true);
}

$page->inventoryform->setCSS("floatright");
$page->inventoryform->submitButton("Änderung Speichern");
$page->inventoryform->close();

?>
