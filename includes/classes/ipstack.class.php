<?php
/**
 * IP Stack Class
 *
 * Class to handle IP Lists
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: ipstack.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * IP Stack Class
 *
 * Class to handle IP Lists
 * @package Ruins
 */
class IPStack extends DatedStackObject
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param int $maxelements Max. Size of the Stack
     */
    function __construct($maxelements=false)
    {
        parent::__construct($maxelements);
    }

    /**
     * Add IP to the IP-Stack
     */
    public function addIP()
    {
        $ip = $this->getTrueIP();

        if ($this->getLast("data") != $ip) {
            $this->add($ip);
        }
    }

    /**
     * Returns the "true" IP address of the current user
     *
     * @return string the ip of the user
     */
    public function getTrueIP()
    {
        global $REMOTE_ADDR, $HTTP_CLIENT_IP;
        global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
        global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;

        // Get some server/environment variables values
        if (empty($REMOTE_ADDR)) {
            if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
            }
            else if (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
            }
            else if (@getenv('REMOTE_ADDR')) {
                $REMOTE_ADDR = getenv('REMOTE_ADDR');
            }
        } // end if

        if (empty($HTTP_CLIENT_IP)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_CLIENT_IP'])) {
                $HTTP_CLIENT_IP = $_SERVER['HTTP_CLIENT_IP'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_CLIENT_IP'])) {
                $HTTP_CLIENT_IP = $_ENV['HTTP_CLIENT_IP'];
            }
            else if (@getenv('HTTP_CLIENT_IP')) {
                $HTTP_CLIENT_IP = getenv('HTTP_CLIENT_IP');
            }
        } // end if

        if (empty($HTTP_X_FORWARDED_FOR)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
            }
            else if (@getenv('HTTP_X_FORWARDED_FOR')) {
                $HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');
            }
        } // end if

        if (empty($HTTP_X_FORWARDED)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
            }
            else if (@getenv('HTTP_X_FORWARDED')) {
                $HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');
            }
        } // end if

        if (empty($HTTP_FORWARDED_FOR)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
            }
            else if (@getenv('HTTP_FORWARDED_FOR')) {
                $HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');
            }
        } // end if

        if (empty($HTTP_FORWARDED)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
            }
            else if (@getenv('HTTP_FORWARDED')) {
                $HTTP_FORWARDED = getenv('HTTP_FORWARDED');
            }
        } // end if

        if (empty($HTTP_VIA)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
                $HTTP_VIA = $_SERVER['HTTP_VIA'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
                $HTTP_VIA = $_ENV['HTTP_VIA'];
            }
            else if (@getenv('HTTP_VIA')) {
                $HTTP_VIA = getenv('HTTP_VIA');
            }
        } // end if
        if (empty($HTTP_X_COMING_FROM)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
            }
            else if (@getenv('HTTP_X_COMING_FROM')) {
                $HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');
            }
        } // end if
        if (empty($HTTP_COMING_FROM)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
            }
            else if (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
            }
            else if (@getenv('HTTP_COMING_FROM')) {
                $HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');
            }
        } // end if

        // Gets the default ip sent by the user
        if (!empty($REMOTE_ADDR)) {
            $direct_ip = $REMOTE_ADDR;
        }

        // Gets the proxy ip sent by the user
        $proxy_ip     = '';
        if (!empty($HTTP_X_FORWARDED_FOR)) {
            $proxy_ip = $HTTP_X_FORWARDED_FOR;
        } else if (!empty($HTTP_X_FORWARDED)) {
            $proxy_ip = $HTTP_X_FORWARDED;
        } else if (!empty($HTTP_FORWARDED_FOR)) {
            $proxy_ip = $HTTP_FORWARDED_FOR;
        } else if (!empty($HTTP_FORWARDED)) {
            $proxy_ip = $HTTP_FORWARDED;
        } else if (!empty($HTTP_VIA)) {
            $proxy_ip = $HTTP_VIA;
        } else if (!empty($HTTP_X_COMING_FROM)) {
            $proxy_ip = $HTTP_X_COMING_FROM;
        } else if (!empty($HTTP_COMING_FROM)) {
            $proxy_ip = $HTTP_COMING_FROM;
        } // end if... else if...

        // Returns the true IP if it has been found, else ...
        if (empty($proxy_ip)) {
            // True IP without proxy
            return $direct_ip;
        } else {
            $is_ip = ereg('^([0-9]{1,3}.){3,3}[0-9]{1,3}', $proxy_ip, $regs);

            if ($is_ip && (count($regs) > 0)) {
                // True IP behind a proxy
                return $regs[0];
            } else {

                if (empty($HTTP_CLIENT_IP)) {
                    // Can't define IP: there is a proxy but we don't have
                    // information about the true IP
                    return "(unbekannt) " . $proxy_ip;
                } else {
                    // better than nothing
                    return $HTTP_CLIENT_IP;
                }
            }
        } // end if... else...
    } // end of function

}
?>
