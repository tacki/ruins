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
use Common\Controller\Registry;

/**
 * System Class
 *
 * Class to manage Cities and other Environmentals
 * @package Ruins
 */
class System
{

    /**
     * Add Site to System
     * @param string $name
     * @param string $description
     * @param array $coords
     * @return Main\Entities\Site
     */
    public static function addSite($name, $description, array $coords)
    {
        $em = Registry::getEntityManager();

        if (!($site = $em->getRepository("Main:Site")->findOneByName($name))) {
            $site = new Entities\Site;
            $site->name        = $name;
            $site->description = $description;

            $waypoint          = new Entities\Waypoint;
            $waypoint->site    = $site;
            $waypoint->x       = $coords[0];
            $waypoint->y       = $coords[1];
            $waypoint->z       = $coords[2];

            $site->waypoint    = $waypoint;

            // Will cascade to Waypoint
            $em->persist($site);
        }

        return $site;
    }

    /**
     * Connect 2 Sites
     * @param Main\Entities\Site $site1
     * @param Main\Entities\Site $site2
     * @param int $difficulty
     * @return Main\Entities\WaypointConnection
     */
    public static function addSiteConnection(Entities\Site $site1, Entities\Site $site2, $difficulty=0)
    {
        $em = Registry::getEntityManager();

        if (!($wp_conn = $em->getRepository("Main:WaypointConnection")->findOneBy(array("start" => $site1->waypoint->id, "end" => $site2->waypoint->id)))) {
            $wp_conn             = new Entities\WaypointConnection;
            $wp_conn->start      = $site1;
            $wp_conn->end        = $site2;
            $wp_conn->difficulty = $difficulty;

            $em->persist($wp_conn);
        }

        return $wp_conn;
    }

    /**
     * Add an Administration Page
     * @param string $name
     * @param string $category
     * @param string $page
     */
    public static function addAdminPage($name, $category, $page)
    {
        $em = Registry::getEntityManager();

        if (!($em->getRepository("Main:Administration")->findOneBy(array("category" => $category, "page" => $page)))) {
            $administration           = new Entities\Administration;
            $administration->name     = (string)$name;
            $administration->category = (string)$category;
            $administration->page     = (string)$page;

            $em->persist($administration);
        }

    }

    /**
     * Return Administration Categories
     * @return array
     */
    public static function getAdminCategories()
    {
        $em = Registry::getEntityManager();

        $qb = $em->createQueryBuilder();

        $result = $qb   ->select("DISTINCT admin.category")
                        ->from("Main:Administration", "admin")
                        ->getQuery()->getResult();

        $categories = array();
        foreach ($result as $entry) {
            $categories[] = $entry['category'];
        }

        return $categories;
    }

    /**
     * Return Adminpages of a given category
     * @param string $category
     * @return array
     */
    public static function getAdminCategoryPages($category)
    {
        $em = Registry::getEntityManager();

        return $em->getRepository("Main:Administration")->findByCategory($category);
    }

    /**
    * Returns the currently active Output Object (prefers $page)
    * @return OutputObject Instance of OutputObject
    */
    public static function getOutputObject()
    {
        global $page;
        global $popup;

        if ($page instanceof \Common\Interfaces\OutputObject) {
            $outputobject =	$page;
        } elseif ($popup instanceof \Common\Interfaces\OutputObject) {
            $outputobject = $popup;
        } else {
            $outputobject = false;
        }

        return $outputobject;
    }

    /**
     * Get Filepath of an overloaded File
     * @param string $path Relative Filepath (e.g. View/Images/trash.png)
     * @param bool $htmlpath Return as htmlpath (relative to Doctree)
     * @return string Full Path of the overloaded File (e.g. .../ruins/Common/View/trash.png)
     */
    public static function getOverloadedFilePath($path, $htmlpath=false)
    {
        $systemCache = Registry::get('main.cache');

        if (!($result = $systemCache->fetch("overloadedFilePath_".md5($path)))) {
            // First Check Module-Directory
            foreach (\Main\Manager\Module::getModuleListFromFilesystem() as $module) {
                if (file_exists(DIR_MODULES.$module['directory'].$path)) {
                    $result = DIR_MODULES.$module['directory'].$path;
                    $systemCache->save("overloadedFilePath_".md5($path), $result);
                    return $htmlpath?\Main\Manager\System::htmlpath($result):$result;
                }
            }


            if (file_exists(DIR_MAIN.$path)) {
                // No Result? Check Main-Tree
                $result = DIR_MAIN.$path;
            } elseif (file_exists(DIR_COMMON.$path)) {
                // Hmm - Common-Tree?
                $result = DIR_COMMON.$path;
            } elseif (file_exists(DIR_BASE.$path)) {
                // Last Chance - Tree is already in path
                $result = DIR_BASE.$path;
            }

            if ($result) {
                $systemCache->save("overloadedFilePath_".md5($path), $result);
                return $htmlpath?\Main\Manager\System::htmlpath($result):$result;
            } else {
                throw new Error("Cannot find $path in any known Directory");
            }
        } else {
            // Use SessionStore
            return $htmlpath?\Main\Manager\System::htmlpath($result):$result;
        }
    }

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

        $htmlpath = str_replace($document_root , '', $realpath);

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
            // Get Overloaded Filepath
            $treepath = \Main\Manager\System::getOverloadedFilePath("Area/".$filepath);
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
        $em = Registry::getEntityManager();
        $user = Registry::getUser();

        $newnews = new Entities\News;
        $newnews->title   = $title;
        $newnews->body    = $body;
        $newnews->author  = $user->character;

        if($area) $newnews->area = $area;

        $em->persist($newnews);
    }

    public static function getNews($area="GLOBAL", $orderDir="DESC")
    {
        $em = Registry::getEntityManager();

        $qb = $em->createQueryBuilder();

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
        $em = Registry::getEntityManager();

        // get HumanReadable from Systemname
        $qb = $em->createQueryBuilder();

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
