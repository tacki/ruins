<?php
/**
 * Classic Chat Class
 *
 * Class to create a simple Chat
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: classicchat.class.php 328 2011-04-20 10:29:29Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once(DIR_INCLUDES."includes.inc.php");

/**
 * Class defines
 */
define("CHATMESSAGE_STATUS_NORMAL", 0);
define("CHATMESSAGE_STATUS_OLDEDIT", 1);
define("CHATMODE_NORMAL", 0);
define("CHATMODE_EDIT", 1);

/**
 * Selecting and Ordering Class
 * @package Ruins
 */
class ClassicChat
{
    /**
     * Page-Object for direct Output
     * @var Page
     */
    private $_page;

    /**
     * Section to use
     * @var string
     */
    private $_section;

    /**
     * Current Pagenumber
     * @var integer
     */
    private $_pagenr = 0;

    /**
     * Current Mode
     * @var integer
     */
    private $_mode = CHATMODE_NORMAL;

    /**
     * constructor - load the default values and initialize the attributes
     * @param Page $page Page-Object for direct Output
     * @param string $section Section to use
     */
    function __construct(Page $page, $section)
    {
        $this->_page 		= $page;
        $this->_section 	= $section;
    }

    /**
     * Add Helper to handle Add's, Del's, etc
     */
    private function _addHelper()
    {
        $chatform = $this->_getChatFormName();

        $this->_page->addJavaScriptFile("btcode.js");
        $this->_page->addJavaScriptFile("colorpreview.func.js");
        $this->_page->addJavaScriptFile("settings.func.js");

        if (!isset($_POST[$chatform.'_op'])) {
            $_POST[$chatform.'_op'] = "";
        }

        switch ($_POST[$chatform.'_op']) {

            case "addLine":
                $this->_addLine($_POST[$chatform.'_chatline']);
                break;

            case "editLine":
                if (isset($_POST[$chatform."_chatline"]) && isset($_POST[$chatform."_editLineID"])) {
                    $this->_updateLine($_POST[$chatform."_editLineID"], $_POST[$chatform."_chatline"]);
                    $this->_mode = CHATMODE_NORMAL;
                } else {
                    $this->_mode = CHATMODE_EDIT;
                }
                break;

        }

        if (isset($_GET[$this->_section.'_page'])) {
            $this->_setPage($_GET[$this->_section.'_page']);
        }
    }

    /**
     * Set Page to load
     * @param integer $pagenr Pagenumber to load
     */
    private function _setPage($pagenumber)
    {
        $this->_pagenr = (int)$pagenumber-1;
    }

