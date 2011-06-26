<?php
/**
 * Interface for Skills
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Interfaces;

/**
 * Interface for Skills
 * @package Ruins
 */
interface Skill
{
    /**
     * Skill initialization
     */
    public function init();

    /**
     * Skill Name
     */
    public function getName();

    /**
     * Skill Description
     */
    public function getDescription();

    /**
     * Skill Type
     */
    public function getType();

    /**
     * Number of possible Targets
     */
    public function getNrOfTargets();

    /**
     * Possible Targets
     */
    public function getPossibleTargets();
}