<?php
/**
 * System Class
 *
 * Class to manage Cities and other Environmentals
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Manager;
use Common\Controller\SessionStore,
    Common\Controller\Error,
    Main\Entities;

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * System Class
 *
 * Class to manage Cities and other Environmentals
 * @package Ruins
 */
class System
{
    /**
     * Creates the relative webbased path
     * @param string $file_path = filesystem path
     * @return string Relative Path
     */
    public static function htmlpath($file_path)
    {
        // extract arguments if there are some
        $divideargs = explode("?",$file_path,2);
        $realpath = $divideargs['0']; // the path

        if (isset($divideargs['1'])) $args=$divideargs['1']; // the arguments if there are any

        // ensure that there are no more ../ etc.
        if (realpath($realpath)) {
            $realpath = realpath($realpath);
        } else {
            throw new Error("File $realpath not found!");
        }

        // Windoof Fix
        $realpath = str_replace("\\","/", $realpath);

        // remove the DOCUMENT_ROOT
        // Ensure that there are no trailing slashes (/)
        $document_root = $_SERVER['DOCUMENT_ROOT'];
        while (substr($document_root, -1) == "/") {
            $document_root = substr($document_root, 0, -1);
        }

        $htmlpath = str_replace(strtolower($document_root) , '', strtolower($realpath));

        return $htmlpath.(isset($args)?"?".$args:"");
    }

    /**
     * Check if a file is valid and inside our Project
     * @param string $file_path File to check
     * @return string The complete and valid Filepath
     */
    public static function validatePHPFilePath($filepath)
    {
        // create Full Path and check it
        $filepath = self::createFullPHPFilePath($filepath);

        if ($filepath === false) {
            throw new Error("Invalid PHPFilePath! File not found!");
        }

        // check if the file is inside our Project
        $projectpath = DIR_BASE;
        $projectpathlength = strlen($projectpath);

        if (substr($filepath, 0, $projectpathlength) !== $projectpath) {
            // The File is not inside our Project...
            throw new Error("Invalid PHPFilePath! PHPFilePath not inside our Project!");
        }

        return $filepath;
    }

    /**
     * Create a complete Filepath of a shortcut (example: common/login or page=common/login)
     * @param string $shortcut
     * @return string The Filepath if successful, else false if file not found
     */
    public static function createFullPHPFilePath($shortcut)
    {
        $filepath = $shortcut;
        $parameters = false;

        // check for an existing 'page=' or 'popup=' in front of the shortcut (and remove it)
        if (strtolower(substr($filepath, 0, 5)) === "page=") {
            // remove 'page='
            $filepath = substr($filepath, 5);
        } elseif (strtolower(substr($filepath, 0, 6)) === "popup=") {
            // remove 'popup='
            $filepath = substr($filepath, 6);
        }

        // remove parameters (we'll add them later)
        if (strpos($filepath, "&")) {
            // strip all parameters
            $stripped = explode("&", $filepath, 2);
            $filepath = $stripped[0];
            $parameters = $stripped[1];
        } elseif (strpos($filepath, "?")) {
            // someone used ? as the seperator or inside the GET-string, which is invalid!
            throw new Error("The Character '?' isn't allowed inside the Navigation Path!
                             Use '&' for additional Parameters or '&amp;#63;' if you want
                             to use the '?' inside the GET-string");
        }

        // check if the shortcut already has a .php extension
        if (strtolower(substr($filepath, -4, 4)) !== ".php") {
            // add the extension
            $filepath = $filepath . ".php";
        }

        // check if filepath consists of dirname+"/"+filename (path to content)
        if (strpos($filepath, "/")) {
            // Check if this is a pathdescription to a file inside the area-tree
            if (file_exists(DIR_AREA . $filepath)) {
                // add DIR_AREA to create the full path
                $treepath = DIR_AREA . $filepath;
            }

            // Fetch Modules
            $moduleList = Module::getModuleListFromDatabase(true);

            // Modulepages overwrite Common
            foreach($moduleList as $module) {
                if (file_exists(DIR_MODULES.$module->basedir . "Area/" . $filepath)) {
                    $treepath = DIR_MODULES.$module->basedir . "Area/" . $filepath;
                }
            }
        }

        // create realpath
        $realpath = realpath($treepath);

        // Windoof Fix
        $realpath = str_replace("\\","/", $realpath);

        // Add last / for directories and the parameters (if any) for files
        if (is_dir($realpath) && substr($realpath, 0, -1) != "/") {
            $realpath .= "/";
        } elseif ($parameters) {
            $realpath = $realpath . "&" . $parameters;
        }

        // return the complete Path
        return $realpath;
    }

    /**
     * Get Content of a given Directory
     * @param string $directory Full Path of the Directory
     * @param string $fullpath Show full Path in result Array
     * @return array|bool Array of Directories and Files or false if no directory given
     */
    public static function getDirList($directory, $fullpath=false)
    {
        $directory = realpath($directory)."/";

        if (is_dir($directory)) {
            $dir 		= array();
            $dircontent = scandir($directory);

            foreach ($dircontent as $path) {
                if (substr($path, 0, 1) == ".") {
                    // Don't use .* Files/Directories
                    continue;
                }
                if (is_dir($directory . $path)) {
                    if ($fullpath) {
                        $path = $directory . $path;
                    }
                    $dir['directories'][] = $path;
                } elseif (is_file($directory . $path)) {
                    if ($fullpath) {
                        $path = $directory . $path;
                    }
                    $dir['files'][] = $path;
                }
            }

            return ($dir);
        } else {
            return false;
        }
    }

    public static function addNews($title, $body, $area=false)
    {
        global $em;
        global $user;

        $newnews = new Entities\News;
        $newnews->title   = $title;
        $newnews->body    = $body;
        $newnews->author  = $user->character;

        if($area) $newnews->area = $area;

        $em->persist($newnews);
    }

    public static function getNews($area="GLOBAL", $orderDir="DESC")
    {
        $qb = getQueryBuilder();

        $qb ->select("news")
            ->from("Main:News", "news")
            ->orderBy("news.date", $orderDir);

        if($area) $qb->where("news.area = ?1")->setParameter(1, $area);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Translate Systemname to human readable
     * @param string $name The Systemname to translate
     * @param bool $return_unknown Return 'Unknown' if the Systemname is not found or the Systemname itself
     * @return mixed The human readable form of the Systemname
     */
    public static function translate($name, $return_unknown=false)
    {
        // get HumanReadable from Systemname
        $qb = getQueryBuilder();

        $result = $qb   ->select("translate.humanreadable")
                        ->from("Main:Translation", "translate")
                        ->where("translate.system = ?1")->setParameter(1, $name)
                        ->getQuery()
                        ->getOneOrNullResult();

        if ($result) {
            return $result;
        } else {
            if ($return_unknown) {
                return "Unbekannt";
            } else {
                return $name;
            }
        }
    }
}
?>
