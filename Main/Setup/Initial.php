<?php
/**
 * Namespaces
 */
use Main\Manager,
    Main\Entities;

global $em;

//*********************************
// Create Admin User
$install_user = Manager\User::createUser("admin", "admin");

$em->flush();

//*********************************
// Create Administration Character
$admin_char = Manager\User::createCharacter("Administrator", $install_user);
$admin_char->displayname = "`#19`bAdministrator`b`#00";

// Add Administrator Char to Admin- and User-Group
$group = Manager\Rights::createGroup("Administrator");
Manager\Rights::addToGroup($group, $admin_char);
$group = Manager\Rights::createGroup("User");
Manager\Rights::addToGroup($group, $admin_char);

$em->flush();


//*********************************
// Create Test Character
$test_char = Manager\User::createCharacter("Testcharacter", $install_user);
$test_char->displayname = "`#59Testcharacter`#00";

// Add User Char to User-Group
$group = Manager\Rights::createGroup("User");
Manager\Rights::addToGroup($group, $test_char);

$em->flush();


//*********************************
// Set default Character to Admin
$install_user->character = $admin_char;
$install_user->settings->default_character = $admin_char;


//*********************************
// Create Test-Weapons
if (!($weapon = $em->getRepository("Main:Items\Weapon")->findOneByName("Testwaffe"))) {
    $weapon             = new Entities\Items\Weapon;
    $weapon->class      = Manager\Item::CLASS_WEAPON;
    $weapon->name       = "Testwaffe";
    $weapon->damage_min = 5;
    $weapon->damage_max = 10;
    $weapon->location   = Manager\Item::LOCATION_BACKPACK;
    $weapon->owner      = $test_char;
    $em->persist($weapon);
}

$em->flush();


//*********************************
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
        $armor->owner      = $test_char;
        $em->persist($armor);
    }
}

$em->flush();


//*********************************
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


//*********************************
// Create Adminpages
Manager\System::addAdminPage("Ironlance", "Travel", "page=ironlance/citysquare");
Manager\System::addAdminPage("Derashok", "Travel", "page=derashok/tribalcenter");
Manager\System::addAdminPage("Dunsplee", "Travel", "page=dunsplee/trail");

Manager\System::addAdminPage("Module", "System", "page=admin/modules");

$em->flush();

?>
