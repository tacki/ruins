<?php
/**
 * Messenger Popup
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2009 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
use Ruins\Main\Controller\Link;
use Ruins\Main\Manager\MessageManager;

/**
 * Page Content
 */
$popup->set("pagetitle", "Ruins Messenger");
$popup->set("headtitle", "Messenger");

$popup->nav->addLink("Verfassen", "popup=popup/messenger&op=create")
           ->addLink("Posteingang", "popup=popup/messenger&op=inbox")
           ->addLink("Postausgang", "popup=popup/messenger&op=outbox");

switch ($_GET['op']) {

    case "send":
        if ($_GET['reply']) {
            $messagestatus = MessageManager::STATUS_REPLIED;
        } else {
            $messagestatus = MessageManager::STATUS_UNREAD;
        }

        $nrSentMessages = MessageManager::write(
                                                    $user->character,
                                                    $_POST['receivers'],
                                                    $_POST['subject'],
                                                    $_POST['text'],
                                                    $messagestatus
                                                );

        if (isset($_GET['reply'])) {
            $popup->output("Antwort gesendet!");
        } else {
            $popup->output("$nrSentMessages Nachricht(en) erfolgreich gesendet!");
        }
        break;

    default:
    case "create":
        $snippet = $popup->createTemplateSnippet();
        $snippet->assign("target", "popup=popup/messenger&op=send");
        $snippet->assign("receiver", "");
        $snippet->assign("subject", "");
        $snippet->assign("text", "");
        $output = $snippet->fetch("snippet_messenger_create.tpl");
        $popup->output($output, true);

        break;

    case "reply":
        $snippet = $popup->createTemplateSnippet();
        $snippet->assign("target", "popup=popup/messenger&op=send&reply=1");
        if (isset($_GET['replyto'])) {
            $message = MessageManager::getMessage($_GET['replyto']);
            $snippet->assign("receiver", $message->sender->name);
            if (substr($message->subject, 0, 4) != "RE: ") {
                // Add 'RE: ' if there isn't already one
                $snippet->assign("subject", "RE: " . $message->data->subject);
            } else {
                $snippet->assign("subject", $message->data->subject);
            }
            $snippet->assign("text", "\r\n\n--- Original Message ---\r\n". $message->data->text);
        }
        $output = $snippet->fetch("snippet_messenger_create.tpl");
        $popup->output($output, true);
        break;

    case "inbox":
        $popup->addForm("delete");
        $popup->getForm("delete")->head("deleteform", "popup=popup/messenger&op=delete");

        $messagelist = MessageManager::getInbox($user->character);
        $showlist = array();
        foreach ($messagelist as $message) {
            $showmessage = array();
            switch($message->status) {
                case 0: $showmessage['status'] = "<img src='".$popup->template['mytemplatedir']."/images/message_unread.gif' />"; break;
                case 1: $showmessage['status'] = "<img src='".$popup->template['mytemplatedir']."/images/message_read.gif' />"; break;
                case 2: $showmessage['status'] = "<img src='".$popup->template['mytemplatedir']."/images/message_replied.gif' />"; break;
            }

            $showmessage['sender']		= "<a href='?popup=popup/messenger&op=read&messageid=".$message->id."'>".$message->sender->name."</a>";
            $showmessage['subject'] 	= "<a href='?popup=popup/messenger&op=read&messageid=".$message->id."'>".$message->data->subject."</a>";
            $showmessage['date']		= $message->date->format("H:i:s d.m.y");
            $showmessage['action']		= "<input type='checkbox' name='chooser[]' value='".$message->id."'>";

            $showlist[] = $showmessage;
        }

        $popup->addTable("messagelist", true);
        $popup->getTable("messagelist")->setCSS("messagelist");
        $popup->getTable("messagelist")->setTabAttributes(false);
        $popup->getTable("messagelist")->addTabHeader(array("", "Absender", "Betreff", "Datum", ""), false, false, "head");
        $popup->getTable("messagelist")->addListArray($showlist, "firstrow", "firstrow");
        $popup->getTable("messagelist")->setSecondRowCSS("secondrow");
        $popup->getTable("messagelist")->load();

        $popup->output("<div id='messagetools'>", true);
        $popup->output("<input type='button' value='Alle' onclick='checkall(\"deleteform\")' class='button' />", true);
        $popup->getForm("delete")->setCSS("delbutton");
        $popup->getForm("delete")->submitButton("Löschen");
        $popup->output("</div>", true);
        $popup->getForm("delete")->close();
        break;

    case "read":
        if (isset($_GET['messageid'])) {
            $message = MessageManager::getMessage($_GET['messageid']);

            $snippet = $popup->createTemplateSnippet();
            $snippet->assign("target", "popup=popup/messenger&op=reply&replyto=".$message->id);
            $snippet->assign("sender", $message->sender->displayname);
            $snippet->assign("date", $message->date->format("H:i:s d.m.y"));
            $snippet->assign("subject", $message->data->subject);
            $snippet->assign("text", $message->data->text);

            if ($message->status != MessageManager::STATUS_DELETED ||
                $message->receiver == $user->character) {
                MessageManager::updateMessageStatus($message->id, MessageManager::STATUS_READ);
            }
        }
        $output = $snippet->fetch("snippet_messenger_read.tpl");
        $popup->output($output, true);
        break;

    case "delete":
        if (isset($_POST['chooser'])) {
            $popup->output("Willst du wirklich " . count($_POST['chooser']) . " Nachrichten löschen?");
            $popup->addForm("delete");
            $popup->getForm("delete")->head("deleteform", "popup=popup/messenger&op=delete&ask=yes");
            $popup->getForm("delete")->hidden("ids", implode(",", $_POST['chooser']));
            $popup->getForm("delete")->setCSS("button");
            $popup->getForm("delete")->submitButton("Ja, Löschen");
            $popup->getForm("delete")->close();
        } elseif (isset($_POST['ids'])) {
            MessageManager::delete($_POST['ids']);

            $popup->output(count($_POST['ids']) . " Nachrichten gelöscht!");
        }
        break;

    case "outbox":
        $messagelist = MessageManager::getOutbox($user->character);

        $showlist = array();
        foreach ($messagelist as $message) {
            $showmessage = array();
            $showmessage['receiver']	= "<a href='?popup=popup/messenger&op=read&messageid=".$message->id."'>".$message->receiver->displayname."</a>";
            $showmessage['subject'] 	= "<a href='?popup=popup/messenger&op=read&messageid=".$message->id."'>".$message->data->subject."</a>";
            $showmessage['date']		= $message->date->format("H:i:s d.m.y");

            $showlist[] = $showmessage;
        }

        $popup->addTable("messagelist", true);
        $popup->getTable("messagelist")->setCSS("messagelist");
        $popup->getTable("messagelist")->setTabAttributes(false);
        $popup->getTable("messagelist")->addTabHeader(array("Empfänger", "Betreff", "Datum"), false, false, "head");
        $popup->getTable("messagelist")->addListArray($showlist, "firstrow", "firstrow");
        $popup->getTable("messagelist")->setSecondRowCSS("secondrow");
        $popup->getTable("messagelist")->load();
        break;
}
?>
