<?php
/**
 * Namespaces
 */
namespace Ruins\Main\Entities;
use Ruins\Common\Interfaces\UserInterface;
use DateTime;
use Ruins\Main\Entities\EntityBase;
use Ruins\Common\Controller\SessionStore;
use Ruins\Common\Controller\Registry;

/**
 * @Entity(repositoryClass="Ruins\Main\Repositories\UserRepository")
 * @Table(name="users")
 */
class User extends EntityBase implements UserInterface
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @Column(length=32, unique=true)
     * @var string
     */
    protected $login;

    /**
     * @Column(length=255)
     * @var string
     */
    protected $password;

    /**
     * @OneToOne(targetEntity="Character")
     * @var Ruins\Main\Entities\Character
     */
    protected $character;

    /**
     * @OneToOne(targetEntity="UserSetting")
     * @var Ruins\Main\Entities\UserSetting
     */
    protected $settings;

    /**
     * @OneToMany(targetEntity="DebugLog", mappedBy="user")
     * @var Ruins\Main\Entities\DebugLog
     */
    protected $debuglog;

    /**
     * @OneToMany(targetEntity="UserIP", mappedBy="user")
     * @var Ruins\Main\Entities\UserIP
     */
    protected $iplist;

    /**
     * @OneToMany(targetEntity="UserUniqueID", mappedBy="user")
     * @var Ruins\Main\Entities\UserUniqueID
     */
    protected $uniqueidlist;


   /**
     * Care about everything needed for login
     */
    public function login()
    {
        // Set Session User ID
        SessionStore::set('userid', $this->id);

        // Check and update UniqueID-List if needed
        $this->checkUniqueID();

        // Add IP Address to the List
        $this->addIPAddress();

        // add default Character (if any)
        if ($this->settings->default_character) {
            $this->character = $this->settings->default_character;
        }
    }

    /**
     * Care about everything needed for logout
     */
    public function logout()
    {
        $systemCache = Registry::get('main.cache');

        // Unload $user->character
        $this->character = NULL;

        // unset Session User ID
        SessionStore::remove('userid');

        // Clear Cache
        $systemCache->deleteAll();
    }

    /**
     * Add the IP Address to the List
     */
    public function addIPAddress()
    {
        $em = Registry::getEntityManager();

        $lastIP     = $this->iplist->last()->ip;
        $requestIP  = $this->_getRequestTrueIP();

        if (is_null($lastIP) || ($lastIP != $requestIP) ) {
            // Add if IP has changed
           $newIP = new UserIP;
           $newIP->user = $this;
           $newIP->ip = $requestIP;
           $em->persist($newIP);
        }
    }

    /**
     * Returns the "true" IP address of the current request
     *
     * @return string the ip of the user
     */
    private function _getRequestTrueIP()
    {
        global $REMOTE_ADDR, $HTTP_CLIENT_IP;
        global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
        global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;

        // Get some server/environment variables values
        if (empty($REMOTE_ADDR)) {
            if (!empty($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
            } elseif (!empty($_ENV) && isset($_ENV['REMOTE_ADDR'])) {
                $REMOTE_ADDR = $_ENV['REMOTE_ADDR'];
            } elseif (@getenv('REMOTE_ADDR')) {
                $REMOTE_ADDR = getenv('REMOTE_ADDR');
            }
        }

        if (empty($HTTP_CLIENT_IP)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_CLIENT_IP'])) {
                $HTTP_CLIENT_IP = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_CLIENT_IP'])) {
                $HTTP_CLIENT_IP = $_ENV['HTTP_CLIENT_IP'];
            } elseif (@getenv('HTTP_CLIENT_IP')) {
                $HTTP_CLIENT_IP = getenv('HTTP_CLIENT_IP');
            }
        }

        if (empty($HTTP_X_FORWARDED_FOR)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
                $HTTP_X_FORWARDED_FOR = $_ENV['HTTP_X_FORWARDED_FOR'];
            } elseif (@getenv('HTTP_X_FORWARDED_FOR')) {
                $HTTP_X_FORWARDED_FOR = getenv('HTTP_X_FORWARDED_FOR');
            }
        }

        if (empty($HTTP_X_FORWARDED)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $_SERVER['HTTP_X_FORWARDED'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_X_FORWARDED'])) {
                $HTTP_X_FORWARDED = $_ENV['HTTP_X_FORWARDED'];
            } elseif (@getenv('HTTP_X_FORWARDED')) {
                $HTTP_X_FORWARDED = getenv('HTTP_X_FORWARDED');
            }
        }

        if (empty($HTTP_FORWARDED_FOR)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $_SERVER['HTTP_FORWARDED_FOR'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED_FOR'])) {
                $HTTP_FORWARDED_FOR = $_ENV['HTTP_FORWARDED_FOR'];
            } elseif (@getenv('HTTP_FORWARDED_FOR')) {
                $HTTP_FORWARDED_FOR = getenv('HTTP_FORWARDED_FOR');
            }
        }

        if (empty($HTTP_FORWARDED)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $_SERVER['HTTP_FORWARDED'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_FORWARDED'])) {
                $HTTP_FORWARDED = $_ENV['HTTP_FORWARDED'];
            } elseif (@getenv('HTTP_FORWARDED')) {
                $HTTP_FORWARDED = getenv('HTTP_FORWARDED');
            }
        }

        if (empty($HTTP_VIA)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_VIA'])) {
                $HTTP_VIA = $_SERVER['HTTP_VIA'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_VIA'])) {
                $HTTP_VIA = $_ENV['HTTP_VIA'];
            } elseif (@getenv('HTTP_VIA')) {
                $HTTP_VIA = getenv('HTTP_VIA');
            }
        }

        if (empty($HTTP_X_COMING_FROM)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $_SERVER['HTTP_X_COMING_FROM'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_X_COMING_FROM'])) {
                $HTTP_X_COMING_FROM = $_ENV['HTTP_X_COMING_FROM'];
            } elseif (@getenv('HTTP_X_COMING_FROM')) {
                $HTTP_X_COMING_FROM = getenv('HTTP_X_COMING_FROM');
            }
        }

        if (empty($HTTP_COMING_FROM)) {
            if (!empty($_SERVER) && isset($_SERVER['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $_SERVER['HTTP_COMING_FROM'];
            } elseif (!empty($_ENV) && isset($_ENV['HTTP_COMING_FROM'])) {
                $HTTP_COMING_FROM = $_ENV['HTTP_COMING_FROM'];
            } elseif (@getenv('HTTP_COMING_FROM')) {
                $HTTP_COMING_FROM = getenv('HTTP_COMING_FROM');
            }
        }

        // Gets the default ip sent by the user
        if (!empty($REMOTE_ADDR)) {
            $direct_ip = $REMOTE_ADDR;
        }

        // Gets the proxy ip sent by the user
        $proxy_ip     = '';
        if (!empty($HTTP_X_FORWARDED_FOR)) {
            $proxy_ip = $HTTP_X_FORWARDED_FOR;
        } elseif (!empty($HTTP_X_FORWARDED)) {
            $proxy_ip = $HTTP_X_FORWARDED;
        } elseif (!empty($HTTP_FORWARDED_FOR)) {
            $proxy_ip = $HTTP_FORWARDED_FOR;
        } elseif (!empty($HTTP_FORWARDED)) {
            $proxy_ip = $HTTP_FORWARDED;
        } elseif (!empty($HTTP_VIA)) {
            $proxy_ip = $HTTP_VIA;
        } elseif (!empty($HTTP_X_COMING_FROM)) {
            $proxy_ip = $HTTP_X_COMING_FROM;
        } elseif (!empty($HTTP_COMING_FROM)) {
            $proxy_ip = $HTTP_COMING_FROM;
        }

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
        }
    }

    /**
     * Add a new UniqueID to the List
     */
    public function checkUniqueID()
    {
        $em = Registry::getEntityManager();

        $lastID = $this->uniqueidlist->last()->uniqueid;

        if (!isset($_COOKIE['ruins_uniqueid']) || strlen($_COOKIE['ruins_uniqueid']) != 32) { // 32=Size of MD5Hash
            // No Cookie or invalid Cookie is set
            if ($lastID !== $_COOKIE['ruins_uniqueid']) {
                // Generate a new Unique ID and add it to the List. Also set
                // a new Cookie
               $newID = new UserUniqueID;
               $newID->user = $this;
               $newID->uniqueid = $this->_generateUniqueID();
               $em->persist($newID);

                // Add Logentry
                $this->addDebugLog("New UniqueID generated!");
            } else {
                // Update Cookie with UniqueID from Database
                // Used for special tracking-ids, shorter than 32 chars
                $_COOKIE['ruins_uniqueid'] = $lastID;
            }
        } elseif ($lastID !== $_COOKIE['ruins_uniqueid']) {
            // A Cookie is set, add to DB because it has changed
            $newID = new UserUniqueID;
            $newID->user = $this;
            $newID->uniqueid = $_COOKIE['ruins_uniqueid'];
            $em->persist($newID);
        }
    }

    /**
     * Generate a new UniqueID
     * @return string Unique ID
     */
    private function _generateUniqueID()
    {
        $uniqueID = md5(microtime());

        return $uniqueID;
    }

    /**
     * Check if the Character has a connection Timeout
     * @return bool the age of the last pagehit in minutes if timeout occurred, else false
     */
    public function hasConnectionTimeout()
    {
        $systemConfig = Registry::getMainConfig();

        // return false if lastpagehit is not set
        if (!isset($this->character->lastpagehit)) {
            return false;
        }

        // get last pagehit in minutes
        $lastpagehit = $this->character->lastpagehit->diff(new DateTime())->format("%i");

        // default connectiontimeout is 15 Minutes
        if ($lastpagehitage > $systemConfig->get("connectiontimeout", 15) ) {
            // connection timout occurred!
            // return age of last pagehit (in Minutes)
            return $lastpagehitage;
        } else {
            // no connection timeout (pagehit is in time)
            return false;
        }
    }

    public function addDebugLog($text)
    {
        $em = Registry::getEntityManager();

        $logentry = new DebugLog;
        $logentry->user = $this;
        $logentry->text = $text;
        $em->persist($logentry);
    }
}
?>