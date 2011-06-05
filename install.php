<?php
/**
 * Installation
 *
 * Installation and Configuration of 'Ruins'
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006-2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
    <title>Ruins Installation Script</title>
    <meta http-equiv="content-type" content="application/xhtml+xml;charset=utf-8" />
    <style type="text/css">
    <!--
        body {
            background-color:#FFFFFF;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: black;
        }

        h4 {
            color: black;
            text-decoration: underline;
        }

        div.checkfor {
            color: black;
            text-align: center;
            border-bottom: #BE9272 1px solid;
        }

        div.ok {
            font-weight: bold;
            text-align: center;
            color: green;
        }

        div.notok {
            font-weight: bold;
            text-align: center;
            color: red;

        }

        div.ask {
            font-weight: bold;
            text-align: center;
            color: #ffae00;

        }

        div.continue {
            font-weight: bold;
            color: #2d5b00;
        }

        input.continue {
            font-weight: bold;
            text-align: center;
            color: green;
            border-left: #BE9272 1px solid;
            border-right: #BE9272 1px solid;
            border-top: #BE9272 1px solid;
            border-bottom: #BE9272 1px solid;
        }

        input.retry {
            font-weight: bold;
            text-align: center;
            color: #ffae00;
            border-left: #BE9272 1px solid;
            border-right: #BE9272 1px solid;
            border-top: #BE9272 1px solid;
            border-bottom: #BE9272 1px solid;
        }

        input.restart {
            font-weight: bold;
            text-align: center;
            color: red;
            border-left: #BE9272 1px solid;
            border-right: #BE9272 1px solid;
            border-top: #BE9272 1px solid;
            border-bottom: #BE9272 1px solid;
        }

        td.description {
            font-size: 8px;
            border-left: #BE9272 1px solid;
            border-right: #BE9272 1px solid;
            border-top: #BE9272 1px solid;
            border-bottom: #BE9272 1px solid;
        }
    -->
    </style>

</head>
<body>

<?php
use Doctrine\ORM\Tools\SchemaValidator;

// Set timezone
date_default_timezone_set('Europe/Berlin');

if (!isset($_GET['step'])) {
    $_GET['step'] = 1;
}

switch ($_GET['step']) {

    default:
        echo "<h2>Invalid Action</h2>";
        echo "<br /><b>Restart from Step 1</b><br />";
        echo "<form action='install.php?step=1' method='post'>
                <input type='submit' value='Restart' class='restart'></form>";
        break;

    case 1:
        echo "<h2>Step " . $_GET['step'] .  " of 4 - Create and Verify Environment</h2>";
        echo "<h4>Checking for write Permissions</h4>";

        $writeaccess = array();

        $writeaccess["Configuration Directory"] = "config";
        $writeaccess["Temp Folder"]				= "tmp";

        // To add more Directories, just keep this syntax
        // $writeaccess['directorydescription']	= "pathname/pathname";

        // Do the voodoo
        foreach ($writeaccess as $description => $dirname) {
            echo "<div class='checkfor'>" . $description . " ... </div>";

            if (!file_exists($dirname) && is_writable(dirname(__FILE__))) {
               echo "<div class='ask'>Creating Directory " . $dirname . "</div>";
               mkdir ($dirname);
            } elseif (!is_writable(dirname(__FILE__))) {
                echo "<div class='notok'>Not OK! Directory " . $dirname . " doesn't exist and can't be created automatically!
                        Please create this Directory manually and make sure that it is writeable by the Webserver.</div><br />";
            }

            if (is_writable($dirname)) {
                echo "<div class='ok'>OK!</div>";
            } else {
                echo "<div class='notok'>Not OK! Directory " . $dirname . " is not writeable!
                        Please make sure that this Directory is writeable by the Webserver.</div><br />";
                echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                            <input type='submit' value='Retry'></form>";
                break 2;
            }

            echo "<br />";
        }

        // ***************************************************************************************** //

        echo "<h4>Creating necessary Systemfolders</h4>";

        $createdirs = array();

        // dirconfig-file
        $createdirs['Smarty Temp Base Dir'] 	= "tmp/smarty";
        $createdirs['Smarty Temp Cache Dir']	= "tmp/smarty/cache";
        $createdirs['Smarty Temp Compile Dir']	= "tmp/smarty/templates_c";
        $createdirs['OpenID Temp Base Dir']		= "tmp/openid";

        // To add more Directories, just keep this syntax
        // $createdirs['directorydescription']	= "pathname";

        // Do the Voodoo
        foreach ($createdirs as $description => $dirname) {
            echo "<div class='checkfor'>" . $description . " ... </div>";

            if (is_dir($dirname) && is_writable($dirname)) {
                echo "<div class='ok'>OK!</div>";
            } elseif (!(file_exists($dirname))) {
                echo "<div class='ok'>Creating! </div>";
                if (mkdir($dirname)) {
                    echo "<div class='ok'>OK!</div>";
                } else {
                    echo "<div class='notok'>Not OK! Directory " . $dirname . " cannot be created!
                            Please make sure that the Directory is writeable by the Webserver.</div><br />";
                    echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                                <input type='submit' value='Retry'></form>";
                    break 2;
                }
            } else {
                echo "<div class='notok'>Not OK! Directory " . $dirname . " is not writeable!
                        Please make sure that this Directory is writeable by the Webserver.</div><br />";
                echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                            <input type='submit' value='Retry'></form>";
                break 2;
            }
        }

        // ***************************************************************************************** //

        echo "<h4>Creating necessary Systemfiles</h4>";

        $conffiles 	= array();

        // dirconfig-file
        $conffiles['dirconfig'] = array();
        $conffiles['dirconfig']['path']			= dirname(__FILE__)."/config/dirconf.cfg.php";
        $conffiles['dirconfig']['content'] 		=	"<?php"."\n" .
                                                    "	define(\"DIR_BASE\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_MAIN\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/main/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_AREA\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/area/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_CONFIG\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/config/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_LIB\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/lib/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_INCLUDES\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/includes/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_INCLUDES_PEAR\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/includes/external/pear/".
                                                        "\");"."\n" .
                                                     "	define(\"DIR_COMMON\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/common/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_COMMON_EXTERNAL\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/common/external/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_INCLUDES_DOCTRINE\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/common/external/doctrine2/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_INCLUDES_SMARTY\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/common/external/smarty/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_IMAGES\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/images/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_LOG\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/logs/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_TEMPLATES\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/templates/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_TEMP\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/tmp/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_MODULES\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/modules/".
                                                        "\");"."\n" .
                                                    "	\n" .
                                                    " 	set_include_path(DIR_INCLUDES_PEAR.PATH_SEPARATOR.get_include_path());"."\n" .
                                                    "?>";

        // To add more Files, just keep this syntax
        // $conffiles['configname']['path']		= dirname(__FILE__)."/config/configname.cfg.php";
        // $conffiles['configname']['content'] 	= "content";

        // Do the voodoo
        foreach ($conffiles as $confname=>$conffile) {
            echo "<div class='checkfor'>" . $conffile['path'] . " ... </div>";

            if (!file_exists($conffile['path']) || (isset($_GET['overwrite']) && $_GET['overwrite'] == $confname)) {
                if ($filehandle = fopen($conffile['path'], "w")) {
                    if (fwrite($filehandle, $conffile['content']) !== false) {
                        echo "<div class='ok'>OK!</div>";
                    } else {
                        echo "<div class='notok'>Not OK! File " . $conffile['path'] ." exists and is not writeable!
                                Please make this File writeable by the Webserver.</div>";
                        echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                                <input type='submit' value='Retry' class='retry'></form>";
                        break 2;
                    }
                } else {
                    echo "<div class='notok'>Not OK! Cannot open " . $conffile['path'] . "!
                            Please make sure this File is readable by the Webserver.</div>";
                    echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                            <input type='submit' value='Retry' class='retry'></form>";
                    break 2;
                }
            } elseif (file_exists($conffile['path'])) {
                $filecontent = file_get_contents($conffile['path']);

                if ($filecontent === $conffile['content']) {
                    // File has already the same content
                    echo "<div class='ok'>OK!</div>";
                    continue;
                }
                echo "<div class='ask'>File already exists. Overwrite?</div>";
                echo "<form action='install.php?step=" . ($_GET['step']) . "&overwrite=" . $confname . "' method='post'>
                        <input type='submit' value='Overwrite' class='retry'></form>";
                break;
            }
        }

        // ***************************************************************************************** //

        echo "<h4>Checking for required PHP-Extensions</h4>";

        $extensions = array();

        $extensions["JSON"] 					= "json";
        $extensions["Sessions"] 				= "Session";
        $extensions["XML"]						= "XML";
        $extensions["LibXML"]					= "libxml";
        $extensions["DomXML"]					= "dom";
        $extensions["GD Lib"]        			= "gd";
        $extensions["Multibyte String"]        = "mbstring";

        // To add more Extensions, just keep this syntax
        // $extensions['featuredescription']	= "Extensionname";

        // Do the voodoo
        foreach ($extensions as $description => $extension) {
            echo "<div class='checkfor'>" . $description . " ... </div>";

            if (extension_loaded($extension)) {
                echo "<div class='ok'>OK!</div>";
            } else {
                echo "<div class='notok'>Not OK! Cannot find Extension '" . $description . "'!
                        Please make sure this Feature is enabled by PHP.</div>";
                echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                        <input type='submit' value='Retry' class='retry'></form>";
                break 2;
            }

        }

        // ***************************************************************************************** //

        echo "<h4>Checking for required Libraries</h4>";

        // Load the Config generated in Step 2
        require_once("config/dirconf.cfg.php");

        $libraries = array();

        $libraries["PEAR Basic Package"] 					= "PEAR.php";
        $libraries["PEAR MDB2 Database Abstraction Layer"] 	= "MDB2.php";
        $libraries["PEAR MDB2 Database Schema Manager"] 	= "MDB2/Schema.php";
        $libraries["PEAR Log"]								= "Log.php";

        // To add more Libraries, just keep this syntax
        // $libraries['librarydescription']					= "libraryfile.php";

        // Do the voodoo
        foreach ($libraries as $description => $filename) {
            echo "<div class='checkfor'>" . $description . " ... </div>";

            $moduleok = false;
            $includepaths = explode(PATH_SEPARATOR, get_include_path());

            foreach ($includepaths as $include) {
                if (file_exists($include . "/" . $filename)) {
                    if (is_readable($include . "/" . $filename)) {
                        echo "<div class='ok'>OK!</div>";
                        $moduleok = true;
                        break;
                    } else {
                        echo "<div class='notok'>Not OK! Library found, but not readable!
                                Please make sure " . $filename .  " is readable by the Webserver.</div>";
                        echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                                <input type='submit' value='Retry' class='retry'></form>";
                        break 2;
                    }
                }
            }

            if (!$moduleok) {
                echo "<div class='notok'>Not OK! Library not found!
                            Please install '" . $description .  "'.</div>";
                echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                        <input type='submit' value='Retry' class='retry'></form>";
                break 2;
            }
        }

        echo "<div class='continue'>Continue to the next Step</div>";
        echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
                <input type='submit' value='Continue' class='continue'></form>";
        break;

    case 2:
        echo "<h2>Step " . $_GET['step'] .  " of 4 - Database Settings</h2>";
        echo "<h4>Checking for Database Information</h4>";

        // Include dirconf and global function library
        require_once("config/dirconf.cfg.php");
        require_once(DIR_INCLUDES."functions/global.func.php");
        require_once(DIR_INCLUDES."functions/database.func.php");

        // Set Autoloader
        spl_autoload_register("ruinsAutoload");

        if (isset($_GET['updateDBinfo'])) {
            echo "<div class='checkfor'>Updating Database Settings ... </div>";

            $dbconnect_content	=	"<?php" . "\n" .  "// created by installscript\n" .
                                    "\$dbconnect = array(\n" .
                                    "'driver' => '" . $_POST['driver'] . "',\n" .
                                    "'host' => '" . $_POST['host'] . "',\n";

            if (strlen($_POST['port'])) { // port is optional
                $dbconnect_content .= "'port' => '" . $_POST['port'] . "',\n";
            }

            $dbconnect_content .=   "'user' => '" . $_POST['user'] . "',\n" .
                                    "'password' => '" . $_POST['password'] . "',\n" .
                                    "'dbname' => '" . $_POST['dbname'] . "',\n" .
                                    "'prefix' => '" . $_POST['prefix'] . "',\n" .
                                    "'charset' => 'utf8',\n" .
                                    ");?>";

            if ($filehandle = fopen(DIR_CONFIG."dbconnect.cfg.php", "w")) {
                    if (fwrite($filehandle, $dbconnect_content) !== false) {
                        echo "<div class='ok'>New Database Settings written!</div>";
                    } else {
                        echo "<div class='notok'>Not OK! File " . DIR_CONFIG."dbconnect.cfg.php" ." exists and is not writeable!
                                Please make this File writeable by the Webserver.</div>";
                        echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                                <input type='submit' value='Retry' class='retry'></form>";
                        break 2;
                    }
                } else {
                    echo "<div class='notok'>Not OK! Cannot open " . DIR_CONFIG."dbconnect.cfg.php" . "!
                            Please make sure this File is readable by the Webserver.</div>";
                    echo "<form action='install.php?step=" . ($_GET['step']) .  "' method='post'>
                            <input type='submit' value='Retry' class='retry'></form>";
                    break 2;
                }
        }

        if (file_exists("config/dbconnect.cfg.php")) {
            echo "<div class='checkfor'>Try to connect using the existing Configuration File ... </div>";

            require_once(DIR_CONFIG."dbconnect.cfg.php");
            $needDBinfo = false;

            // CLEAR PREVIOUS CACHE
            SessionStore::pruneCache();

            // Try to connect using the given Data
            try {
                $database = getDBInstance();
            } catch (Doctrine\DBAL\DBALException $e) {
                $database = $e;
            } catch (PDOException $e) {
                $database = $e;
            }

            if ($database instanceof Doctrine\DBAL\Connection) {
                echo "<div class='ok'>OK!</div>";
            } else {
                // $database holds Exception Object
                echo "<div class='notok'>
                        Database Settings invalid!<br />
                        ErrorMessage: {$database->getMessage()}<br />
                        Please enter the correct Database Information:
                      </div>";
                $needDBinfo = true;
            }
        } else {
            echo "<div class='ask'>Database Settings don't exist. Please enter the Database Information:</div>";
            $needDBinfo = true;
        }

        if ($needDBinfo) {
            echo "<form action='install.php?step=" . ($_GET['step']) .  "&updateDBinfo' method='post'>
                    <table border='0'>
                    <tr>
                        <td>Databasetype:</td>
                        <td><select name='driver'>
                                <option value='pdo_mysql'>MySQL</option>
                                <option value='pdo_pgsql'>PostgreSQL</option>
                                <option value='pdo_sqlite'>SQLite</option>
                            </select>
                        </td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Type of Database Server
                        </td>
                    </tr><tr>
                        <td>Hostname:</td>
                        <td><input type='text' name='host'></td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Hostname of the Database Server. For example 'localhost', 'database.example.com' or '192.168.1.5'
                        </td>
                    </tr><tr>
                        <td>Port:</td>
                        <td><input type='text' name='port'></td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Keep empty for default
                        </td>
                    </tr><tr>
                        <td>Username:</td>
                        <td><input type='text' name='user'></td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Username to connect to the Database Server
                        </td>
                    </tr><tr>
                        <td>Password:</td>
                        <td><input type='password' name='password'></td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Password to connect to the Database Server
                        </td>
                    </tr><tr>
                        <td>Database:</td>
                        <td><input type='text' name='dbname'></td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Database to use on the Database Server
                        </td>
                    </tr><tr>
                        <td>Database Table Prefix:</td>
                        <td><input type='text' name='prefix'></td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Prefix of the Tables for this Project (Keep empty if not used). Examples: ruins__, ruinstest__, etc.
                        </td>
                    </tr>
                    </table>
                    <input type='submit' value='Update' class='retry'></form>";
        } else {
            echo "<div class='continue'>Continue to the next Step</div>";
            echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
                    <input type='submit' value='Continue' class='continue'></form>";
        }
        break;

    case 3:
        echo "<h2>Step " . $_GET['step'] .  " of 4 - Prepare Database</h2>";
        echo "<h4>Import initial Database</h4>";

        // Include dirconf, global function library and database information
        require_once("config/dirconf.cfg.php");
        require_once(DIR_INCLUDES."includes.inc.php");
        require_once(DIR_COMMON_EXTERNAL."doctrine2_init.php");

        // Set Autoloader
        spl_autoload_register("ruinsAutoload");

        if (isset($_GET['import'])) {
            echo "<div class='checkfor'>Import Initial Database ... </div>";

            $erroraccured = false;

            try {
                $validator = new \Doctrine\ORM\Tools\SchemaValidator($em);
                $error = $validator->validateMapping();
                if($error) var_dump($error);

                $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
                $metadata = $em->getMetadataFactory()->getAllMetadata();
                if (isset($_GET['force'])) {
                    $schemaTool->dropSchema($metadata);

                    $install_char = new Entities\Character;
                    $install_char->name = "Testuser";
                    $install_char->displayname = "`#35Testuser`#00";
                    $em->persist($install_char);

                    $install_settings = new Entities\UserSetting;
                    $install_settings->default_character = $install_char;
                    $em->persist($install_settings);

                    $install_user = new Entities\User;
                    $install_user->login = "test";
                    $install_user->password = md5("test");
                    $install_user->character = $install_char;
                    $install_user->settings  = $install_settings;
                    $em->persist($install_user);

                    // Reverse Mappings
                    $install_char->user     = $install_user;
                    $install_settings->user = $install_user;
                }
                $schemaTool->updateSchema($metadata);

                $em->flush();
            } catch (Exception $e) {
                echo "<div class='notok'>Update failed! (" . $e->getMessage() . ") Force Overwrite (Destroy all Data and install clean Database!)?</div>";
                echo "<form action='install.php?step=" . ($_GET['step']) . "&import=true&force=true' method='post'>
                        <input type='submit' value='Force overwrite' class='retry'></form>";

                break;
            }

            echo "<div class='ok'>OK!</div>";
            echo "<div class='continue'>Continue to the next Step</div>";
            echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
                <input type='submit' value='Continue' class='continue'></form>";

            break;
        }

        echo "<div class='checkfor'>Check for already existing Tables ... </div>";

        $oldtablesfound = false;

        // Try to connect using the given Data
        $database = getDBInstance();

        $tablelist = $database->getSchemaManager()->listTables();

        foreach ($tablelist as $table) {
            if ( (strlen($dbconnect['prefix']) && strpos($table->getName(), $dbconnect['prefix']) === 0) ||
                  strlen($dbconnect['prefix']) === 0 ) {
                echo "<div class='notok'>Already existing Table found: " . $table->getName() . "</div>";
                $oldtablesfound = true;
            }
        }

        if ($oldtablesfound) {
            echo "<div class='ask'>More than 1 already existing Table found! I will try to update these Tables.</div>";
            echo "<form action='install.php?step=" . ($_GET['step']) . "&import=true' method='post'>
                    <input type='submit' value='Start Import' class='continue'></form>";
        } else {
            echo "<div class='ok'>No existing Tables found. Please press 'Start Import' to import the initial Database</div>";
            echo "<form action='install.php?step=" . ($_GET['step']) . "&import=true&force=true' method='post'>
                    <input type='submit' value='Start Import' class='continue'></form>";
        }
        break;
/*
    case 4:
        echo "<h2>Step " . $_GET['step'] .  " of 4 - Initialize Modules</h2>";
        echo "<h4>Initialize Modules</h4>";

        // Include dirconf, global function library and database information
        require_once("config/dirconf.cfg.php");
        require_once(DIR_INCLUDES."includes.inc.php");

        // Set Autoloader
        spl_autoload_register("ruinsAutoload");

        echo "<div class='checkfor'>Sync ModuleList to Database ... </div>";

        // Generate Module List
        if (ModuleSystem::syncModuleListToDatabase()) {
            echo "<div class='ok'>OK!</div>";
        } else {
            echo "<div class='notok'>NOT OK! Failure during initial ModuleList Sync!</div>";
            echo "<form action='install.php?step=" . ($_GET['step']) . "&import=true' method='post'>
                    <input type='submit' value='Retry' class='retry'></form>";
            break;
        }

        echo "<div class='checkfor'>Initialize Modules ... </div>";

        // Initialize Modules
        if ($moduleList = ModuleSystem::getModuleListFromDatabase()) {
            if (is_array($moduleList)) {
                foreach ($moduleList as $module) {
                    if (ModuleSystem::installModule($module['type'], $module['filesystemname'])) {
                        echo "<div class='ok'>" . $module['name'] . " ... OK!</div>";
                    } else {
                        echo "<div class='notok'>Module Initialization Error: " . $module['name'] . "</div>";
                    }
                }
            }
            echo "<div class='continue'>Continue to the next Step</div>";
            echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
                    <input type='submit' value='Continue' class='continue'></form>";
        } else {
            echo "<div class='ask'>No Modules found! Continue?</div>";
            echo "<form action='install.php?step=" . ($_GET['step']) . "&import=true' method='post'>
                    <input type='submit' value='Retry' class='retry'></form>";
            echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
                    <input type='submit' value='Continue' class='continue'></form>";
            break;
        }

        break;

*/

    case 4:
        echo "<h2>Installation complete</h2>";
        echo "<h4>Congratulations, the Installation is complete! Press 'Continue' to load the Frontpage of Ruins.</h4>";

        echo "<form action='index.php' method='post'>
                <input type='submit' value='Continue' class='continue'></form>";
        break;

}


?>

</body>
</html>
