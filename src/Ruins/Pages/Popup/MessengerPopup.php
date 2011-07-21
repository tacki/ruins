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
namespace Ruins\Pages\Popup;
use Ruins\Main\Controller\Link;
use Ruins\Main\Manager\MessageManager;
use Ruins\Common\Controller\AbstractPageObject;

class MessengerPopup extends AbstractPageObject
{
    public $title  = "Messenger";

    public function createContent($page, $parameters)
    {
        $page->getNavigation()->addLink("Verfassen", "Popup/Messenger/create")
                  ->addLink("Posteingang", "Popup/Messenger/inbox")
                  ->addLink("Postausgang", "Popup/Messenger/outbox");

        switch ($parameters['op']) {

            case "send":
                if ($parameters['reply']) {
                    $messagestatus = MessageManager::STATUS_REPLIED;
                } else {
                    $messagestatus = MessageManager::STATUS_UNREAD;
                }

                $nrSentMessages = MessageManager::write(
                    $user->character,
                    $parameters['receivers'],
                    $parameters['subject'],
                    $parameters['text'],
                    $messagestatus
                );

                if (isset($parameters['reply'])) {
                    $page->output("Antwort gesendet!");
                } else {
                    $page->output("$nrSentMessages Nachricht(en) erfolgreich gesendet!");
                }
                break;

            default:
            case "create":
                $snippet = $page->createTemplateSnippet();
                $snippet->assign("target", "Popup/Messenger/send");
                $snippet->assign("receiver", "");
                $snippet->assign("subject", "");
                $snippet->assign("text", "");
                $output = $snippet->fetch("snippet_messenger_create.tpl");
                $page->output($output, true);

            break;

            case "reply":
                $snippet = $page->createTemplateSnippet();
                $snippet->assign("target", "Popup/Messenger/send&reply=1");
                if (isset($parameters['replyto'])) {
                    $message = MessageManager::getMessage($parameters['replyto']);
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
                $page->output($output, true);
                break;

            case "inbox":
                $page->addForm("delete");
                $page->getForm("delete")->head("deleteform", "Popup/Messenger/delete");

                $messagelist = MessageManager::getInbox($user->character);
                $showlist = array();
                foreach ($messagelist as $message) {
                    $showmessage = array();
                    switch($message->status) {
                        case 0: $showmessage['status'] = "<img src='".$page->template['mytemplatedir']."/images/message_unread.gif' />"; break;
                        case 1: $showmessage['status'] = "<img src='".$page->template['mytemplatedir']."/images/message_read.gif' />"; break;
                        case 2: $showmessage['status'] = "<img src='".$page->template['mytemplatedir']."/images/message_replied.gif' />"; break;
                    }

                    $showmessage['sender']		= "<a href='Popup/Messenger/read&messageid=".$message->id."'>".$message->sender->name."</a>";
                    $showmessage['subject'] 	= "<a href='Popup/Messenger/read&messageid=".$message->id."'>".$message->data->subject."</a>";
                    $showmessage['date']		= $message->date->format("H:i:s d.m.y");
                    $showmessage['action']		= "<input type='checkbox' name='chooser[]' value='".$message->id."'>";

                    $showlist[] = $showmessage;
                }

                $page->addTable("messagelist", true);
                $page->getTable("messagelist")->setCSS("messagelist");
                $page->getTable("messagelist")->setTabAttributes(false);
                $page->getTable("messagelist")->addTabHeader(array("", "Absender", "Betreff", "Datum", ""), false, false, "head");
                $page->getTable("messagelist")->addListArray($showlist, "firstrow", "firstrow");
                $page->getTable("messagelist")->setSecondRowCSS("secondrow");
                $page->getTable("messagelist")->load();

                $page->output("<div id='messagetools'>", true);
                $page->output("<input type='button' value='Alle' onclick='checkall(\"deleteform\")' class='button' />", true);
                $page->getForm("delete")->setCSS("delbutton");
                $page->getForm("delete")->submitButton("Löschen");
                $page->output("</div>", true);
                $page->getForm("delete")->close();
                break;

            case "read":
                if (isset($parameters['messageid'])) {
                    $message = MessageManager::getMessage($parameters['messageid']);

                    $snippet = $page->createTemplateSnippet();
                    $snippet->assign("target", "Popup/Messenger/reply&replyto=".$message->id);
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
                $page->output($output, true);
                break;

            case "delete":
                if (isset($parameters['chooser'])) {
                    $page->output("Willst du wirklich " . count($parameters['chooser']) . " Nachrichten löschen?");
                    $page->addForm("delete");
                    $page->getForm("delete")->head("deleteform", "Popup/Messenger/delete&ask=yes");
                    $page->getForm("delete")->hidden("ids", implode(",", $parameters['chooser']));
                    $page->getForm("delete")->setCSS("button");
                    $page->getForm("delete")->submitButton("Ja, Löschen");
                    $page->getForm("delete")->close();
                } elseif (isset($parameters['ids'])) {
                    MessageManager::delete($parameters['ids']);

                    $page->output(count($parameters['ids']) . " Nachrichten gelöscht!");
                }
                break;

            case "outbox":
                $messagelist = MessageManager::getOutbox($user->character);

                $showlist = array();
                foreach ($messagelist as $message) {
                    $showmessage = array();
                    $showmessage['receiver']	= "<a href='Popup/Messenger/read&messageid=".$message->id."'>".$message->receiver->displayname."</a>";
                    $showmessage['subject'] 	= "<a href='Popup/Messenger/read&messageid=".$message->id."'>".$message->data->subject."</a>";
                    $showmessage['date']		= $message->date->format("H:i:s d.m.y");

                    $showlist[] = $showmessage;
                }

                $page->addTable("messagelist", true);
                $page->getTable("messagelist")->setCSS("messagelist");
                $page->getTable("messagelist")->setTabAttributes(false);
                $page->getTable("messagelist")->addTabHeader(array("Empfänger", "Betreff", "Datum"), false, false, "head");
                $page->getTable("messagelist")->addListArray($showlist, "firstrow", "firstrow");
                $page->getTable("messagelist")->setSecondRowCSS("secondrow");
                $page->getTable("messagelist")->load();
                break;
        }
    }
}