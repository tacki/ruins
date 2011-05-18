<?php
/**
 * Message Class
 *
 * Single Message Object
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2007 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: message.class.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Message Class
 *
 * Single Message Object
 * @package Ruins
 */
class Message extends DBObject
{
    /**
     * constructor - load the default values and initialize the attributes
     * @param array $settings Settings for this Object (see Documentation)
     */
    function __construct($settings=false)
    {
        // Call Constructor of the Parent-Class
        parent::__construct($settings);
    }

    /**
     * Add Receiver to messages_reference Table
     * @param int $messageid ID of the Message
     * @param int $receiver ID of the Receiver
     */
    public function addReceiver($messageid, $receiver)
    {
        global $dbconnect;
        $dbqt = new QueryTool;

        $tablename 	= "messages_references";

        if (is_array($receiver)) {
            foreach ($receiver as $receiverid) {
                $values = array (
                                    "messageid" => $messageid,
                                    "receiver"	=> $receiverid,
                                    "status"	=> 0,
                                );
                $dbqt->insertinto($tablename)->data($values)->exec();
                $dbqt->clear();
            }
        } elseif (is_numeric($receiver)) {
            $values = array (
                                "messageid" => $messageid,
                                "receiver"	=> $receiver,
                                "status"	=> 0,
                            );

            $dbqt->insertinto($tablename)->data($values)->exec();
            $dbqt->clear();
        }
    }

}
?>
