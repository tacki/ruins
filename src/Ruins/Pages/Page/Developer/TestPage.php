<?php
/**
 * Testpage
 *
 * Simple Testpage of 'Ruins'
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Developer;
use Ruins\Main\Controller\Link;
use Ruins\Main\Controller\TimerController as Timer;
use Ruins\Main\Controller\Travel;
use Ruins\Main\Controller\Dice;
use Ruins\Common\Controller\BtCode;
use Ruins\Common\Controller\Config;
use Ruins\Common\Controller\SessionStore;
use Ruins\Common\Controller\StackObject;
use Ruins\Common\Controller\Form;
use Ruins\Common\Controller\Table;
use Ruins\Main\Manager\SystemManager;
use Ruins\Main\Manager\RightsManager;
use Ruins\Main\Manager\MessageManager;
use Ruins\Common\Controller\Registry;
use Ruins\Main\Controller\Page;
use Ruins\Common\Interfaces\PageObjectInterface;

class TestPage extends Page implements PageObjectInterface
{
    protected $pagetitle  = "Ruins Test Page";

    public function setTitle()
    {
        $this->set("pagetitle", $this->pagetitle);
        $this->set("headtitle", $this->pagetitle);
    }

    public function createMenu()
    {
        $this->nav->addHead("Ruins")
                  ->addLink("Testpage", "Page/Developer/TestPage");
    }

    public function createContent(array $parameters)
    {
        // CLEAR CACHE
        $em          = Registry::getEntityManager();
        $systemCache = Registry::get('main.cache');
        $systemCache->deleteAll();

        $mt = microtime(true);

        // Config-Test START
        $systemConfig = Registry::getMainConfig();

        $this->output("Starting Config Test: `n");
        $position = "configtest";

        $systemConfig->set("test", "123");
        $systemConfig->set("test", "321");
        if ($systemConfig->get("test") == "321") {
            $this->output("Configtest successful `n");
        }

        // Config-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // User-Test START
        $this->output("Starting User Test: `n");
        $position = "usertest";

        $user = $em->find("Main:User",1);

        if ($user) {
            $this->output("Usertest successful `n");
        }

        // User-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // User-Test START
        $this->output("Generate secure Password Test: `n");
        $position = "passwordtest";

        if (CRYPT_SHA512) $alg = "SHA512";
        elseif (CRYPT_SHA256) $alg = "SHA256";
        elseif (CRYPT_BLOWFISH) $alg = "Blowfish";
        elseif (CRYPT_MD5) $alg = "MD5";

        $this->output("Using ".$alg." as hashing algorithm`n`n");

        $hashedPassword = $em->getRepository("Main:User")->hashPassword("password");

        $this->output($hashedPassword."`n`n");
        $this->output("Now checking if new Passwordhash is correct:`n");

        if ($em->getRepository("Main:User")->hashPassword("password", $hashedPassword)) {
            $this->output("Yes: Passwordtest successful `n");
        }

        // User-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Global Timer-Test START
        $this->output("Starting Global Timer Test: `n");
        $position = "globaltimertest";

        $timer = $em->getRepository("Main:Timer")
        ->create("1hourtimertest");

        switch($_GET['op'])
        {
            case "stoptimer";
            $timer->stop();
            break;

            case "starttimer":
                $timer->start();
                break;
        }

        if ($timer->get()) {
            $this->output("1 Hour Timer: ". $timer->get(), true);
        } else {
            $timer->set(0, 0, 1);
            $this->output("1 Hour Timer: ". $timer->get(), true);
        }

        $tempform = new Form($this);
        if ($_GET['op'] == "stoptimer") {
            $tempform->head("", $this->url->base."/starttimer");
            $tempform->submitButton("Start");
        } else {
            $tempform->head("", $this->url->base."/stoptimer");
            $tempform->submitButton("Stop");
        }
        $tempform->close();

        unset ($timer);

        // Global Timer-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // btcode Test START
        $this->output("Starting btcode Test: `n");
        $position = "btcodetest";

        $colors = array (
                        "Gray" => 0x0f,
                        "Red" => 0x1f,
                        "Orange" => 0x2f,
                        "Yellow" => 0x3f,
                        "Lime" => 0x4f,
                        "Light Green" => 0x5f,
                        "Solid Green" => 0x6f,
                        "Green-Blue" => 0x7f,
                        "Turquoise" => 0x8f,
                        "Turquoise-Blue" => 0x9f,
                        "Light Blue" => 0xaf,
                        "Solid Blue" => 0xbf,
                        "Solid Purple" => 0xcf,
                        "Purple" => 0xdf,
                        "Pink" => 0xef,
                        "Solid Pink" => 0xff,
        );

        $counter = 0x00;

        foreach ($colors as $colorname => $maxcount) {
            $this->output($colorname . ":`n");

            $outputbuffer_fg = "";
            $outputbuffer_bg = "";

            for (; $counter<=$maxcount; $counter++) {
                $outputbuffer_fg .= "`#" . sprintf("%02x", $counter) . "### ";
                $outputbuffer_bg .= "`~" . sprintf("%02x", $counter) . "### ";
            }

            $this->output($outputbuffer_fg . $outputbuffer_bg . "`n");
        }

        $this->output("Invalid: `n `5Invalid Colorcode`n");

        $this->output("`n");
        $this->output("Bold (b): `n `bBold Text`b `n");
        $this->output("Centered (c): `n `cCentered Text`c");
        $this->output("Italic (i): `n `iItalic Text`i `n");
        $this->output("Newline (n): `n New ->`n<- line `n");
        $this->output("Big (g): `n `gBig Text`g `n");
        $this->output("Small (s): `n `sSmall Text`s `n");
        $this->output("Sub (u): `n Normal Text `uSub Text`u `n");
        $this->output("Sup (p): `n Normal Text `pSup Text`p `n");
        $this->output("`n");

        $textsample = "`#54Das `#35i`#36s`#37t `#19ja `#99ein `bdi`#55c`#56k`#57e`#58r `#85`iHund`i`b!";

        $this->output("Zusammen: `n". $textsample ."`n");

        $this->output("Der gleiche Text nach einem purge: `n". BtCode::purgeTags($textsample) . "`n");

        $this->output("`n`nAJAX Examples`n");

        $this->output("<label for='chat'>Chatpreview: </label>", true);
        $this->output("<input type='text' id='chat' />", true);
        $this->output("<div id='chatpreview'></div>", true);
        $this->addJavaScriptFile("colorpreview.func.js");

        $this->addJavaScript("
            $(function() {
                setColorPreview('chat', 'chatpreview');
            });
         ");

        $this->output("<label for='searchField'>Charnames: </label>", true);
        $this->output("<input type='text' id='searchField' name='searchField'>", true);
        $this->addCommonCSS("autocomplete.css");
        $this->addJavaScriptFile("autocomplete.func.js");

        $this->addJavaScript("
            $(function() {
                setAutoComplete('searchField', 'results', '" . SystemManager::getOverloadedFilePath("Helpers/ajax/autocomplete_charname.ajax.php", true)."?part=');
            });
        ");
        // btcode Test END

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // QueryBuilder && Table Test Start
        $em = Registry::getEntityManager();

        $position = "tabletest";

        $this->output("Starting Lists Test:`n`n");
        $this->output("Geordnet nach Gold:`n");

        $qb = $em->createQueryBuilder();

        $result = $qb   ->select("char.displayname, char.id, char.money, char.lifepoints, char.healthpoints")
        ->from("Main:Character", "char")
        ->where("char.money >= 0")
        ->orderBy("char.money", "DESC")
        ->addOrderBy("char.lifepoints", "ASC")
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();

        $thetable = new Table;
        //$thetable->setCSS("`~35");
        $thetable->setSecondRowCSS("`~25");
        $thetable->setTabAttributes(false,2);
        $thetable->addTabHeader(array("Name","ID","Gold","LP","HP"),false,array("`b","`b`c","`b`c","`b`c","`b`c"));
        // This following addTabRow will put the array in the first position of the table behind the header
        $thetable->addTabRow(array("teruk","7","2300","34","12"));
        $thetable->addListArray($result, false, "`~35");

        $this->output($thetable->load()->getHTML(),true);

        // Case 2:
        $this->output("`nGeordnet nach HP:`n");
        $result = $qb	->orderBy("char.healthpoints")
        ->getQuery()
        ->getResult();

        $newtab = new Table;
        //$newtab->setCSS("`~35");
        $newtab->setTabAttributes(false,2);
        $newtab->addTabHeader(array("Name","ID","Gold","LP","HP"),false,array("`b","`b`c","`b`c","`b`c","`b`c"));
        $newtab->addListArray($result);
        $this->output($newtab->load()->getHTML(),true);

        // Case 2 (alternative):
        $this->output("`nGeordnet nach HP:`n");
        $qb->orderBy("char.healthpoints");

        $newtab = new Table;
        //$newtab->setCSS("`~35");
        $newtab ->setTabAttributes(false,2)
        ->addTabHeader(array("Name","ID","Gold","LP","HP"),false,array("`b","`b`c","`b`c","`b`c","`b`c"))
        ->addListObject($qb);
        $this->output($newtab->load()->getHTML(),true);

        // Case 3:
        $this->output("`n`n");
        $design = new Table;
        //$design->setCSS("`~35");
        $design->setTabAttributes("80%",2);
        $design->addTabSize(20,7);
        $design->addFieldContent(1,1,"Dunkelelf");
        $design->addFieldContent(1,2,"Troll");
        $design->addFieldContent(1,3,"Org");
        $design->addFieldContent(1,4,"Org");
        $design->addFieldContent(1,5,"Taurus");
        $design->addFieldContent(1,6,"Org");
        $design->addFieldContent(2,1,"Dunkelelfbild",false,false,3);
        $design->addFieldContent(2,2,"Trollbild",false,false,3);
        $design->addFieldContent(2,3,"Orgbild",false,false,3);
        $design->addFieldContent(2,4,"Orgbild",false,false,3);
        $design->addFieldContent(2,5,"Taurusbild",false,false,3);
        $design->addFieldContent(2,6,"Orgbild",false,false,3);
        $design->addFieldContent(5,1,"Healthstatus");
        $design->addFieldContent(5,2,"Healthstatus");
        $design->addFieldContent(5,3,"Healthstatus");
        $design->addFieldContent(5,4,"Healthstatus");
        $design->addFieldContent(5,5,"Healthstatus");
        $design->addFieldContent(5,6,"Healthstatus");
        $design->addFieldContent(6,1,"Kampfbericht",false,false,5,6);
        $design->addFieldContent(11,1,"Hasturion");
        $design->addFieldContent(11,2,"Sophia");
        $design->addFieldContent(11,3,"tacki");
        $design->addFieldContent(11,4,"Bown");
        $design->addFieldContent(11,5,"teruk");
        $design->addFieldContent(11,6,"Waldemar");
        for ($u=1;$u<7;$u++)
        {
            $design->addFieldContent(12,$u,"Bild",false,false,2);
            $design->addFieldContent(13,$u,"Warten/Senden");
            $design->addFieldContent(14,$u,"Healthstatus");
            $design->addFieldContent(15,$u,"Nahkampf");
            $design->addFieldContent(16,$u,"Fernkampf");
            $design->addFieldContent(17,$u,"Magie");
            $design->addFieldContent(18,$u,"Heiltrank");
            $design->addFieldContent(19,$u,"Verteidigen");
            $design->addFieldContent(20,$u,"Flucht");
        }
        for ($i=1;$i<21;$i++)
        {
            $design->addFieldContent($i,7,"Zeile:".$i);
        }
        $this->output($design->load()->getHTML(),true);

        $this->output("`n`n");
        //$this->addform(true,"head","name","test.php","post");
        $design1 = new Table;
        //$design1->setCSS("`~35");
        $design1->setTabAttributes("40%",2);
        $design1->addTabSize(4,4);
        $design1->addFieldContent(1,1,"Oben links",false,"`~25",1,1);
        $design1->addFieldContent(2,1,"Oben unten links",false,false,1,1);
        $design1->addFieldContent(1,2,"Oben halb links",false,false,1,1);
        $design1->addFieldContent(1,3,"Oben halb rechts",false,false,1,1);
        $design1->addFieldContent(1,4,"Oben rechts",false,false,2,1);
        $design1->addFieldContent(2,2,/*$this->addform(false,"inputText","mitte")*/"",false,"`c",2,2);
        $design1->addFieldContent(3,1,"Unten links",false,false,2,1);
        $design1->addFieldContent(3,4,"Unten oben rechts",false,false,1,1);
        $design1->addFieldContent(4,4,"Unten rechts",false,false,1,1);
        $design1->addFieldContent(4,2,"Unten",false,false,1,2);
        $this->output($design1->load()->getHTML(),true);
        //$this->addform(true,"close");
        // Querytool & Table Test end

        // Neue Tabellenform:
        $this->output("`n`n");
        $this->addTable("testtabelle");
        //$this->testtabelle->setCSS("`~35");
        $this->getTable("testtabelle")->setTabAttributes("40%",2);
        $this->getTable("testtabelle")->addTabSize(4,4);
        $this->getTable("testtabelle")->addFieldContent(1,1,"Oben links",false,"`~25",1,1);
        $this->getTable("testtabelle")->addFieldContent(2,1,"Oben unten links",false,false,1,1);
        $this->getTable("testtabelle")->addFieldContent(1,2,"Oben halb links",false,false,1,1);
        $this->getTable("testtabelle")->addFieldContent(1,3,"Oben halb rechts",false,false,1,1);
        $this->getTable("testtabelle")->addFieldContent(1,4,"Oben rechts",false,false,2,1);
        $this->getTable("testtabelle")->addFieldContent(2,2,/*$this->addform(false,"inputText","mitte")*/"",false,"`c",2,2);
        $this->getTable("testtabelle")->addFieldContent(3,1,"Unten links",false,false,2,1);
        $this->getTable("testtabelle")->addFieldContent(3,4,"Unten oben rechts",false,false,1,1);
        $this->getTable("testtabelle")->addFieldContent(4,4,"Unten rechts",false,false,1,1);
        $this->getTable("testtabelle")->addFieldContent(4,2,"Unten",false,false,1,2);
        $this->getTable("testtabelle")->load();

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Link-Class + Rights-Class Test start
        $position = "link&rightstest";
        $this->output("Starting Links+Rights Test:`n");

        $user = $em->find("Main:User",1);
        $user->login();

        // Adding Group
        RightsManager::createGroup("TempGroup");

        // Adding User to Group
        RightsManager::addToGroup("TempGroup", $user->character);

        $this->output("Is Character {$user->character->name} in Group TempGroup?`n");
        if (RightsManager::isInGroup("TempGroup", $user->character)) {
            $this->output("Yes!`n");
        } else {
            $this->output("No!`n");
        }

        foreach($user->character->groups as $group) {
            $this->output("Character ".$user->character->name." is in Group:" . $group->name . "`n");
        }

        // Removing User from Group
        RightsManager::removeFromGroup("TempGroup", $user->character);

        // Remove Group
        RightsManager::removeGroup("TempGroup", $user->character);

        // Link-Class + Rights-Class Test end

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Navigation Test start
        $position = "navtest";
        $this->output("Starting Navigation Test:`n");

        $this->output("see left side ;)");

        $this->nav->disableValidation();
        $this->nav->addHead("Home")
             ->addLink("Login Page", "Page/Common/LoginPage","main")
             ->addLink("Testpage", "Page/Developer/TestPage","main");

        $this->nav->addHead("Dorf")
             ->addLink("Ausgang", "Page/Common/LogoutPage","main")
             ->addLink("Home", "Page/Developer/TestPage","shared")
             ->addLink("Logout", "Page/Common/LogoutPage","shared");

        $this->nav->save();

        // Navigation Test end

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Money Test start
        $position = "moneytest";
        $this->output("Starting Money+Manager-Module Test:`n");

        $user = $em->find("Main:User",1);

        $this->output("`nStarting Money from {$user->character->displayname}:`n");

        $this->output("Gold: ". $user->character->money->getCurrency("gold") ."`n");
        $this->output("Silver: ". $user->character->money->getCurrency("silver") ."`n");
        $this->output("Copper: ". $user->character->money->getCurrency("copper") ."`n");

        $this->output("`nReceive 5 Gold from dead uncle:`n");
        if (!$user->character->money->receive(5, "gold")) {
            $this->output("Transaction failed!`n");
        }

        $this->output("Gold: ". $user->character->money->getCurrency("gold") ."`n");
        $this->output("Silver: ". $user->character->money->getCurrency("silver") ."`n");
        $this->output("Copper: ". $user->character->money->getCurrency("copper") ."`n");

        $this->output("`nPay 20 Silver for Ale:`n");
        if (!$user->character->money->pay(20, "silver")) {
            $this->output("Transaction failed!`n");
        }

        $this->output("Gold: ". $user->character->money->getCurrency("gold") ."`n");
        $this->output("Silver: ". $user->character->money->getCurrency("silver") ."`n");
        $this->output("Copper: ". $user->character->money->getCurrency("copper") ."`n");

        $this->output("`nTry to pay 100 Gold to Mafia via Check:`n");
        if (!$user->character->money->pay(100, "gold")) {
            $this->output("Transaction failed! Oh Oh, Mafia is coming!`n");
        } else {
            $this->output("Transaction OK! The Mafia is pleased!`n");
        }

        $this->output("Gold: ". $user->character->money->getCurrency("gold") ."`n");
        $this->output("Silver: ". $user->character->money->getCurrency("silver") ."`n");
        $this->output("Copper: ". $user->character->money->getCurrency("copper") ."`n");

        // Money Test end

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // News Test Start

        $position = "newstest";

        $newstab = new Table;
        //$newtab->setCSS("`~35");
        $newstab->setTabAttributes(false,2);
        $newstab->addTabHeader(array("ID","Datum","Author","Titel","HP", "Inhalt", "Ort"));

        if (!($newslist = SystemManager::getNews())) {
            SystemManager::addNews("Skandal!", "Heute wieder kein Weltuntergang");
        } else {
            $newstab->addListArray($newslist);
        }
        $this->output($newstab->load()->getHTML(),true);
        /*
         $news = new News;
        $news->setNewsAttributes(3,"80%","`~35","`~35","`~35");
        $this->output($news->load(),true);
        */
        // News Test End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Form Test Start

        $position = "formtest";

        $this->output("Formulartest!`n1.Formular:`nName");
        $this->addForm("testformular");
        $this->getForm("testformular")->head("form", "test.php");
        $this->getForm("testformular")->selectStart("selectform");
        $this->getForm("testformular")->selectOption("Jack");
        $this->getForm("testformular")->selectOption("Jim", false, true);
        $this->getForm("testformular")->selectOption("Johnny");
        $this->getForm("testformular")->selectEnd();
        $this->output("`nBegründung`n");
        $this->getForm("testformular")->textArea("textareaform","Hier kannst du deine Auswahl begründen!");
        $this->output("`n");
        $this->getForm("testformular")->submitButton("Absenden");
        $this->getForm("testformular")->close();

        if (isset($_POST['textareaform'])) $this->output("Wenn Formular 1 ausgeführt wurde, steht im folgenden die Begründung:`n`#25".$_POST['textareaform']."`n",true);


        // Form Test End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Message Test Start
        $this->output("Starting Message Test: `n");
        $position = "messagetest";

        $user = $em->find("Main:User",1);

        $this->output("Sending Message from {$user->character->displayname} to {$user->character->displayname}`n");
        MessageManager::write($user->character, $user->character, "du...", "...idiota!");

        $this->output("`bInbox of {$user->character->displayname}:`b`n");
        $messagelist = MessageManager::getInbox($user->character);
        $showlist = array();
        foreach ($messagelist as $message) {
            $showmessage = array();
            $showmessage['id']		  = $message->id;
            $showmessage['sender']	  = $message->sender->name;
            $showmessage['receiver']  = $message->receiver->name;
            $showmessage['subject']   = $message->data->subject;
            $showmessage['date']      = $message->date->format("H:i:s d.m.y");
            $showmessage['status']    = $message->status;
            $showlist[] = $showmessage;
        }

        $newtab = new Table;
        $newtab->setCSS("`~9f");
        $newtab->setTabAttributes(false,0);
        $newtab->addTabHeader(array("id","sender","receiver","subject","date","status"),false,array("`b","`b`c","`b`c","`b`c","`b`c","`b`c"));
        $newtab->addListArray($showlist);
        $this->output($newtab->load()->getHTML(),true);

        $this->output("Deleting last Message`n");
        $lastMessage = MessageManager::getInbox($user->character, 1, false);
        MessageManager::delete($lastMessage);

        $this->output("`bInbox of {$user->character->displayname}:`b`n");
        $messagelist = MessageManager::getInbox($user->character);
        $messagelist = MessageManager::getInbox($user->character);
        $showlist = array();
        foreach ($messagelist as $message) {
            $showmessage = array();
            $showmessage['id']		  = $message->id;
            $showmessage['sender']	  = $message->sender->name;
            $showmessage['receiver']  = $message->receiver->name;
            $showmessage['subject']   = $message->data->subject;
            $showmessage['date']      = $message->date->format("H:i:s d.m.y");
            $showmessage['status']    = $message->status;
            $showlist[] = $showmessage;
        }

        MessageManager::flushDeleted($user->character);

        $newtab = new Table;
        $newtab->setCSS("`~9f");
        $newtab->setTabAttributes(false,0);
        $newtab->addTabHeader(array("id","sender","receiver","subject","date","status"),false,array("`b","`b`c","`b`c","`b`c","`b`c","`b`c"));
        $newtab->addListArray($showlist);
        $this->output($newtab->load()->getHTML(),true);

        // Message Test End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // StackObject Test Start

        $this->output("Starting Stack Object Test: `n");
        $position = "stackobjecttest";

        $array = array(1,2,3);

        $stack = new StackObject(3);
        $stack->import($array);

        $this->output("Initialize with 1,2,3 (maxed to 3 Elements!)`n");

        foreach($stack->export() as $element) {
            $this->output($element . "`n");
        }

        $this->output("`nStep 1: Add Element '4'`n");

        $stack->add(4);

        $this->output("Stackcontent:`n");
        foreach($stack->export() as $element) {
            $this->output($element . "`n");
        }

        $this->output("`nStep 2: Remove First Element`n");

        $stack->delFirst();

        $this->output("Stackcontent:`n");
        foreach($stack->export() as $element) {
            $this->output($element . "`n");
        }

        $this->output("`nStep 3: Get Last Element`n");

        $this->output($stack->getLast() . "`n");

        $this->output("`nStep 4: Is '2' inside the Stack?`n");

        $this->output(($stack->contains(2)?"Yes":"No") . "`n");

        $this->output("`nStep 5: Is '3' inside the Stack?`n");

        $this->output(($stack->contains(3)?"Yes":"No") . "`n");

        // StackObject Test End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Dice Test Start

        $this->output("Dice Roll Test: `n");
        $position = "stackobjecttest";

        $this->output("Roll 1D4:`n");
        $this->output(Dice::rollD4() . "`n");

        $this->output("Roll 2D6:`n");
        $this->output(Dice::rollD6(2) . "`n");

        $this->output("Roll 1D20:`n");
        $this->output(Dice::rollD20() . "`n");

        $this->output("Roll 2D100:`n");
        $this->output(Dice::rollD100(2) . "`n");

        // Dice Test End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // QueryBuilder Start
        $em = Registry::getEntityManager();

        $this->output("QueryBuilder Start: `n");
        $position = "QueryBuildertest";

        $qb = $em->createQueryBuilder();

        $result = $qb ->select("char.id, char.displayname")
        ->from("Main:Character", "char")
        ->where("char.id > 0")
        ->orderBy("char.id", "DESC")
        ->getQuery()->getResult();

        $table = new Table();
        //$table->setCSS("`~35");
        $table->setTabAttributes(false,2);
        $table->addTabHeader(array("id","displayname"),false,array("`b","`b`c"));
        $table->addListArray($result);
        $this->output($table->load()->getHTML(),true);

        // QueryBuilder End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Classic Chat Start

        $this->output("Classic Chat Start: `n");
        $position = "classicchattest";

        $this->addChat("testchat")->show();

        // Classic Chat End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // OpenID Start

        $this->output("Enter your OpenID: `n");

        $this->addForm("openid");
        $this->getForm("openid")->head("login", "page/developer/test/checkopenid");
        $this->getForm("openid")->inputText("openid_identifier");
        $this->getForm("openid")->submitButton("Check");
        $this->getForm("openid")->close();

        if ($_GET['op'] == "checkopenid") {
            OpenIDSystem::checkOpenID($_POST['openid_identifier'], "page/developer/test/returnopenid");

            if ($error = SessionStore::get("openiderror")) {
                $this->output($error);
                SessionStore::remove("openiderror");
            }

        } elseif ($_GET['op'] == "returnopenid") {

            $result = OpenIDSystem::evalTrustResult("page/developer/test/returnopenid");

            if ($error = SessionStore::get("openiderror")) {
                $this->output($error);
                SessionStore::remove("openiderror");
            } else {
                $this->output("Successfull auth for ". $result['openid']);
            }
        }

        // OpenID End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Race Module Start
        /*
         $this->output("Race Definition Start: `n");
        $position = "racedefinitiontest";

        $user = $em->find("Main:User",1);
        $user->load(1);
        $user->loadCharacter();

        $this->output("Generate Stats:`n");
        $this->output("Race: " . $user->char->race->getHumanReadable() . "`n");
        $this->output("Sex: " . $user->char->sex . "`n");
        $this->output("Height: " . $user->char->race->generateHeight() . "cm `n");
        $this->output("Weight: " . $user->char->race->generateWeight() . "kg `n");
        $this->output("Age: " . $user->char->race->generateAge() . " years old `n");
        $this->output("Max. Age " . $user->char->race->generateMaxAge() . " years `n");
        */
        // Race Module End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Item Handling Start

        $this->output("Item Handling Start: `n");
        $position = "itemhandling";

        $item = $em->getRepository("Main:Item")->findOneByClass("fish");

        if ($item) {
            $this->output("`n");
            $this->output("Item Name: " . $item->name . " (Level: " . $item->level . ")`n");
            $this->output("Item Wert: " . $item->value->getAllCurrenciesWithPic() . "`n", true);
            $this->output("`n");
        }

        $weapon = $em->getRepository("Main:Items\Weapon")->findOneByClass("weapon");

        if ($weapon) {
            $this->output("`n");
            $this->output("Item Name: " . $weapon->name . " (Level: " . $weapon->level . ")`n");
            $this->output("Waffen Schaden: " . $weapon->showDamage(false) . "`n", true);
            $this->output("Item Wert: " . $weapon->value->getAllCurrenciesWithPic(). "`n", true);
            $this->output("`n");
        }

        $armorset = $em->find("Main:ArmorSet", 1);

        if ($armorset) {
            $this->output("`n");
            $this->output("Kopf: " . $armorset->head->name . " - RK: " . $armorset->head->getArmorClass() . " (Level: " . $armorset->head->level . ")`n");
            $this->output("Wert: " . $armorset->head->value->getAllCurrenciesWithPic() . "`n", true);
            $this->output("Oberkörper: " . $armorset->chest->name . " - RK: " . $armorset->chest->getArmorClass() . " (Level: " . $armorset->chest->level . ")`n");
            $this->output("Wert: " . $armorset->chest->value->getAllCurrenciesWithPic() . "`n", true);
            $this->output("Arme: " . $armorset->arms->name . " - RK: " . $armorset->arms->getArmorClass() . " (Level: " . $armorset->arms->level . ")`n");
            $this->output("Wert: " . $armorset->arms->value->getAllCurrenciesWithPic() . "`n", true);
            $this->output("Beine: " . $armorset->legs->name . " - RK: " . $armorset->legs->getArmorClass() . " (Level: " . $armorset->legs->level . ")`n");
            $this->output("Wert: " . $armorset->legs->value->getAllCurrenciesWithPic() . "`n", true);
            $this->output("Füße: " . $armorset->feet->name . " - RK: " . $armorset->feet->getArmorClass() . " (Level: " . $armorset->feet->level . ")`n");
            $this->output("Wert: " . $armorset->feet->value->getAllCurrenciesWithPic() . "`n", true);
            $this->output("Gesamt RK: " . $armorset->getTotalArmorClass());
        }

        // Itemhandling End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Output Module Start

        // OutputModules, which are 'enabled' at the Database, are loaded automatically
        // during the $this->create() Process

        // force loading of the 'example'-Module
        //ModuleSystem::enableOutputModule($this, "example");

        //$this->output("see links and top- and bottom-note");

        // Output Module End

        // *************************************
        //$res = microtime(true) - $mt;
        //$this->output("`n"."Dauer: ".$res." seconds");
        //$this->output("`n`n*************************************`n`n");
        //$mt = microtime(true);
        // *************************************

        // page Test Start (this has to be the last test)

        $this->output("Starting Page Test: `n");
        $position = "pagetest";
        $this->output("looks like it's working`n");

        // page Test END

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************


        /* Ende Ausklammern */

        // Waypoint Traveling System Start

        $travel = new Travel;
        $this->output("Der Startpunkt ist Ironlance. `n");
        $this->output("Der Zielort ist Derashok.`n");

        $ironlance = $em->getRepository("Main:Site")->findOneByName("ironlance/citysquare");
        $dunsplee  = $em->getRepository("Main:Site")->findOneByName("dunsplee/trail");
        $derashok  = $em->getRepository("Main:Site")->findOneByName("derashok/tribalcenter");

        if ($travel->isConnected($ironlance->waypoint, $derashok->waypoint)){
            $this->output("Sind verbunden. Die Reisedauer betraegt: ");
            $this->output($travel->findWay($ironlance->waypoint, $derashok->waypoint));
            $this->output(" Sekunden.`n`n");
        }else {
            $this->output("Nope, sind nicht verbunden.`n`n");
        }

        $this->output("Der Startpunkt ist Ironlance. `n");
        $this->output("Der Zielort ist Dunsplee.`n");
        if ($travel->isConnected($ironlance->waypoint, $dunsplee->waypoint)){
            $this->output("Sind verbunden. Die Reisedauer betraegt: ");
            $this->output($travel->findWay($ironlance->waypoint, $dunsplee->waypoint));
            $this->output(" Sekunden.`n`n");
        }else {
            $this->output("Nope, sind nicht verbunden.`n`n");
        }

        // Waypoint Traveling System End

        // *************************************
        $res = microtime(true) - $mt;
        $this->output("`n"."Dauer: ".$res." seconds");
        $this->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

    }
}