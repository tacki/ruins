<?php
global $em;

use Manager\Item;

// Create Test-Character
if (!($install_char = $em->getRepository("Entities\Character")->findOneByName("Testuser"))) {
    $install_char = new Entities\Character;
    $install_char->name = "Testuser";
    $install_char->displayname = "`#35Testuser`#00";
    $em->persist($install_char);
}

// Create Test-UserSettings
if (!($install_settings = $em->getRepository("Entities\UserSetting")->findOneBy(array("default_character" => $install_char->id)))) {
    $install_settings = new Entities\UserSetting;
    $install_settings->default_character = $install_char;
    $em->persist($install_settings);
}


// Create Test-User
if (!($install_user = $em->getRepository("Entities\User")->findOneByLogin("test"))) {
    $install_user = new Entities\User;
    $install_user->login = "test";
    $install_user->password = md5("test");
    $install_user->character = $install_char;
    $install_user->settings  = $install_settings;
    $em->persist($install_user);
}

// Create Test-Weapons
if (!($weapon = $em->getRepository("Entities\Items\Weapon")->findOneByName("Testwaffe"))) {
    $weapon             = new Entities\Items\Weapon;
    $weapon->class      = Item::CLASS_WEAPON;
    $weapon->name       = "Testwaffe";
    $weapon->damage_min = 5;
    $weapon->damage_max = 10;
    $weapon->location   = Item::LOCATION_BACKPACK;
    $weapon->owner      = $install_char;
    $em->persist($weapon);
}

// Create Test-Armor
$armors = array (
                   Item::CLASS_ARMOR_HEAD  => "Testrüstung (Kopf)",
                   Item::CLASS_ARMOR_ARMS  => "Testrüstung (Arm)",
                   Item::CLASS_ARMOR_CHEST => "Testrüstung (Brust)",
                   Item::CLASS_ARMOR_LEGS  => "Testrüstung (Beine)",
                   Item::CLASS_ARMOR_FEET  => "Testrüstung (Füße)",
                );

foreach ($armors as $armorclass => $armorname) {
    if (!($armor = $em->getRepository("Entities\Items\Armor")->findOneByName($armorname))) {
        $armor             = new Entities\Items\Armor;
        $armor->class      = $armorclass;
        $armor->name       = $armorname;
        $armor->armorclass = 1;
        $armor->location   = Item::LOCATION_BACKPACK;
        $armor->owner      = $install_char;
        $em->persist($armor);
    }
}


// Reverse Mappings
$install_char->user     = $install_user;
$install_settings->user = $install_user;

?>
