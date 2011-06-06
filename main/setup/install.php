<?php
global $em;

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
    $weapon = new Entities\Items\Weapon;
    $weapon->class = Manager\Item::CLASS_WEAPON;
    $weapon->name = "Testwaffe";
    $weapon->damage_min = 5;
    $weapon->damage_max = 10;
    $weapon->location = Manager\Item::LOCATION_BACKPACK;
    $em->persist($weapon);
}

// Create Test-Armor (Head)
if (!($armor_head = $em->getRepository("Entities\Items\Armor")->findOneByName("Testarmor"))) {
    $armor_head = new Entities\Items\Armor;
    $armor_head->class = Manager\Item::CLASS_ARMOR_HEAD;
    $armor_head->name = "Testrüstung (Kopf)";
    $em->persist($armor_head);
}

// Create Test-Armor (Arms)
if (!($armor_arms = $em->getRepository("Entities\Items\Armor")->findOneByName("Testarmor"))) {
    $armor_arms = new Entities\Items\Armor;
    $armor_arms->class = Manager\Item::CLASS_ARMOR_ARMS;
    $armor_arms->name = "Testrüstung (Arm)";
    $em->persist($armor_arms);
}

// Create Test-Armor (Chest)
if (!($armor_chest = $em->getRepository("Entities\Items\Armor")->findOneByName("Testarmor"))) {
    $armor_chest = new Entities\Items\Armor;
    $armor_chest->class = Manager\Item::CLASS_ARMOR_CHEST;
    $armor_chest->name = "Testrüstung (Brust)";
    $em->persist($armor_chest);
}

// Create Test-Armor (Legs)
if (!($armor_legs = $em->getRepository("Entities\Items\Armor")->findOneByName("Testarmor"))) {
    $armor_legs = new Entities\Items\Armor;
    $armor_legs->class = Manager\Item::CLASS_ARMOR_LEGS;
    $armor_legs->name = "Testrüstung (Beine)";
    $em->persist($armor_legs);
}

// Create Test-Armor (Feet)
if (!($armor_feet = $em->getRepository("Entities\Items\Armor")->findOneByName("Testarmor"))) {
    $armor_feet = new Entities\Items\Armor;
    $armor_feet->class = Manager\Item::CLASS_ARMOR_FEET;
    $armor_feet->name = "Testrüstung (Füße)";
    $em->persist($armor_feet);
}


// Reverse Mappings
$install_char->user     = $install_user;
$install_settings->user = $install_user;

$weapon->owner          = $install_char;
$armor_head->owner      = $install_char;
$armor_arms->owner      = $install_char;
$armor_chest->owner     = $install_char;
$armor_legs->owner      = $install_char;
$armor_feet->owner      = $install_char;

?>
