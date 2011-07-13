<?php
/**
 * Skill Repository
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Repositories;
use Exception;
use Main\Manager\System;


/**
 * Skill Repository
 * @package Ruins
 */
class SkillRepository extends Repository
{
    public function getController($skillname)
    {
        $entity = $this->findOneByName($skillname);

        $controller = new $entity->classname;

        return $controller;
    }

    /**
     * Get the List of known Skills from the Filesystem
     * @return array List of Skill FQ-Classnames
     */
    public function getListFromFilesystem()
    {
        $result = array();
        $dircontent = System::getDirList(DIR_MAIN."Controller/Skills");

        foreach ($dircontent['files'] as $filename) {
            if (strtolower(substr($filename, -4,4) == ".php")) {
                $classname = pathinfo($filename, \PATHINFO_FILENAME);
                $result[] = "Main\\Controller\\Skills\\".$classname;
            }
        }

        return $result;
    }

    /**
     * Synchronize the Skills existing at the Database with the Skills existing in our Directory
     * @return bool true if successful, else false
     */
    public function syncToDatabase()
    {
        $skillsFsList = $this->getListFromFilesystem();
        $skillsDbList = $this->findAll();

        if (empty($skillsFsList)) return false;

        foreach($skillsFsList as $skillFS) {
            $addFlag        = true;

            foreach($skillsDbList as $skillDB) {
                if ($skillDB->classname == $skillFS) {
                    $addFlag = false;
                }
            }

            if ($addFlag) {
                // execute init()-Method of unknown Skill
                try {
                    $skill = new $skillFS;
                    $skill->init();
                } catch (Exception $e) {
                    return false;
                }
            }
        }

        $this->getEntityManager()->flush();

        return true;
    }
}