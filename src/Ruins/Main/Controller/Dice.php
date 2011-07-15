<?php
/**
 * Dice Class
 *
 * Roll a Dice and get the Result
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: dice.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Main\Controller;

/**
 * Dice Class
 *
 * Roll a Dice and get the Result
 * @package Ruins
 */
class Dice
{
    /**
     * Roll a D4
     * @param int $times How many times
     * @return Result of the throw
     */
    public static function rollD4($times=1)
    {
        return self::_roll(4, $times);
    }

    /**
     * Roll a D6
     * @param int $times How many times
     * @return Result of the throw
     */
    public static function rollD6($times=1)
    {
        return self::_roll(6, $times);
    }

    /**
     * Roll a D8
     * @param int $times How many times
     * @return Result of the throw
     */
    public static function rollD8($times=1)
    {
        return self::_roll(8, $times);
    }

    /**
     * Roll a D10
     * @param int $times How many times
     * @return Result of the throw
     */
    public static function rollD10($times=1)
    {
        return self::_roll(10, $times);
    }

    /**
     * Roll a D12
     * @param int $times How many times
     * @return Result of the throw
     */
    public static function rollD12($times=1)
    {
        return self::_roll(12, $times);
    }

    /**
     * Roll a D20
     * @param int $times How many times
     * @return Result of the throw
     */
    public static function rollD20($times=1)
    {
        return self::_roll(20, $times);
    }

    /**
     * Roll a D100
     * @param int $times How many times
     * @return Result of the throw
     */
    public static function rollD100($times=1)
    {
        return self::_roll(100, $times);
    }

    /**
     * Generate Random Values for the throw
     * @param int $dicesides The nr. of Sides
     * @param int $times How many times
     * @return Result of the throw
     */
    private static function _roll($dicesides, $times)
    {
        $result = 0;

        for ($i=0; $i<$times; $i++) {
            $result += mt_rand(1, $dicesides);
        }

        return $result;
    }
}
?>
