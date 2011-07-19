<?php
/**
 * Inventory Chooser
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
use Ruins\Common\Controller\AbstractPageObject;

class InventoryChooserPage extends AbstractPageObject
{
    public $title  = "Inventar";

    public function createContent($page, $parameters)
    {
        $page->nav->addHead("Navigation");
        if (isset($parameters['return'])) {
            $page->nav->addLink("Zurück", $parameters['return']);
        } else {
            $page->output("`b`g`#25This Page needs a return-Parameter! Please fix this!");
        }

        $page->nav->addHead("Inventar");

        switch ($parameters['op']) {

            default:
            case "all":
                // Show the complete Inventory
                if (isset($parameters['order']) && isset($parameters['orderDesc'])) {
                    $itemlist 	= ItemManager::getInventoryList($user->character,
                                                                "all",
                                                                false,
                                                                $parameters['order'],
                                                                $parameters['orderDesc']);
                } else {
                    $itemlist 	= ItemManager::getInventoryList($user->character, "all");
                }

                $newURL = clone $page->url;
                $newURL->setParameter("op", "backpack");
                $page->nav->addLink("Rucksack", $newURL);
                break;

            case "backpack":
                // Only show the backpack
                if (isset($parameters['order']) && isset($parameters['orderDesc'])) {
                    $itemlist 	= ItemManager::getInventoryList($user->character,
                                                                "backpack",
                                                                false,
                                                                $parameters['order'],
                                                                $parameters['orderDesc']);
                } else {
                    $itemlist 	= ItemManager::getInventoryList($user->character, "backpack");
                }

                $newURL = clone $page->url;
                $newURL->setParameter("op", "all");
                $page->nav->addLink("Alle zeigen", $newURL);
                break;

        }

        // The callop-attribute is a op-value that is called after the items are chosen.
        // Example:
        //	$_GET['return'] = Page/Ironlance/Merchant
        //	$_GET['callop'] = sell
        // Results into call: Page/Ironlance/Merchant/sell
        if (isset($parameters['callop'])) {
            $callbackpage = $parameters['return']."/".$parameters['callop'];
            $page->addForm("chooser");
            $page->getForm("chooser")->head("chooser", $callbackpage);
            $page->nav->addHiddenLink($callbackpage);
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

                if (isset($parameters['callop'])) {
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
        $page->getTable("itemlist_armors")->setCSS("messagelist");
        $page->getTable("itemlist_armors")->setTabAttributes(false);
        $page->getTable("itemlist_armors")->addTabHeader($headers);
        $page->getTable("itemlist_armors")->addListArray($newItemList, "firstrow", "firstrow");
        $page->getTable("itemlist_armors")->setSecondRowCSS("secondrow");
        $page->getTable("itemlist_armors")->load();

        if (isset($parameters['callop'])) {
            $page->getForm("chooser")->setCSS("delbutton");
            $page->getForm("chooser")->submitButton("Weiter");
            $page->getForm("chooser")->close();
        }

        // Add Navlinks for the clickable Headers
        foreach ($headers as $link=>$linkname) {
            $newURL = clone $page->url;
            $newURL->setParameter("order", $link);
            if (isset($parameters['order']) && isset($parameters['orderDesc'])
                && $parameters['order'] == $link && $parameters['orderDesc'] == 0) {
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
    }
}