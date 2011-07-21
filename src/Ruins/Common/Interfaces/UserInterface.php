<?php
/**
 * Interface for Users
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Common\Interfaces;

/**
 * Interface for Users
 * @package Ruins
 */
interface UserInterface
{
    /**
     * Get Character
     * @return \Ruins\Main\Entities\Character
     */
    public function getCharacter();

    /**
     * Set Character
     * @param \Ruins\Main\Entities\Character $character
     */
    public function setCharacter($character);

    /**
     * Get User Settings
     * @return \Ruins\Main\Entities\UserSetting
     */
    public function getSettings();

    /**
     * Prepare User for normal usage
     */
    public function prepare();
}
?>