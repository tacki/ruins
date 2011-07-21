<?php
/**
 * Security Class
 *
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2011 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Controller;
use Ruins\Main\Manager\RightsManager;

use Ruins\Common\Manager\RequestManager;
use Ruins\Common\Controller\Request;
use Ruins\Common\Interfaces\UserInterface;
use Ruins\Common\Interfaces\NavigationInterface;

/**
 * Class Name
 * @package Ruins
 */
class Firewall
{
    /**
     * Check current Request against Navigation Object
     * @param Request $request
     * @param NavigationInterface $navigation
     */
    public function checkRequestAllowed(UserInterface $user, Request $request)
    {
        $savedNavigation = $user->getCharacter()->getAllowedNavigation();

        foreach ($savedNavigation->getLinkList() as $allowedNavigation) {
            if ($allowedNavigation['url'] == RequestManager::getWebBasePath() . "/" . $request->getCompleteRequest()) {
                // Navigation is in our Navigation-List
                return true;
            }
        }

        return false;
    }

    /**
     * Filter Navigation-Entries that are restricted to the given User
     * @param UserInterface $user
     * @param NavigationInterface $navigation
     * @return NavigationInterface
     */
    public function filterNavigationRestriction(UserInterface $user, NavigationInterface $navigation)
    {
        $result = array();

        foreach ($navigation->getLinkList() as $link) {
            if (isset($link['restriction'])) {
                if (RightsManager::isInGroup($link['restriction'], $user->getCharacter())) {
                    $result[] = $link;
                }
            } else {
                $result[] = $link;
            }
        }

        $navigation->setLinkList($result);

        return $navigation;
    }
}