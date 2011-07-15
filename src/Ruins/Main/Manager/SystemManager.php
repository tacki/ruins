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
namespace Ruins\Main\Manager;
use Ruins\Common\Controller\SessionStore;
use Ruins\Common\Controller\Error;
use Ruins\Common\Interfaces\OutputObjectInterface;
use Ruins\Main\Entities\Administration;
use Ruins\Main\Entities\News;
use Ruins\Main\Entities\Site;
use Ruins\Main\Entities\Waypoint;
use Ruins\Main\Entities\WaypointConnection;
use Ruins\Main\Manager\ModuleManager;
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\Request;
use Ruins\Common\Manager\RequestHandler;

/**
 * System Class
 *
 * Class to manage Cities and other Environmentals
 * @package Ruins
 */
class SystemManager
{

    /**
     * Add Site to System
     * @param string $name
     * @param string $description
     * @param array $coords
     * @return Ruins\Main\Entities\Site
     */
    public static function addSite($name, $description, array $coords)
    {
        $em = Registry::getEntityManager();

        if (!($site = $em->getRepository("Main:Site")->findOneByName($name))) {
            $site = new Site;
            $site->name        = $name;
            $site->description = $description;

            $waypoint          = new Waypoint;
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
     * @param Ruins\Main\Entities\Site $site1
     * @param Ruins\Main\Entities\Site $site2
     * @param int $difficulty
     * @return Ruins\Main\Entities\WaypointConnection
     */
    public static function addSiteConnection(Site $site1, Site $site2, $difficulty=0)
    {
        $em = Registry::getEntityManager();

        if (!($wp_conn = $em->getRepository("Main:WaypointConnection")->findOneBy(array("start" => $site1->waypoint->id, "end" => $site2->waypoint->id)))) {
            $wp_conn             = new WaypointConnection;
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
            $administration           = new Administration;
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

        if ($page instanceof OutputObjectInterface) {
            $outputobject =	$page;
        } elseif ($popup instanceof OutputObjectInterface) {
            $outputobject = $popup;
        } else {
            $outputobject = false;
        }

        return $outputobject;
    }

    /**
     * Find correct Path of a Web Ressource (JS, CSS, Image, etc)
     * @param string $path Relative Filepath (e.g. common/images/trash.png)
     * @param bool $htmlpath Return as htmlpath (relative to Doctree)
     * @throws Error
     */
    public static function getWebRessourcePath($path, $htmlpath=false)
    {
        $systemCache = Registry::get('main.cache');

        if (!($result = $systemCache->fetch("webRessourcePath_".md5($path)))) {
            // First check Module-Directory for web-ressources
            foreach (ModuleManager::getModuleListFromFilesystem() as $module) {
                if (file_exists(DIR_MODULES.$module['directory'].'web/'.$path)) {
                    $result = DIR_MODULES.$module['directory'].'web/'.$path;
                    $systemCache->save("webRessourcePath_".md5($path), $result);

                    return $htmlpath?SystemManager::htmlpath($result):$result;
                }
            }


            if (file_exists(DIR_WEB."common/".$path)) {
                // No Result? Check Common Web-Directory
                $result = DIR_WEB."common/".$path;
            } elseif (file_exists(DIR_WEB."main/".$path)) {
                // Check Main Web-Directory
                $result = DIR_WEB."main/".$path;
            } elseif (file_exists(DIR_WEB.$path)) {
                // Last Chance - Full web Path is given
                $result = DIR_WEB.$path;
            }

            if ($result) {
                $systemCache->save("webRessourcePath_".md5($path), $result);

                return $htmlpath?SystemManager::htmlpath($result):$result;
            } else {
                throw new Error("Cannot find Web Ressource $path in any known Directory");
            }
        } else {
            // Use SessionStore
            return $htmlpath?SystemManager::htmlpath($result):$result;
        }
    }

    /**
     * Retrieve request filepath
     * @param Request $request
     * @param bool $addQuery
     * @return string
     */
    public static function getRequestFilePath(Request $request, $addQuery=false)
    {
        $caller     = $request->getRouteCaller();
        $parameters = current($request->getRoute());
        $filePath   = "Pages/".$request->getRouteCaller();
        $foundPath  = "";
        $counter    = 0;

        // Find Filename
        // TODO: NEEDS SOME CACHING
        for ($counter=0; $counter<count($parameters); $counter++) {
            $filePath .= "/" . $parameters[$counter];

            // First Check Module-Directory
            foreach (ModuleManager::getModuleListFromFilesystem() as $module) {
                if (file_exists(DIR_MODULES.$module['directory'].$filePath.".php")) {
                    $foundpath = DIR_MODULES.$module['directory'].$filePath.".php";
                    break 2;
                }
            }

            if (file_exists(DIR_COMMON.$filePath . ".php")) {
                $foundpath = DIR_COMMON.$filePath.".php";
                break;
            } elseif (file_exists(DIR_MAIN.$filePath . ".php")) {
                $foundpath = DIR_MAIN.$filePath.".php";
                break;
            }
        }

        if (!$addQuery) {
            return $foundpath;
        }

        // Add Rest of Parameters as opX-query
        for ($i=1; $counter<count($parameters); $counter++) {
            if (strstr($foundpath, "?") === false) {
                $foundpath .= "?op=".$parameters[$counter];
            } else {
                $foundpath .= "&op".$i."=".$parameters[$counter];
            }
            $i++;
        }

        return $foundpath;
    }

    /**
     * Get Filepath of an overloaded File
     * @param string $path Relative Filepath (e.g. View/Images/trash.png)
     * @param bool $htmlpath Return as htmlpath (relative to Doctree)
     * @throws Error
     * @return string Full Path of the overloaded File (e.g. .../ruins/Common/View/trash.png)
     */
    public static function getOverloadedFilePath($path, $htmlpath=false)
    {
        $systemCache = Registry::get('main.cache');

        if (!($result = $systemCache->fetch("overloadedFilePath_".md5($path)))) {
            // First Check Module-Directory
            foreach (ModuleManager::getModuleListFromFilesystem() as $module) {
                if (file_exists(DIR_MODULES.$module['directory'].$path)) {
                    $result = DIR_MODULES.$module['directory'].$path;
                    $systemCache->save("overloadedFilePath_".md5($path), $result);
                    return $htmlpath?SystemManager::htmlpath($result):$result;
                }
            }


            if (file_exists(DIR_MAIN.$path)) {
                // No Result? Check Main-Tree
                $result = DIR_MAIN.$path;
            } elseif (file_exists(DIR_COMMON.$path)) {
                // Hmm - Common-Tree?
                $result = DIR_COMMON.$path;
            } elseif (file_exists(DIR_BASE."src/Ruins/".$path)) {
                // Last Chance - Tree is already in path
                $result = DIR_BASE."src/Ruins/".$path;
            } elseif (file_exists(DIR_BASE.$path)) {
                // Last Chance - Tree is already in path
                $result = DIR_BASE.$path;
            }

            if ($result) {
                $systemCache->save("overloadedFilePath_".md5($path), $result);
                return $htmlpath?SystemManager::htmlpath($result):$result;
            } else {
                throw new Error("Cannot find $path in any known Directory");
            }
        } else {
            // Use SessionStore
            return $htmlpath?SystemManager::htmlpath($result):$result;
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
     * Create a complete Filepath of a shortcut (example: common/login or page/common/login)
     * @param string $filepath
     * @return string The Filepath if successful, else false if file not found
     */
    public static function createFullPHPFilePath($path)
    {
        if (!($path instanceof Request)) {
            $request = RequestHandler::getRequest($path);
        } else {
            $request = $path;
        }

        $treepath = SystemManager::getRequestFilePath($request);

        // strip query string
        if (strpos($treepath, "?")) {
            $stripped = explode("?", $treepath, 2);
            $treepath = $stripped[0];
            $query    = $stripped[1];
        }

        // create realpath
        $realpath = realpath($treepath);

        // Windoof Fix
        $realpath = str_replace("\\","/", $realpath);

        // Add last / for directories and the parameters (if any) for files
        if (is_dir($realpath) && substr($realpath, 0, -1) != "/") {
            $realpath .= "/";
        }

        if ($query) {
            $realpath = $realpath . "?" . $query;
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

        $newnews = new News;
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
