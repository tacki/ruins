<?php
/**
 * File Functions
 *
 * Functions for Filehandling and more
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: file.func.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Creates the relative webbased path
 * @param string $file_path = filesystem path
 * @return string Relative Path
 */
function htmlpath($file_path)
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
function validatePHPFilePath($filepath)
{
    // create Full Path and check it
    $filepath = createFullPHPFilePath($filepath);

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
function createFullPHPFilePath($shortcut)
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
            $filepath = DIR_AREA . $filepath;
        }
    }

    // create realpath
    $filepath = realpath($filepath);

    // Windoof Fix
    $filepath = str_replace("\\","/", $filepath);

    // Add last / for directories and the parameters (if any) for files
    if (is_dir($filepath) && substr($filepath, 0, -1) != "/") {
        $filepath .= "/";
    } elseif ($parameters) {
        $filepath = $filepath . "&" . $parameters;
    }

    // return the complete Path
    return $filepath;
}

/**
 * Get Content of a given Directory
 * @param string $directory Full Path of the Directory
 * @param string $fullpath Show full Path in result Array
 * @return array|bool Array of Directories and Files or false if no directory given
 */
function getDirList($directory, $fullpath=false)
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

?>
