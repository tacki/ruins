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

    <script type="text/javascript" src="Common/View/JavaScript/jquery-1.5.1.min.js "></script>
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
        $createdirs['Dummy Dir'] 	            = "tmp/dummy";
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
                                                        "/Main/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_AREA\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/area/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_CONFIG\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/config/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_INCLUDES\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/includes/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_COMMON\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/Common/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_EXTERNAL\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/External/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_TEMP\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/tmp/".
                                                        "\");"."\n" .
                                                    "	define(\"DIR_MODULES\", ".
                                                        "\"".str_replace('\\', '/', dirname(__FILE__)).
                                                        "/Modules/".
                                                        "\");".
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
        $extensions["Multibyte String"]         = "mbstring";

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

        echo "<h4>Checking for optional PHP-Extensions</h4>";

        // Include dirconf
        require_once("config/dirconf.cfg.php");
        // Include Common\Controller\Config Class-Definition
        require_once(DIR_BASE."Common/Controller/Config.php");


        $extensions = array();

        $extensions["APC Caching Driver"]       = "apc";
        $extensions["Memcache Caching Driver"]  = "memcache";
        $extensions["Xcache Caching Driver"]    = "xcache";

        // To add more Extensions, just keep this syntax
        // $extensions['featuredescription']	= "Extensionname";

        // Do the voodoo
        $systemConfig = new Common\Controller\Config;
        foreach ($extensions as $description => $extension) {
            echo "<div class='checkfor'>" . $description . " ... </div>";

            if (extension_loaded($extension)) {
                echo "<div class='ok'>OK!</div>";
                $systemConfig->setSub("option", $extension, 1);
            } else {
                echo "<div class='ask'>Not found! Extension '" . $description . "' is not available!</div>";
                $systemConfig->setSub("option", $extension, 0);
            }

        }

        echo "<div class='continue'>Continue to the next Step</div>";
        echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
                <input type='submit' value='Continue' class='continue'></form>";
        break;

    case 2:
        echo "<h2>Step " . $_GET['step'] .  " of 4 - Database Settings</h2>";
        echo "<h4>Checking for Database Information</h4>";

        // Include dirconf
        require_once("config/dirconf.cfg.php");

        if (isset($_GET['updateDBinfo'])) {
            echo "<div class='checkfor'>Updating Database Settings ... </div>";


            $dbconnect_content	=	"<?php" . "\n" .  "// created by installscript\n" .
                                    "\$dbconnect = array(\n" .
                                        "'driver' => '" . $_POST['driver'] . "',\n";

            switch ($_POST['driver']) {

                case "pdo_mysql":
                    if (strlen($_POST['unix_socket'])) {
                        $dbconnect_content .= "'unix_socket' => '" . $_POST['unix_socket'] . "',\n";
                    } else {
                        $dbconnect_content .= "'host' => '" . $_POST['host'] . "',\n";
                        if (strlen($_POST['port'])) {
                            // port is optional
                            $dbconnect_content .= "'port' => '" . $_POST['port'] . "',\n";
                        }
                    }
                    $dbconnect_content .=   "'user' => '" . $_POST['user'] . "',\n" .
                                            "'password' => '" . $_POST['password'] . "',\n" .
                                            "'dbname' => '" . $_POST['dbname'] . "',\n" .
                                            "'prefix' => '" . $_POST['prefix'] . "',\n" .
                                            "'charset' => 'utf8',\n";
                    break;

                case "pdo_pgsql":
                    $dbconnect_content .= "'host' => '" . $_POST['host'] . "',\n";
                    if (strlen($_POST['port'])) {
                        // port is optional
                        $dbconnect_content .= "'port' => '" . $_POST['port'] . "',\n";
                    }
                    $dbconnect_content .=   "'user' => '" . $_POST['user'] . "',\n" .
                                            "'password' => '" . $_POST['password'] . "',\n" .
                                            "'dbname' => '" . $_POST['dbname'] . "',\n" .
                                            "'prefix' => '" . $_POST['prefix'] . "',\n";
                    break;

                case "pdo_sqlite":
                    $dbconnect_content .=   "'path' => '" . $_POST['path'] . "',\n" .
                                            "'prefix' => '" . $_POST['prefix'] . "',\n";
                    break;


            }

            $dbconnect_content .=  ");?>";

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

            // Include Standard Header
            require_once(DIR_BASE."main.inc.php");

            $needDBinfo = false;

            // Try to connect using the given Data
            global $dbconnect;

            try {
                $database = \Doctrine\DBAL\DriverManager::getConnection($dbconnect);
                $database->connect();
            } catch (Exception $e) {
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
            echo "
                    <form action='install.php?step=" . ($_GET['step']) .  "&updateDBinfo' method='post'>
                    <table border='0'>
                    <tr>
                        <td>Databasetype:</td>
                        <td><select name='driver' id='driver'>
                                <option value='pdo_mysql' selected='selected'>MySQL</option>
                                <option value='pdo_pgsql'>PostgreSQL</option>
                                <option value='pdo_sqlite'>SQLite</option>
                            </select>
                        </td>
                    </tr><tr>
                        <td colspan='2' class='description'>
                            Type of Database Server
                        </td>
                    </tr><tr class='sqlite'>
                        <td>Path:</td>
                        <td><input type='text' name='path'></td>
                    </tr><tr class='sqlite'>
                        <td colspan='2' class='description'>
                            Path to the SQLite-Database
                        </td>
                    </tr><tr class='mysql pgsql'>
                        <td>Hostname:</td>
                        <td><input type='text' name='host'></td>
                    </tr><tr class='mysql pgsql'>
                        <td colspan='2' class='description'>
                            Hostname of the Database Server. For example 'localhost', 'database.example.com' or '192.168.1.5'
                        </td>
                    </tr><tr class='mysql pgsql'>
                        <td>Port:</td>
                        <td><input type='text' name='port'></td>
                    </tr><tr class='mysql pgsql'>
                        <td colspan='2' class='description'>
                            Keep empty for default
                        </td>
                    </tr><tr class='mysql'>
                        <td>Unix Socket:</td>
                        <td><input type='text' name='unix_socket'></td>
                    </tr><tr class='mysql'>
                        <td colspan='2' class='description'>
                            Leave this empty if you're connecting via Hostname+Port
                        </td>
                    </tr><tr class='mysql pgsql'>
                        <td>Username:</td>
                        <td><input type='text' name='user'></td>
                    </tr><tr class='mysql pgsql'>
                        <td colspan='2' class='description'>
                            Username to connect to the Database Server
                        </td>
                    </tr><tr class='mysql pgsql'>
                        <td>Password:</td>
                        <td><input type='password' name='password'></td>
                    </tr><tr class='mysql pgsql'>
                        <td colspan='2' class='description'>
                            Password to connect to the Database Server
                        </td>
                    </tr><tr class='mysql pgsql'>
                        <td>Database:</td>
                        <td><input type='text' name='dbname'></td>
                    </tr><tr class='mysql pgsql'>
                        <td colspan='2' class='description'>
                            Database to use on the Database Server
                        </td>
                    </tr><tr class='mysql pgsql sqlite'>
                        <td>Database Table Prefix:</td>
                        <td><input type='text' name='prefix'></td>
                    </tr><tr class='mysql pgsql sqlite'>
                        <td colspan='2' class='description'>
                            Prefix of the Tables for this Project (Keep empty if not used). Examples: ruins__, ruinstest__, etc.
                        </td>
                    </tr>
                    </table>
                    <input type='submit' value='Update' class='retry'></form>


                    <script>
                        // Defaults to pdo_mysql
                        $('.pqsql').hide();
                        $('.sqlite').hide();
                        $('.mysql').show();

                        $('#driver').change(function() {
                            switch ($('#driver option:selected').val()) {
                                case 'pdo_mysql':
                                    $('.pqsql').hide();
                                    $('.sqlite').hide();
                                    $('.mysql').show();
                                    break;
                                case 'pdo_pgsql':
                                    $('.mysql').hide();
                                    $('.sqlite').hide();
                                    $('.pgsql').show();
                                    break;

                                case 'pdo_sqlite':
                                    $('.mysql').hide();
                                    $('.pqsql').hide();
                                    $('.sqlite').show();
                                    break;
                            }
                        });
                    </script>
            ";
        } else {
            echo "<div class='continue'>Continue to the next Step</div>";
            echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
                    <input type='submit' value='Continue' class='continue'></form>";
        }
        break;

    case 3:
        echo "<h2>Step " . $_GET['step'] .  " of 4 - Prepare Database</h2>";
        echo "<h4>Import initial Database</h4>";

        // Include Standard Header
        require_once("config/dirconf.cfg.php");
        require_once(DIR_BASE."main.inc.php");

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
        global $dbconnect;
        $database = \Doctrine\DBAL\DriverManager::getConnection($dbconnect);

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

    case 4:
        echo "<h2>Step " . $_GET['step'] .  " of 4 - Initialize Modules and setup initial values</h2>";
        echo "<h4>Initialize Modules</h4>";

        // Include Standard Header
        require_once("config/dirconf.cfg.php");
        require_once(DIR_BASE."main.inc.php");

        //---------
        echo "<div class='checkfor'>Sync ModuleList to Database ... </div>";

        // Sync Module List
        if (Main\Manager\Module::syncModuleListToDatabase()) {
            echo "<div class='ok'>OK!</div>";
        }

        echo "<div class='checkfor'>Modules found ... </div>";
        foreach(Main\Manager\Module::getModuleListFromDatabase() as $module) {
            echo "<div class='ok'>" . $module->name . "</div>";
        }

        //---------
        echo "<div class='checkfor'>Sync SkillList to Database ... </div>";

        // Sync Skill List
        if (Main\Manager\Battle::syncSkillListToDatabase()) {
            echo "<div class='ok'>OK!</div>";
        }

        echo "<div class='checkfor'>Skills found ... </div>";
        foreach(Main\Manager\Battle::getSkillListFromDatabase() as $skill) {
            echo "<div class='ok'>" . $skill->name . "</div>";
        }

        //---------
        echo "<h4>Initial Values</h4>";

        echo "<div class='checkfor'>Setup initial values ... </div>";

        $initFiles = array(
                            // Main Init-Files
                            DIR_COMMON."Setup/Initial.php",
                            DIR_MAIN."Setup/Initial.php",
                            DIR_MODULES."Setup/Initial.php",
                          );

        // Add possible Module-Initfiles
        foreach(Main\Manager\Module::getModuleListFromDatabase() as $module) {
            $initFiles[] = DIR_MODULES . $module->basedir."Setup/Initial.php";
        }

        foreach ($initFiles as $filename) {
            if (file_exists($filename)) {
                include_once($filename);
                echo "<div class='ok'>Calling " . $filename . "</div>";
            }
        }

        echo "<div class='continue'>Continue to the next Step</div>";
        echo "<form action='install.php?step=" . ($_GET['step']+1) .  "' method='post'>
              <input type='submit' value='Continue' class='continue'></form>";

        break;

    case 5:
        echo "<h2>Installation complete</h2>";
        echo "<h4>Congratulations, the Installation is complete! Press 'Continue' to load the Frontpage of Ruins.</h4>";

        echo "<form action='index.php' method='post'>
                <input type='submit' value='Continue' class='continue'></form>";
        break;

}


?>

</body>
</html>
