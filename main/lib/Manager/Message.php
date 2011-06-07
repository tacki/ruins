<?php
/**
 * MessageSystem Class
 *
 * Class to control the Messaging-System
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Manager;
use Entities\Character,
    Manager\User,
    SessionStore;

/**
 * MessageSystem Class
 *
 * Class to control the Messaging-System
 * @package Ruins
 */
class Message
{
    const STATUS_UNREAD    = 0;
    const STATUS_READ      = 1;
    const STATUS_REPLIED   = 2;
    const STATUS_DELETED   = 3;

    /**
     * Write a normal Message
     * @param Character $sender ID of the Sender
     * @param mixed $receivers Receivers ID OR array of ID's OR "all" for all ID's
     * @param string $subject Subject of the Message
     * @param string $text Messagetext
     * @param int $status Messagestatus to set
     * @return int Number of Messages sent
     */
    public static function write(Character $sender, $receivers, $subject, $text, $status=self::STATUS_UNREAD)
    {
        global $em;

        // Add MessageData (happens only once)
        $messagedata = $em ->getRepository("Entities\MessageData")
                           ->findOneBy(array(
                                               "subject" => $subject,
                                               "text"	 => $text,
                                            )
                                       );
        if (!$messagedata) {
            $messagedata          = new \Entities\MessageData;
            $messagedata->subject = $subject;
            $messagedata->text    = $text;
            $em->persist($messagedata);
        }

        $receiverIDlist = array();

        if (is_array($receivers)) {
            $receiverIDlist = $receivers;
        } elseif (is_numeric($receivers)) {
            $receiverIDlist[] = $receivers;
        } elseif ($receivers instanceof Character) {
            $receiverIDlist[] = $receivers->id;
        } elseif (is_string($receivers) && $receivers != "all") {
            $receiverNameList = explode(",", $receivers);
            foreach ($receiverNameList as $receiver) {
                $receiverIDlist[] = User::getCharacterID(trim($receiver));
            }
        } elseif ($receivers == "all") {
            $receiverIDlist = User::getCharacterList("id");
        }

        // remove duplicates from List
        $receiverIDlist = array_unique($receiverIDlist);

        $nrSentMessages = 0;
        $batchSize = 20;
        $listSize = count($receiverIDlist);
        for ($i = 0; $i <= $listsize; $i++) {
            $message = new \Entities\Message;
            $message->sender = $sender;
            $message->receiver = $em->find("Entities\Character", $receiverIDlist[$i]);
            $message->data = $messagedata;
            $message->status = $status;
            if ($message->receiver) {
                // Receiver exists
                $em->persist($message);
                $nrSentMessages++;
            }

            if (($i % $batchSize) == 0 && $i > 0) {
                // Write to DB every $batchSize Inserts
                $em->flush();
            }
        }

        if ($nrSentMessages == 0) {
            // delete $messagedata
            $em->detach($messagedata);
        }

        return $nrSentMessages;
    }

    /**
     * Delete a Message by setting the status to self::STATUS_DELETED
     * @param int|array $messageid
     */
    public static function delete($messageid)
    {
        self::updateMessageStatus($messageid, self::STATUS_DELETED);
    }

    /**
     * Update Message Status
     * @param int|array $messageid ID of the Message to alter
     * @param int $status New Status
     */
    public static function updateMessageStatus($messageid, $status)
    {
        $qb = getQueryBuilder();

        $qb ->update("Entities\Message", "message")
            ->set("message.status", $status)
            ->where("message.id = ?1");

        if (is_array($messageid)) {
            foreach ($messageid as $id) {
                $qb->getQuery()->execute(array(1 => $id));
            }
        } else {
            $qb->getQuery()->execute(array(1 => $messageid));
        }
    }

    /**
     * Delete Messages from Database, marked as self::STATUS_DELETED
     * @param Character $character Limit to this Character
     */
    public static function flushDeleted($character=false)
    {
        global $em;

        // Delete the Messages
        $qb = getQueryBuilder();

        $qb ->delete("Entities\Message", "message")
            ->where("message.status = ?1")->setParameter(1, self::STATUS_DELETED);

        if ($character instanceof Character) $qb->andWhere("message.receiver = ?2")->setParameter(2, $character);

        $qb->getQuery()->execute();
        $em->flush();

        //---

        // Delete orphan Messagedata
        $qb = getQueryBuilder();
        $sub = getQueryBuilder();

        // IDs of MessageData in use
        $sub ->select("sub_messagedata.id")
             ->from("Entities\Message", "sub_message")
             ->join("sub_message.data", "sub_messagedata");

        // Retrieve unused MessageData
        $qb ->select("messagedata.id")
            ->from("Entities\MessageData", "messagedata")
            ->where("messagedata NOT IN (".$sub->getDQL().")");

        $result = $qb->getQuery()->getResult();

        // Remove them From Database
        $delqb = getQueryBuilder();

        $delqb->delete("Entities\MessageData", "messagedata");

        foreach ($result as $resrow) {
            $delqb->orWhere("messagedata.id = ?1")->setParameter(1, $resrow['id']);
        }

        if ($result) {
            $delqb->getQuery()->execute();
        }
    }

    /**
     * Get a specific Message
     * @param int $messageid ID of the Message to get
     * @param mixed String or Array of Fields to retrieve
     */
    public static function getMessage($messageid, $fields=false)
    {
        global $em;

        return $em->find("Entities\Message", $messageid);
    }

    /**
     * Get Message Inbox for a specific Character
     * @param Character $character Character Object
     * @param int $limit Number of Messages to get
     * @param bool $ascending Ascending sorting
     * @param int $status The Status of the Messages
     * @return array Array of Messages
     */
    public static function getInbox(Character $character, $limit=false, $ascending=true, $status=false)
    {
        $qb = getQueryBuilder();

        $qb  ->select("message")
             ->from("Entities\Message", "message")
             ->where("message.receiver = ?1")->setParameter(1, $character->id);


        if ($status) {
            $qb->andWhere("message.status = ?2")->setParameter(2, $status);
        } else {
            $qb->andWhere("message.status != ?2")->setParameter(2, self::STATUS_DELETED);
        }

        if ($limit) $qb->setMaxResults($limit);

        if ($ascending) {
            $qb->orderBy("message.date", "ASC");
        } else {
            $qb->orderBy("message.date", "DESC");
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get Message Outbox for a specific Character
     * @param Character $character Character Object
     * @param int $limit Number of Messages to get
     * @param bool $ascending Ascending sorting
     * @param int $status The Status of the Messages
     * @return array Array of Messages
     */
    public static function getOutbox(Character $character, $limit=false, $ascending=true, $status=false)
    {
        $qb = getQueryBuilder();

        $qb ->select("message")
            ->from("Entities\Message", "message")
            ->where("message.sender = ?1")->setParameter(1, $character);

        if ($limit) $qb->setMaxResults($limit);

        if ($ascending) {
            $qb->orderBy("message.date", "ASC");
        } else {
            $qb->orderBy("message.date", "DESC");
        }

        return $qb->getQuery()->getResult();
    }
}
?>
