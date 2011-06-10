<?php
/**
 * Namespaces
 */
use Main\Manager;

global $em;

// Create Test-Character
if (!($install_char = $em->getRepository("Main:Character")->findOneByName("Testuser"))) {
    $install_char = new Entities\Character;
    $install_char->name = "Testuser";
    $install_char->displayname = "`#35Testuser`#00";
    $em->persist($install_char);
}

$em->flush();

// Create Test-UserSettings
if (!($install_settings = $em->getRepository("Main:UserSetting")->findOneBy(array("default_character" => $install_char->id)))) {
    $install_settings = new Entities\UserSetting;
    $install_settings->default_character = $install_char;
    $em->persist($install_settings);
}

$em->flush();

// Create Test-User
if (!($install_user = $em->getRepository("Main:User")->findOneByLogin("test"))) {
    $install_user = new Entities\User;
    $install_user->login = "test";
    $install_user->password = md5("test");
    $install_user->character = $install_char;
    $install_user->settings  = $install_settings;
    $em->persist($install_user);
}

$em->flush();

// Create Test-Weapons
if (!($weapon = $em->getRepository("Main:Items\Weapon")->findOneByName("Testwaffe"))) {
    $weapon             = new Entities\Items\Weapon;
    $weapon->class      = Manager\Item::CLASS_WEAPON;
    $weapon->name       = "Testwaffe";
    $weapon->damage_min = 5;
    $weapon->damage_max = 10;
    $weapon->location   = Manager\Item::LOCATION_BACKPACK;
    $weapon->owner      = $install_char;
    $em->persist($weapon);
}

$em->flush();

// Create Test-Armor
$armors = array (
                   Manager\Item::CLASS_ARMOR_HEAD  => "Testrüstung (Kopf)",
                   Manager\Item::CLASS_ARMOR_ARMS  => "Testrüstung (Arm)",
                   Manager\Item::CLASS_ARMOR_CHEST => "Testrüstung (Brust)",
                   Manager\Item::CLASS_ARMOR_LEGS  => "Testrüstung (Beine)",
                   Manager\Item::CLASS_ARMOR_FEET  => "Testrüstung (Füße)",
                );

foreach ($armors as $armorclass => $armorname) {
    if (!$em->getRepository("Main:Items\Armor")->findOneByName($armorname)) {
        $armor             = new Entities\Items\Armor;
        $armor->class      = $armorclass;
        $armor->name       = $armorname;
        $armor->armorclass = 1;
        $armor->location   = Manager\Item::LOCATION_BACKPACK;
        $armor->owner      = $install_char;
        $em->persist($armor);
    }
}

$em->flush();

// Create Waypoints
$waypoints = array (
                        "derashok"   => array(135, 170, 25),
                        "ironlance"  => array(20, 180, 40),
                        "dunsplee"	 => array(55, 45, 30),
                   );

foreach ($waypoints as $name => $coords) {
    if (!$em->getRepository("Main:Waypoint")->findOneByName($name)) {
        $waypoint         = new Entities\Waypoint;
        $waypoint->name   = $name;
        $waypoint->x      = $coords[0];
        $waypoint->y      = $coords[1];
        $waypoint->z      = $coords[2];
        $em->persist($waypoint);
    }
}

$em->flush();

// Create Waypoint-Connections
$waypoints_conn = array (
                            array("derashok","dunsplee"),
                            array("ironlance","dunsplee"),
                        );

foreach ($waypoints_conn as $connection) {
    $wp_conn             = new Entities\WaypointConnection;
    $wp_conn->start      = $em->getRepository("Main:Waypoint")->findOneByName($connection[0]);
    $wp_conn->end        = $em->getRepository("Main:Waypoint")->findOneByName($connection[1]);
    $wp_conn->difficulty = 0;

    if (!$em->getRepository("Main:WaypointConnection")->findOneBy(array("start" => $wp_conn->start->id, "end" => $wp_conn->end->id))) {
        $em->persist($wp_conn);
    }

}

$em->flush();

// Reverse Mappings
$install_char->user     = $install_user;
$install_settings->user = $install_user;

$em->flush();

?>
