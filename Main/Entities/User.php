<?php
/**
 * Namespaces
 */
namespace Main\Entities;
use DateTime,
    Common\Controller\SessionStore;

/**
 * @Entity
 * @Table(name="users")
 */
class User extends EntityBase
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /** @Column(length=32, unique=true) */
    protected $login;

    /** @Column(length=32) */
    protected $password;

    /**
     * @OneToOne(targetEntity="Character")
     */
    protected $character;

    /**
     * @OneToOne(targetEntity="UserSetting")
     */
    protected $settings;

    /**
     * @OneToMany(targetEntity="DebugLog", mappedBy="user")
     */
    protected $debuglog;

    /**
     * @OneToMany(targetEntity="UserIP", mappedBy="user")
     */
    protected $iplist;

    /**
     * @OneToMany(targetEntity="UserUniqueID", mappedBy="user")
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
        // Unload $user->character
        $this->character = NULL;

        // unset Session User ID
        SessionStore::remove('userid');

        // prune Cache
        SessionStore::pruneCache();
    }

    /**
     * Add the IP Address to the List
     */
    public function addIPAddress()
    {
        global $em;

        $lastIP     = $this->iplist->last()->ip;
        $requestIP  = getRequestTrueIP();

        if (is_null($lastIP) || ($lastIP != $requestIP) ) {
            // Add if IP has changed
           $newIP = new UserIP;
           $newIP->user = $this;
           $newIP->ip = $requestIP;
           $em->persist($newIP);
        }
    }

    /**
     * Add a new UniqueID to the List
     */
    public function checkUniqueID()
    {
        global $em;

        $lastID = $this->uniqueidlist->last()->uniqueid;

        if (!isset($_COOKIE['ruins_uniqueid']) || strlen($_COOKIE['ruins_uniqueid']) != 32) { // 32=Size of MD5Hash
            // No Cookie or invalid Cookie is set
            if ($lastID !== $_COOKIE['ruins_uniqueid']) {
                // Generate a new Unique ID and add it to the List. Also set
                // a new Cookie
               $newID = new UserUniqueID;
               $newID->user = $this;
               $newID->uniqueid = generateUniqueID();
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
     * Check if the Character has a connection Timeout
     * @return bool the age of the last pagehit in minutes if timeout occurred, else false
     */
    public function hasConnectionTimeout()
    {
        global $config;

        // return false if lastpagehit is not set
        if (!isset($this->character->lastpagehit)) {
            return false;
        }

        // get last pagehit in minutes
        $lastpagehit = $this->character->lastpagehit->diff(new DateTime())->format("%i");

        // default connectiontimeout is 15 Minutes
        if ($lastpagehitage > $config->get("connectiontimeout", 15) ) {
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
        global $em;

        $logentry = new DebugLog;
        $logentry->user = $this;
        $logentry->text = $text;
        $em->persist($logentry);
    }
}
?>