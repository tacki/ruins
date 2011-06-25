<?php
/**
 * Namespaces
 */
use Main\Manager,
    Main\Entities;

global $em;

//*********************************
// Create Admin User
$install_admin_user = Manager\User::createUser("admin", "admin");
// Create Normal User
$install_normal_user = Manager\User::createUser("user", "user");

$em->flush();


//*********************************
// Create Administration Character
$admin_char = Manager\User::createCharacter("Administrator", $install_admin_user);
$admin_char->displayname = "`#19`bAdministrator`b`#00";

// Add Administrator Char to Admin- and User-Group
$group = Manager\Rights::createGroup("Administrator");
Manager\Rights::addToGroup($group, $admin_char);
$group = Manager\Rights::createGroup("User");
Manager\Rights::addToGroup($group, $admin_char);

$em->flush();


//*********************************
// Create Test Character for Admin User
$test_char = Manager\User::createCharacter("Testcharacter", $install_admin_user);
$test_char->displayname = "`#59Testcharacter`#00";

// Add User Char to User-Group
$group = Manager\Rights::createGroup("User");
Manager\Rights::addToGroup($group, $test_char);

$em->flush();


//*********************************
// Create Test Character for Normal User
$test_char2 = Manager\User::createCharacter("Testcharacter2", $install_normal_user);
$test_char2->displayname = "`#79Testcharacter2`#00";

// Add User Char to User-Group
$group = Manager\Rights::createGroup("User");
Manager\Rights::addToGroup($group, $test_char2);

$em->flush();


//*********************************
// Set default Character to Administrator for Admin User
$install_admin_user->character = $admin_char;
$install_admin_user->settings->default_character = $admin_char;

// Set default Character to Testcharacter2 for Normal User
$install_normal_user->character = $test_char2;
$install_normal_user->settings->default_character = $test_char2;

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
// Create Sites+Waypoints
$sites = array (
                    "derashok/tribalcenter" => "Derashok Stammeszentrum - Der wichtigste Treffpunkt der orkischen Clans",
                    "ironlance/citysquare"  => "Ironlance Stadtplatz - Der Platz mitten in Ironlance, dem Stolz der Menschen",
                    "dunsplee/trail"		=> "Dunsplee Wald - Weg zum sagenumwobenen Wald`n`n",
               );

$waypoints = array (
                        "derashok/tribalcenter"  => array(135, 170, 25),
                        "ironlance/citysquare"   => array(20, 180, 40),
                        "dunsplee/trail"	     => array(55, 45, 30),
);

foreach ($sites as $name => $description) {
    Manager\System::addSite($name, $description, $waypoints[$name]);
}

$em->flush();

// Create Waypoint-Connections
$waypoints_conn = array (
                            "derashok/tribalcenter" => "dunsplee/trail",
                            "ironlance/citysquare"  => "dunsplee/trail",
                        );

foreach ($waypoints_conn as $source => $target) {
    $site1 = $em->getRepository("Main:Site")->findOneByName($source);
    $site2 = $em->getRepository("Main:Site")->findOneByName($target);

    Manager\System::addSiteConnection($site1, $site2);
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
