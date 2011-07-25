<?php
/**
 * Update Settings
 *
 * Change User/Character Settings through Ajax
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Json\Main;
use Ruins\Main\Manager\SystemManager;
use Ruins\Common\Controller\AbstractPageObject;
use Ruins\Common\Controller\SessionStore;
use Ruins\Common\Controller\Registry;

class UpdateSettingsJson extends AbstractPageObject
{
    /**
     * @see Ruins\Common\Interfaces.PageObjectInterface::createContent()
     */
    public function createContent($page, $parameters)
    {
        $em = Registry::getEntityManager();
        $user = Registry::getUser();

        if ($user) {
            $qb = $em->createQueryBuilder();

            $userconfig = $qb   ->select("settings")
                                ->from("Main:UserSetting", "settings")
                                ->where("settings.user = ?1")->setParameter(1, $user)
                                ->getQuery()
                                ->getOneOrNullResult();
        }

        if (!$user || is_null($userconfig) || !$parameters['settingsobject']) {
            $page->output(false);
            return;
        }

        if ($parameters['settingsobject'] === 'user') {
            if (isset($parameters['arrayaction'])) {
                $arraySetting = $userconfig->$parameters['setting'];

                switch ($parameters['arrayaction']) {
                    case "add":
                        if (array_search($parameters['data'], $arraySetting) === false) {
                            array_push($arraySetting, $parameters['data']);
                        }
                        break;
                    case "remove":
                        if (($pos = array_search($parameters['data'], $arraySetting)) !== false) {
                            unset($arraySetting[$pos]);
                        }
                        break;
                }

                $userconfig->$parameters['setting'] = $arraySetting;
            } else {
                $userconfig->$parameters['setting'] = $parameters['data'];
            }
        }

        $em->flush();

        $page->output(true);
    }
}