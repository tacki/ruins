<?php
/**
 * Namespaces
 */
use Ruins\Main\Manager\RightsManager;
use Ruins\Main\Manager\SystemManager;
use Ruins\Main\Manager\ItemManager;
use Ruins\Main\Entities\Items\Weapon;
use Ruins\Main\Entities\Items\Armor;
use Ruins\Common\Controller\Registry;

$em = Registry::getEntityManager();
$config = Registry::getMainConfig();

//*********************************
// Create Config-Settings
$config->addPublicPage(array(	"Page/Common/Login",
                                "Page/Common/Logout",
                                "Page/Developer/Test",)
);
$config->addNoCachePage(array(	"Page/Common/Portal" )
);

//*********************************
// Create Admin User
$install_admin_user = $em->getRepository("Main:User")->create("admin", "admin");
// Create Normal User
$install_normal_user = $em->getRepository("Main:User")->create("user", "user");

$em->flush();


//*********************************
// Create Administration Character
$admin_char = $em->getRepository("Main:Character")->create("Administrator", $install_admin_user);
$admin_char->displayname = "`#19`bAdministrator`b`#00";

// Add Administrator Char to Admin- and User-Group
$group = RightsManager::createGroup("Administrator");
RightsManager::addToGroup($group, $admin_char);
$group = RightsManager::createGroup("User");
RightsManager::addToGroup($group, $admin_char);

$em->flush();


//*********************************
// Create Test Character for Admin User
$test_char = $em->getRepository("Main:Character")->create("Testcharacter", $install_admin_user);
$test_char->displayname = "`#59Testcharacter`#00";

// Add User Char to User-Group
$group = RightsManager::createGroup("User");
RightsManager::addToGroup($group, $test_char);

$em->flush();


//*********************************
// Create Test Character for Normal User
$test_char2 = $em->getRepository("Main:Character")->create("Testcharacter2", $install_normal_user);
$test_char2->displayname = "`#79Testcharacter2`#00";

// Add User Char to User-Group
$group = RightsManager::createGroup("User");
RightsManager::addToGroup($group, $test_char2);

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
    $weapon             = new Weapon;
    $weapon->class      = ItemManager::CLASS_WEAPON;
    $weapon->name       = "Testwaffe";
    $weapon->damage_min = 5;
    $weapon->damage_max = 10;
    $weapon->location   = ItemManager::LOCATION_BACKPACK;
    $weapon->owner      = $test_char;
    $em->persist($weapon);
}

$em->flush();


//*********************************
// Create Test-Armor
$armors = array (
                   ItemManager::CLASS_ARMOR_HEAD  => "Testrüstung (Kopf)",
                   ItemManager::CLASS_ARMOR_ARMS  => "Testrüstung (Arm)",
                   ItemManager::CLASS_ARMOR_CHEST => "Testrüstung (Brust)",
                   ItemManager::CLASS_ARMOR_LEGS  => "Testrüstung (Beine)",
                   ItemManager::CLASS_ARMOR_FEET  => "Testrüstung (Füße)",
                );

foreach ($armors as $armorclass => $armorname) {
    if (!$em->getRepository("Main:Items\Armor")->findOneByName($armorname)) {
        $armor             = new Armor;
        $armor->class      = $armorclass;
        $armor->name       = $armorname;
        $armor->armorclass = 1;
        $armor->location   = ItemManager::LOCATION_BACKPACK;
        $armor->owner      = $test_char;
        $em->persist($armor);
    }
}

$em->flush();


//*********************************
// Create Sites+Waypoints
$sites = array (
                    "Page/Derashok/Tribalcenter" => "Derashok Stammeszentrum - Der wichtigste Treffpunkt der orkischen Clans",
                    "Page/Ironlance/Citysquare"  => "Ironlance Stadtplatz - Der Platz mitten in Ironlance, dem Stolz der Menschen",
                    "Page/Dunsplee/Trail"        => "Dunsplee Wald - Weg zum sagenumwobenen Wald`n`n",
               );

$waypoints = array (
                        "Page/Derashok/Tribalcenter"  => array(135, 170, 25),
                        "Page/Ironlance/Citysquare"   => array(20, 180, 40),
                        "Page/Dunsplee/Trail"	      => array(55, 45, 30),
);

foreach ($sites as $name => $description) {
    SystemManager::addSite($name, $description, $waypoints[$name]);
}

$em->flush();

// Create Waypoint-Connections
$waypoints_conn = array (
                            "Page/Derashok/Tribalcenter" => "Page/Dunsplee/Trail",
                            "Page/Ironlance/Citysquare"  => "Page/Dunsplee/Trail",
                        );

foreach ($waypoints_conn as $source => $target) {
    $site1 = $em->getRepository("Main:Site")->findOneByName($source);
    $site2 = $em->getRepository("Main:Site")->findOneByName($target);

    SystemManager::addSiteConnection($site1, $site2);
}

$em->flush();


//*********************************
// Create Adminpages
SystemManager::addAdminPage("Ironlance", "Travel", "Page/Ironlance/Citysquare");
SystemManager::addAdminPage("Derashok", "Travel", "Page/Derashok/Tribalcenter");
SystemManager::addAdminPage("Dunsplee", "Travel", "Page/Dunsplee/Trail");

SystemManager::addAdminPage("Module", "System", "Page/Admin/Modules");

$em->flush();

?>
