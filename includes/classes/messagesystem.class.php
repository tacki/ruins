<?php
/**
 * MessageSystem Class
 *
 * Class to control the Messaging-System
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: messagesystem.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Class Defines
 */
define("MESSAGESYSTEM_STATUS_UNREAD", 0);
define("MESSAGESYSTEM_STATUS_READ", 1);
define("MESSAGESYSTEM_STATUS_REPLIED", 2);
define("MESSAGESYSTEM_STATUS_DELETED", 3);

/**
 * MessageSystem Class
 *
 * Class to control the Messaging-System
 * @package Ruins
 */
class MessageSystem
{
    /**
     * Write a normal Message
     * @param int $sender ID of the Sender
     * @param mixed $receivers Receivers ID OR array of ID's OR "all" for all ID's
     * @param string $subject Subject of the Message
     * @param string $text Messagetext
     * @param int ID of the newly created Message
     */
    public static function write($sender, $receivers, $subject, $text)
    {
        global $user;

        // Some Sanity-Checks
        if (!$subject) $subject=" ";
        if (!$text) $text=" ";

        $message = new Message;
        $message->create();

        // Fill in the Messagedata
        $message->sender = $sender;
        $message->subject = $subject;
        $message->text = $text;
        $message->date = date("Y-m-d H:i:s");
        $messageid = $message->save();

        // Add Messagerefs for the receivers
        if (is_array($receivers)) {
            // Array of ID's

            // Add DebugLogEntry
            if (isset ($user->char)) {
                $user->char->debuglog->add("Send Message to ids: ".implode(", ", $receivers), "veryverbose");
            }

            // Add Messageref
            $message->addReceiver($messageid, $receivers);
        } elseif (is_numeric($receivers)) {
            // Single ID

            // Add DebugLogEntry
            if (isset ($user->char)) {
                $user->char->debuglog->add("Send Message to $receivers", "veryverbose");
            }

            // Add Messageref
            $message->addReceiver($messageid, $receivers);
        } elseif (is_string($receivers) && strtolower($receivers) != "all") {
            // Charactername, separated by ;
            $characternames = explode(";", $receivers);

            // Add DebugLogEntry
            if (isset ($user->char)) {
                $user->char->debuglog->add("Send Message to ".implode(", ", $characternames), "veryverbose");
            }

            foreach ($characternames as $receivername) {
                // Add Messageref
                $message->addReceiver($messageid, UserSystem::getCharacterID(trim($receivername)));
            }
        } elseif (is_string($receivers) && strtolower($receivers) == "all") {
            // "all"
            // Get ID-List
            $dbqt = new QueryTool();
            $result = $dbqt	->select("id")
                            ->from("characters")
                            ->exec()
                            ->fetchCol("id");

            // Add DebugLogEntry
            if (isset ($user->char)) {
                $user->char->debuglog->add("Send Message to all Users!", "veryverbose");
            }

            // Add Messageref
            $message->addReceiver($messageid, $result);
        }

        return $messageid;
    }

    /**
     * Delete a Message by setting the status to MESSAGESYSTEM_STATUS_DELETED
     * @param int $messageid
     */
    public static function delete($messageid)
    {
        $dbqt = new QueryTool();
        $result = $dbqt	->select("id")
                        ->from("messages_references")
                        ->where("messageid=".$messageid)
                        ->exec()
                        ->fetchCol("id");

        if (!is_array($result)) {
            throw new Error("Can't get Message_References List for Message ID " . $messageid . " (Messagesys->delete())");
        }

        // Delete the References
        foreach ($result as $ids) {
            self::updateMessageStatus($ids, false, MESSAGESYSTEM_STATUS_DELETED);
        }
    }

    /**
     * Update Message Status
     * @param int $messageid ID of the Message to alter
     * @param int $receiverid ID of the Receiver (set to false for all receivers)
     * @param int $status New Status
     */
    public static function updateMessageStatus($messageid, $receiverid, $status)
    {
        global $dbconnect;
        $dbqt = new QueryTool;

        $tablename 	= "messages_references";
        $values		= array ( "status" => $status );

        $dbqt	->update($tablename)
                ->set($values)
                ->where("messageid=".$messageid);

        if (is_numeric($receiverid) && $receiverid > 0) {
            $dbqt->where("receiver=".$receiverid);
        }

        $dbqt->exec();
    }

    /**
     * Get a specific Message
     * @param int $messageid ID of the Message to get
     * @param mixed String or Array of Fields to retrieve
     */
    public static function getMessage($messageid, $fields=false)
    {
        if (!$result = SessionStore::readCache("message_".$messageid."_".serialize($fields))) {
            $dbqt = new QueryTool();

            if (is_array($fields)) {
                $dbqt->select(implode(",", $fields));
            } else {
                $dbqt->select("*");;
            }

            $dbqt	->from("messages")
                    ->where("id=".$messageid);

            $result = $dbqt->exec()->fetchRow();

            SessionStore::writeCache("message_".$messageid."_".serialize($fields), $result);
        }
        return $result;
    }

    /**
     * Get Message Inbox for a specific Character
     * @param Character $character Character Object
     * @param array $fields Fields to get
     * @param int $limit Number of Messages to get
     * @param bool $ascending Ascending sorting
     * @param int $status The Status of the Messages
     * @return array Array of Messages
     */
    public static function getInbox(Character $character, $fields=false, $limit=false, $ascending=true, $status=false)
    {
        $dbqt = new QueryTool();

        if (is_array($fields)) {
            $dbqt->select(implode(", ", $fields));
        } else {
            $dbqt->select("messages.id, sender, receiver, subject, text, date, status");
        }

        $dbqt	->from("messages_references")
                ->join("messages", "messages.id = messages_references.messageid")
                ->where("receiver=".$character->id);

        if ($status && is_numeric($status)) {
            $dbqt->where("status=".$status);
        } else {
            $dbqt->where("status!=3"); // Message is not deleted
        }

        $dbqt	->order("date", $ascending)
                ->order("id", $ascending);

        if (is_numeric($limit)) {
            $dbqt->limit($limit, 0);
        }

        $result = $dbqt->exec()->fetchAll();

        return $result;
    }

    /**
     * Get Message Outbox for a specific Character
     * @param Character $character Character Object
     * @param array $fields Fields to get
     * @param int $limit Number of Messages to get
     * @param bool $ascending Ascending sorting
     * @param int $status The Status of the Messages
     * @return array Array of Messages
     */
    public static function getOutbox(Character $character, $fields=false, $limit=false, $ascending=true, $status=false)
    {
        $dbqt = new Querytool;

        if (is_array($fields)) {
            $dbqt->select(implode(", ", $fields));
        } else {
            $dbqt->select("messages.id, sender, receiver, subject, text, date, status");
        }

        $dbqt	->from("messages_references")
                ->join("messages", "messages.id = messages_references.messageid")
                ->where("sender=".$character->id);

        if ($status && is_numeric($status)) {
            $dbqt->where("status=".$status);
        }

        $dbqt	->order("date", $ascending)
                ->order("id", $ascending);

        if (is_numeric($limit)) {
            $dbqt->limit($limit, 0);
        }

        $result = $dbqt->exec()->fetchAll();

        return $result;
    }
}
?>
