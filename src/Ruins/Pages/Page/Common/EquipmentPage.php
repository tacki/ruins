<?php
/**
 * Inventory
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Common;
use Ruins\Main\Controller\Link;
use Ruins\Main\Manager\ItemManager;
use Ruins\Main\Manager\SystemManager;
use Ruins\Main\Entities\Items\Armor;
use Ruins\Main\Entities\Items\Weapon;
use Ruins\Common\Controller\AbstractPageObject;

class EquipmentPage extends AbstractPageObject
{
    public $title  = "Ausrüstung";

    public function createContent($page, $parameters)
    {
        $em = $this->getEntityManager();

        $page->getNavigation()->addHead("Navigation");
        if (isset($parameters['return'])) {
            $page->getNavigation()->addLink("Zurück", $parameters['return']);
        } else {
            $page->output("`b`g`#25This Page needs a return-Parameter! Please fix this!");
        }
        $page->getNavigation()->addLink("Aktualisieren", $page->getUrl());

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

        if ($parameters['op'] == 'change' && isset($_POST['equipped'])) {

            foreach ($_POST['equipped'] as $itemclass => $itemid) {

                if (is_numeric($itemid)) {
                    // Replaced by a new Item or still old item
                    $item = ItemManager::getItem($itemid);

                    // Check if the item fits the slot it's placed into
                    if ($item->class === $itemclass) {
                        // Get the old equipped item
                        $olditem = ItemManager::getEquippedItem($user->character, $itemclass);

                        // First check if the newitem is different
                        if ($olditem && $olditem->id != $item->id) {
                            // Replace the old item from Manager\Item::LOCATION_EQUIPMENT
                            // with the new one
                            $olditem->location 	= ItemManager::LOCATION_BACKPACK;

                            // Move the new item to the equipment
                            $item->location 	= ItemManager::LOCATION_EQUIPMENT;
                        } elseif (!$olditem) {
                            // There was no old item
                            $item->location 	= ItemManager::LOCATION_EQUIPMENT;
                        } else {
                            // Old and new item are the same - no action
                        }
                    }
                } else {
                    // Removed item
                    $olditem = ItemManager::getEquippedItem($user->character, $itemclass);
                    if ($olditem) {
                        $olditem->location 	= ItemManager::LOCATION_BACKPACK;
                    }

                }
            }

            $em->flush();

        }

        // ---------

        $page->addForm("inventory");
        $newURL = clone $page->getUrl();
        $newURL->setParameter("op", "change");
        $page->getForm("inventory")->head("inventoryform", $newURL);
        $page->getNavigation()->addHiddenLink($newURL);

        $itemtypes = array (ItemManager::CLASS_WEAPON, ItemManager::CLASS_ARMOR);

        $itemclasses = array (ItemManager::CLASS_WEAPON);
        $itemclasses = array_merge($itemclasses, ItemManager::getArmorClasses());

        $equipped = ItemManager::getInventoryList($user->character, "equipment", $itemtypes);
        $backpack = ItemManager::getInventoryList($user->character, "backpack", $itemtypes, "class");

        $page->output("`g`bAusrüstung`b`g`n");

        $emptybox = true;

        foreach ($itemclasses as $itemclass) {
            foreach ($equipped as $item) {
                if ($item->class == $itemclass) {
                    $emptybox = false;
                    break;
                } else {
                    $emptybox = true;
                }
            }

            if ($emptybox) {
                $page->output("<div id='{$itemclass}'>", true);

                $page->output("`b`g`c".SystemManager::translate($itemclass) . "`c`g`b");

                $page->output("<div class='equippedbox'>", true);
                $page->output("</div>", true);

                $page->output("<input type='hidden' id='{$itemclass}_equipped' name='equipped[{$itemclass}]' value='0'>", true);
                $page->output("</div>", true);
            } else {
                $page->output("<div id='{$item->class}'>", true);

                $page->output("`b`g`c".SystemManager::translate($item->class) . "`c`g`b");

                $page->output("<div class='equippedbox'>", true);

                $page->output("<p class='itemid hidden'>".$item->id."</p>", true);
                $page->output("<p class='itemname'>".$item->name."</p>", true);
                $page->output("<p class='itemdata'>".SystemManager::translate($item->class)."</p>", true);

                if ($item instanceof Weapon) {
                    $page->output("<p class='itemdata'>Schaden: ".$item->showDamage(false)."</p>", true);
                } elseif ($item instanceof Armor) {
                    $page->output("<p class='itemdata'>Rüstung: ".$item->showArmorClass(false)."</p>", true);
                }

                $page->output("<p class='itemdata'>Level ".$item->level."</p>", true);
                $page->output("</div>", true);

                $page->output("<input type='hidden' id='{$item->class}_equipped' name='equipped[{$item->class}]' value=''>", true);
                $page->output("</div>", true);
            }
        }

        $page->output("<div class='floatclear'></div>", true);
        $page->output("<hr>", true);
        $page->output("`g`bRucksack`b`g`n");

        foreach ($backpack as $item) {
            $page->output("<div id='{$item->class}'>", true);

            $page->output("<div class='backpackbox'>", true);

            $page->output("<p class='itemid hidden'>".$item->id."</p>", true);
            $page->output("<p class='itemname'>".$item->name."</p>", true);
            $page->output("<p class='itemdata'>".SystemManager::translate($item->class)."</p>", true);

            if ($item instanceof Weapon) {
                $page->output("<p class='itemdata'>Schaden: ".$item->showDamage(false)."</p>", true);
            } elseif ($item instanceof Armor) {
                $page->output("<p class='itemdata'>Rüstung: ".$item->showArmorClass(false)."</p>", true);
            }

            $page->output("<p class='itemdata'>Level ".$item->level."</p>", true);
            $page->output("</div>", true);

            $page->output("</div>", true);
        }

        $page->output("<div class='floatclear'></div>", true);

        $page->getForm("inventory")->setCSS("floatright");
        $page->getForm("inventory")->submitButton("Änderung Speichern");
        $page->getForm("inventory")->close();
    }
}