    /**
     * Get Page from Database
     * @param integer $linesperpage Lines to grab per Page
     * @param integer $pagenr Pagenumber to load
     * @return array Resultarray
     */
    private function _getPage($linesperpage, $pagenr=0)
    {
        $dbqt = new QueryTool();

        $result = $dbqt	->select("date, authorid, displayname, chatline")
                        ->from("chat")
                        ->join("characters", "characters.id = chat.authorid")
                        ->where("section=".$dbqt->quote($this->_section))
                        ->where("status=".CHATMESSAGE_STATUS_NORMAL)
                        ->order("date", true)
                        ->limit($linesperpage, $pagenr*$linesperpage)
                        ->exec()
                        ->fetchAll();

        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get the last written line at this Chat
     * @param Character|int $char Character Object or Character ID
     * @return array|bool The chatline (including it's id) or false if none is given
     */
    private function _getLastLine($char)
    {
        $charid = 0;

        if ($char instanceof Character) {
            $charid = $char->id;
        } else {
            $charid = (int)$char;
        }

        $dbqt = new QueryTool();

        $result = $dbqt ->select("id, chatline")
                        ->from("chat")
                        ->where("section=".$dbqt->quote($this->_section))
                        ->where("authorid=".$charid)
                        ->where("status=".CHATMESSAGE_STATUS_NORMAL)
                        ->order("date", true)
                        ->limit(1, 0)
                        ->exec()
                        ->fetchRow();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Get Number of Pages
     * @param integer $linesperpage Lines per Page
     * @return integer Number of Pages for this Chat
     */
    private function _getNumberOfPages($linesperpage)
    {
        $dbqt = new QueryTool();

        $chatlines = $dbqt	->select("id")
                            ->from("chat")
                            ->where("section=".$dbqt->quote($this->_section))
                            ->exec()
                            ->numRows();

        $result = $chatlines/$linesperpage;

        if ($chatlines%$linesperpage) {
            return (int)$result+1;
        } else {
            return (int)$result;
        }
    }

    /**
     * Add Line to the Chat
     * @param string $text Chatline
     * @return bool true if successful, else false
     */
    private function _addLine($text)
    {
        global $user;
        global $dbconnect;

        if (!strlen($text)) {
            return false;
        }

        if (isset($user->char) && $user->char instanceof Character) {
            $chatentry = new DBObject(array("tablename" => $dbconnect['prefix'] . "chat"));

            $chatentry->create();
            $chatentry->authorid 	= $user->char->id;
            $chatentry->section 	= $this->_section;
            $chatentry->chatline 	= $text;
            $chatentry->date		= date("Y-m-d H:i:s");

            $chatentry->save();

            // Add DebugLogEntry
            $user->char->debuglog->add("Wrote at Chat $this->_section", "veryverbose");

            return true;
        } else  {
            return false;
        }
    }

    /**
     * Update an edited line
     * @param int $id ID of the Line to update
     * @param string $text New Chatline
     */
    private function _updateLine($id, $text)
    {
        $dbqt = new QueryTool();

        // Get Data from oldline
        $oldline = $dbqt	->select("*")
                            ->from("chat")
                            ->where("id=".$id)
                            ->exec()
                            ->fetchRow();

        // Create a new line
        $newline = array(
                            "section" => $oldline['section'],
                            "authorid" => $oldline['authorid'],
                            "chatline" => $text,
                            "date" => $oldline['date'],
                        );

        $dbqt->clear();

        // Add new line
        $dbqt	->insertinto("chat")
                ->data($newline)
                ->exec();

        // Hide old Message from Chat
        $this->_updateMessageStatus($id, CHATMESSAGE_STATUS_OLDEDIT);
    }

    /**
     * Update Messagestatus of a given Message
     * @param int $id ID of the Message to change Status
     * @param int $status New status
     */
    private function _updateMessageStatus($id, $status)
    {
        $dbqt = new QueryTool();

        $dbqt	->update("chat")
                ->set(array("status" => (int)$status))
                ->where("id=".(int)$id)
                ->exec();
    }

    /**
     * Apply Censorship to the chatline. Filter bad words
     * @param string $text The chatline to check
     * @return string Filtered chatline
     */
    private function _chatlineCensorship($text)
    {
        // We check each word separately
        $words = explode(" ", $text);

        // Get List of bad words (use SessionStore to avoid unneeded dbtraffic)
        if (!$badwords = SessionStore::readCache("badwords")) {
            $dbqt = new QueryTool();

            $badwords = $dbqt	->select("badword, replacement")
                                ->from("badwords")
                                ->exec()
                                ->fetchAll();

             SessionStore::writeCache("badwords", $badwords);
        }

        foreach ($words as $key=>$word) {
            $purgedword = btcode::purgeTags($word);

            foreach ($badwords as $badword) {
                if (array_search($purgedword, $badword) !== false) {
                    // this is the replacement
                    $words[$key] = $badword['replacement'];
                }
            }
        }

        // put them together
        $text = implode(" ", $words);

        return $text;
    }


    /**
     * Wrap the Databaseresult to generate a more readable Output
     * @param array $page The loaded Page
     * @return array|bool Revised Databaseresult or false if unsuccessful
     */
    private function _pageDisplayWrapper($page)
    {
        global $user;

        if (!is_array($page)) {
            return false;
        }

        for ($i=0; $i<sizeof($page); $i++) {
            // Format Date to userdefined chatdateformat
            $page[$i]['date'] = date($user->settings->get("chatdateformat", "[H:i:s]"), strtotime($page[$i]['date']));
            $page[$i]['displayname'] = "" . trim($page[$i]['displayname']);

            // authorid is not needed anymore
            unset($page[$i]['authorid']);

            // Apply Censorship
            if ($user->settings->get("chatcensorship", 1)) {
                $page[$i]['chatline'] = $this->_chatlineCensorship($page[$i]['chatline']);
            }

            // Check for special Commands
            $firstword 	= substr($page[$i]['chatline'], 0, strpos($page[$i]['chatline'], " "));
            $rest		= strstr($page[$i]['chatline'], " ");

            if ($firstword == "/me" || $firstword == ":") {
                // Actions
                $page[$i]['specialline'] = "`b* `b" . $page[$i]['displayname'] . " " . $rest;

                unset($page[$i]['displayname']);
                unset($page[$i]['chatline']);
            } elseif ($firstword == "/mes") {
                // Actions with "'s"
                $page[$i]['specialline'] = "`b* `b" . $page[$i]['displayname'] . "'s " . $rest;

                unset($page[$i]['displayname']);
                unset($page[$i]['chatline']);
            } elseif ($firstword == "/em" || $firstword == "/X") {
                // Emotes
                $page[$i]['specialline'] = $rest;

                unset($page[$i]['displayname']);
                unset($page[$i]['chatline']);
            }
        }

        $page = array_reverse($page);

        return $page;
    }

    /**
     * Generate Name of the Chatform
     * @return string Name of the Chatform
     */
    private function _getChatFormName()
    {
        if (strlen($this->_section)) {
            return $this->_section . "_form";
        } else {
            throw new Error("Section not set! A Chat has no Sectionname set!");
        }
    }

    /**
     * Compile the visible Chat
     * @param integer $pagenr Pagenumber to show
     */
    public function show()
    {
        global $user;

        $output		= "";

        $chatform = $this->_getChatFormName();

        $this->_addHelper();

        switch ($this->_mode) {
            case CHATMODE_NORMAL:
                // Chatlines
                $chatsnippet = $this->_page->createTemplateSnippet();

                $chatsnippet->assign("chatname", $this->_section);
                $chatsnippet->assign("chatform", $chatform);
                $chatsnippet->assign("target", (string)$this->_page->url);
                // Nav for all Buttons (important)
                $this->_page->nav->add(new Link("", $this->_page->url));

                // Get Chatpage
                $resultpage = $this->_pageDisplayWrapper($this->_getPage(20, $this->_pagenr));

                if (count($resultpage)) {

                    foreach ($resultpage as $resultline) {

                        $output .= "<tr>";

                        if (isset($resultline['specialline'])) {
                            $output .= "<td class='chatdate'>";
                            $output .= $resultline['date'];
                            $output .= "</td>";
                            $output .= "<td class='chattext' colspan=2>";
                            $output .= $resultline['specialline'];
                            $output .= "</td>";
                        } else {
                            $output .= "<td class='chatdate'>";
                            $output .= $resultline['date'];
                            $output .= "</td>";
                            $output .= "<td class='chattext'>";
                            $output .= "<"  . $resultline['displayname'] . "> " . $resultline['chatline'];
                            $output .= "</td>";
                        }

                        $output .= "</tr>";
                    }
                }

                $chatsnippet->assign("chat_rows", $output);


                // Previous Pages
                $pagesoutput	= "";
                $numberOfPages 	= $this->_getNumberOfPages(20);
                for ($i=1; $i<=$numberOfPages; $i++) {
                    // Replace previous GET-Query
                    $newurl = clone $this->_page->url;
                    $newurl->setParameter($this->_section."_page", $i);
                    $this->_page->nav->add(new Link("", $newurl));
                    $pagesoutput .= "<a href='?".$newurl."'>". $i . "</a> ";
                }


                $chatsnippet->assign("chat_pages", $pagesoutput);


                // visibility
                if (isset($user->char->settings)) {
                    if ($user->char->settings->get("chat_".$this->_section."_visibility", 1)) {
                        $chatsnippet->assign("visibility", "inline");
                        $chatsnippet->assign("visibility_inv", "none");
                    } else {
                        $chatsnippet->assign("visibility", "none");
                        $chatsnippet->assign("visibility_inv", "inline");
                    }
                } else {
                    $chatsnippet->assign("visibility", "inline");
                    $chatsnippet->assign("visibility_inv", "none");
                }


                $this->_page->output($chatsnippet->fetch("snippet_classicchat.tpl"), true);

                break;

            case CHATMODE_EDIT:

                if ($row = $this->_getLastLine($user->char)) {
                    $chattable = new SimpleTable;

                    // Editfield
                    $output .= $chattable->startData();
                    $this->_page->addForm($chatform, true);
                    $this->_page->$chatform->head($chatform, $this->_page->url);
                    $this->_page->$chatform->hidden($chatform."_op", "editLine");
                    $this->_page->$chatform->hidden($chatform."_section", $this->_section);
                    $this->_page->$chatform->hidden($chatform."_editLineID", $row['id']);
                    $this->_page->$chatform->setCSS("floatleft textarea");
                    $this->_page->$chatform->textArea($chatform."_chatline", btCode::exclude($row['chatline']), 60, 10);

                    $this->_page->$chatform->setCSS("floatleft button");
                    $this->_page->$chatform->submitButton("Ändern");
                    $this->_page->$chatform->close();

                    $this->_page->output("`n`n");

                    // BackButton
                    $this->_page->nav->add(new Link("", $this->_page->url));
                    $this->_page->addForm("refreshbutton");
                    $this->_page->refreshbutton->head("refreshbutton", $this->_page->url);
                    $this->_page->refreshbutton->setCSS("button");
                    $this->_page->refreshbutton->submitButton("Zurück");
                    $this->_page->refreshbutton->close();
                } else {
                    $this->_page->output("Editieren des letzten Eintrages nicht möglich!");

                    // BackButton
                    $this->_page->nav->add(new Link("", $this->_page->url));
                    $this->_page->addForm("refreshbutton");
                    $this->_page->refreshbutton->head("refreshbutton", $this->_page->url);
                    $this->_page->refreshbutton->setCSS("button");
                    $this->_page->refreshbutton->submitButton("Zurück");
                    $this->_page->refreshbutton->close();
                }
                break;
        }
    }
}

?>
