<?php
/**
 * Classic Chat Class
 *
 * Class to create a simple Chat
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Controller;
use Common\Controller\BtCode,
    Common\Controller\SimpleTable,
    Common\Controller\SessionStore,
    Main\Entities\Chat,
    Main\Entities\Character,
    DateTime;

/**
 * Class to create a simple Chat
 * @package Ruins
 */
class ClassicChat
{
    const MESSAGE_STATUS_NORMAL = 0;
    const MESSAGE_STATUS_OLDEDIT= 1;
    const MODE_NORMAL= 0;
    const MODE_EDIT= 1;

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
    private $_mode = self::MODE_NORMAL;

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
                    $this->_mode = self::MODE_NORMAL;
                } else {
                    $this->_mode = self::MODE_EDIT;
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

        $qb = getQueryBuilder();

        $result = $qb   ->select("chat")
                        ->from("Main:Chat", "chat")
                        ->where("chat.section = ?1")->setParameter(1, $this->_section)
                        ->andWhere("chat.status = ?2")->setParameter(2, self::MESSAGE_STATUS_NORMAL)
                        ->orderBy("chat.date", "DESC")
                        ->setFirstResult( $pagenr*$linesperpage )
                        ->setMaxResults( $linesperpage )
                        ->getQuery()
                        ->getResult();

        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Get the last written line at this Chat
     * @param Character|int $character Character Object or Character ID
     * @return array|bool The chatline (including it's id) or false if none is given
     */
    private function _getLastLine($character)
    {
        $qb = getQueryBuilder();

        $result = $qb   ->select("chat")
                        ->from("Main:Chat", "chat")
                        ->where("chat.section = ?1")->setParameter(1, $this->_section)
                        ->andWhere("chat.status = ?2")->setParameter(2, self::MESSAGE_STATUS_NORMAL)
                        ->andWhere("chat.author = ?3")->setParameter(3, $character)
                        ->orderBy("chat.date", "DESC")
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();

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
        $qb = getQueryBuilder();

        $chatlines = $qb->select('COUNT(chat.id)')
                        ->from("Main:Chat", "chat")
                        ->where('chat.section = ?1')->setParameter(1, $this->_section)
                        ->andWhere("chat.status = ?2")->setParameter(2, self::MESSAGE_STATUS_NORMAL)
                        ->getQuery()
                        ->getSingleScalarResult();

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
        global $em;

        if (!strlen($text)) {
            return false;
        }

        if ($user->character instanceof Character) {
            // Add new line
            $newline = new Chat;
            $newline->section  = $this->_section;
            $newline->author   = $user->character;
            $newline->chatline = $text;
            $newline->date     = new DateTime();
            $em->persist($newline);
            $em->flush();

            // Add DebugLogEntry
            $user->addDebuglog("Wrote at Chat $this->_section", "veryverbose");

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
        global $em;

        $qb = getQueryBuilder();

        // Get Data from oldline
        $oldline = $em->find("Main:Chat", $id);

        // Add new line
        $newline = new Chat;
        $newline->section  = $oldline->section;
        $newline->author   = $oldline->author;
        $newline->chatline = $text;
        $newline->date     = $oldline->date;
        $em->persist($newline);
        $em->flush();

        // Hide old Message from Chat
        $this->_updateMessageStatus($id, self::MESSAGE_STATUS_OLDEDIT);
    }

    /**
     * Update Messagestatus of a given Message
     * @param int $id ID of the Message to change Status
     * @param int $status New status
     */
    private function _updateMessageStatus($id, $status)
    {
        $qb = getQueryBuilder();

        $qb    ->update("Main:Chat", "chat")
               ->set("chat.status", (int)$status)
               ->where("chat.id = ?1")->setParameter(1, $id)
               ->getQuery()
               ->execute();
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
        if (!($badwords = SessionStore::readCache("badwords"))) {
            $qb = getQueryBuilder();

            $badwords = $qb->select("bw")
                           ->from("Main:Badword", "bw")
                           ->getQuery()
                           ->getResult();

             SessionStore::writeCache("badwords", $badwords);
        }

        foreach ($words as $key=>$word) {
            $purgedword = BtCode::purgeTags($word);

            foreach ($badwords as $badword) {
                if (array_search($purgedword, $badword) !== false) {
                    // this is the replacement
                    $words[$key] = $badword->replacement;
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

        $resultpage = array();

        for ($i=0; $i<sizeof($page); $i++) {
            // Format Date to userdefined chatdateformat
            $resultpage[$i]['date'] = $page[$i]->date->format($user->settings->chat_dateformat);

            // Get Displayname
            $resultpage[$i]['displayname'] = $page[$i]->author->displayname;

            // Apply Censorship
            if ($user->settings->chat_censorship) {
                $resultpage[$i]['chatline'] = $this->_chatlineCensorship($page[$i]->chatline);
            } else {
                $resultpage[$i]['chatline'] = $page[$i]->chatline;
            }

            // Check for special Commands
            $firstword 	= substr($resultpage[$i]['chatline'], 0, strpos($resultpage[$i]['chatline'], " "));
            $rest		= strstr($resultpage[$i]['chatline'], " ");

            if ($firstword == "/me" || $firstword == ":") {
                // Actions
                $resultpage[$i]['specialline'] = "`b* `b" . $resultpage[$i]['displayname'] . " " . $rest;

                unset($resultpage[$i]['displayname']);
                unset($resultpage[$i]['chatline']);
            } elseif ($firstword == "/mes") {
                // Actions with "'s"
                $resultpage[$i]['specialline'] = "`b* `b" . $resultpage[$i]['displayname'] . "'s " . $rest;

                unset($resultpage[$i]['displayname']);
                unset($resultpage[$i]['chatline']);
            } elseif ($firstword == "/em" || $firstword == "/X") {
                // Emotes
                $resultpage[$i]['specialline'] = $rest;

                unset($resultpage[$i]['displayname']);
                unset($resultpage[$i]['chatline']);
            }
        }

        $page = array_reverse($resultpage);

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
            throw new \Error("Section not set! A Chat has no Sectionname set!");
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
            case self::MODE_NORMAL:
                // Chatlines
                $chatsnippet = $this->_page->createTemplateSnippet();

                $chatsnippet->assign("chatname", $this->_section);
                $chatsnippet->assign("chatform", $chatform);
                $chatsnippet->assign("target", (string)$this->_page->url);
                // Nav for all Buttons (important)
                $this->_page->nav->addHiddenLink($this->_page->url);

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
                    $this->_page->nav->addHiddenLink($newurl);
                    $pagesoutput .= "<a href='?".$newurl."'>". $i . "</a> ";
                }


                $chatsnippet->assign("chat_pages", $pagesoutput);


                // visibility
                if ($user->settings->chat_hide) {
                    if (array_search($this->_section, $user->settings->chat_hide) !== false)
                    {
                        var_dump("hide");
                        // Chat is hidden
                        $chatsnippet->assign("visibility", "none");
                        $chatsnippet->assign("visibility_inv", "inline");
                    } else {
                        $chatsnippet->assign("visibility", "inline");
                        $chatsnippet->assign("visibility_inv", "none");
                    }

                } else {
                    $chatsnippet->assign("visibility", "inline");
                    $chatsnippet->assign("visibility_inv", "none");
                }


                $this->_page->output($chatsnippet->fetch("snippet_classicchat.tpl"), true);

                break;

            case self::MODE_EDIT:

                if ($row = $this->_getLastLine($user->character)) {
                    $chattable = new SimpleTable;

                    // Editfield
                    $output .= $chattable->startData();
                    $this->_page->addForm($chatform, true);
                    $this->_page->getForm($chatform)->head($chatform, $this->_page->url);
                    $this->_page->getForm($chatform)->hidden($chatform."_op", "editLine");
                    $this->_page->getForm($chatform)->hidden($chatform."_section", $this->_section);
                    $this->_page->getForm($chatform)->hidden($chatform."_editLineID", $row->id);
                    $this->_page->getForm($chatform)->setCSS("floatleft textarea");
                    $this->_page->getForm($chatform)->textArea($chatform."_chatline", BtCode::exclude($row->chatline), 60, 10);

                    $this->_page->getForm($chatform)->setCSS("floatleft button");
                    $this->_page->getForm($chatform)->submitButton("Ändern");
                    $this->_page->getForm($chatform)->close();

                    $this->_page->output("`n`n");

                    // BackButton
                    $this->_page->nav->addHiddenLink("", $this->_page->url);
                    $this->_page->addForm("refreshbutton");
                    $this->_page->getForm("refreshbutton")->head("refreshbutton", $this->_page->url);
                    $this->_page->getForm("refreshbutton")->setCSS("button");
                    $this->_page->getForm("refreshbutton")->submitButton("Zurück");
                    $this->_page->getForm("refreshbutton")->close();
                } else {
                    $this->_page->output("Editieren des letzten Eintrages nicht möglich!`n`n");

                    // BackButton
                    $this->_page->nav->addHiddenLink("", $this->_page->url);
                    $this->_page->addForm("refreshbutton");
                    $this->_page->getForm("refreshbutton")->head("refreshbutton", $this->_page->url);
                    $this->_page->getForm("refreshbutton")->setCSS("button");
                    $this->_page->getForm("refreshbutton")->submitButton("Zurück");
                    $this->_page->getForm("refreshbutton")->close();
                }
                break;
        }
    }
}

?>
