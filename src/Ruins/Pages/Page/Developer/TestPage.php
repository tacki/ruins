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
use Ruins\Common\Manager\HtmlElementManager;
use Ruins\Common\Manager\RequestManager;
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
use Ruins\Common\Controller\AbstractPageObject;

class TestPage extends AbstractPageObject
{
    public $title  = "Ruins Test Page";

    public function createContent($page, $parameters)
    {
        // CLEAR CACHE
        $em          = Registry::getEntityManager();
        $systemCache = Registry::get('main.cache');
        $systemCache->deleteAll();

        $mt = microtime(true);

        // Config-Test START
        $systemConfig = Registry::getMainConfig();

        $page->output("Starting Config Test: `n");
        $position = "configtest";

        $systemConfig->set("test", "123");
        $systemConfig->set("test", "321");
        if ($systemConfig->get("test") == "321") {
            $page->output("Configtest successful `n");
        }

        // Config-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // User-Test START
        $page->output("Starting User Test: `n");
        $position = "usertest";

        $user = $em->find("Main:User",1);

        if ($user) {
            $page->output("Usertest successful `n");
        }

        // User-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // User-Test START
        $page->output("Generate secure Password Test: `n");
        $position = "passwordtest";

        if (CRYPT_SHA512) $alg = "SHA512";
        elseif (CRYPT_SHA256) $alg = "SHA256";
        elseif (CRYPT_BLOWFISH) $alg = "Blowfish";
        elseif (CRYPT_MD5) $alg = "MD5";

        $page->output("Using ".$alg." as hashing algorithm`n`n");

        $hashedPassword = $em->getRepository("Main:User")->hashPassword("password");

        $page->output($hashedPassword."`n`n");
        $page->output("Now checking if new Passwordhash is correct:`n");

        if ($em->getRepository("Main:User")->hashPassword("password", $hashedPassword)) {
            $page->output("Yes: Passwordtest successful `n");
        }

        // User-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Global Timer-Test START
        $page->output("Starting Global Timer Test: `n");
        $position = "globaltimertest";

        $timer = $em->getRepository("Main:Timer")
                    ->create("1hourtimertest");

        switch($parameters['op'])
        {
            case "stoptimer";
                $timer->stop();
                break;

            case "starttimer":
                $timer->start();
                break;
        }

        if ($timer->get()) {
            $page->output("1 Hour Timer: ". $timer->get(), true);
        } else {
            $timer->set(0, 0, 1);
            $page->output("1 Hour Timer: ". $timer->get(), true);
        }

        $tempform = new Form($page);
        if ($parameters['op'] == "stoptimer") {
            $tempform->head("", $page->getUrl()->base."/starttimer");
            $tempform->submitButton("Start");
        } else {
            $tempform->head("", $page->getUrl()->base."/stoptimer");
            $tempform->submitButton("Stop");
        }
        $tempform->close();

        unset ($timer);

        // Global Timer-Test END

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // btcode Test START
        $page->output("Starting btcode Test: `n");
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
            $page->output($colorname . ":`n");

            $outputbuffer_fg = "";
            $outputbuffer_bg = "";

            for (; $counter<=$maxcount; $counter++) {
                $outputbuffer_fg .= "`#" . sprintf("%02x", $counter) . "### ";
                $outputbuffer_bg .= "`~" . sprintf("%02x", $counter) . "### ";
            }

            $page->output($outputbuffer_fg . $outputbuffer_bg . "`n");
        }

        $page->output("Invalid: `n `5Invalid Colorcode`n");

        $page->output("`n");
        $page->output("Bold (b): `n `bBold Text`b `n");
        $page->output("Centered (c): `n `cCentered Text`c");
        $page->output("Italic (i): `n `iItalic Text`i `n");
        $page->output("Newline (n): `n New ->`n<- line `n");
        $page->output("Big (g): `n `gBig Text`g `n");
        $page->output("Small (s): `n `sSmall Text`s `n");
        $page->output("Sub (u): `n Normal Text `uSub Text`u `n");
        $page->output("Sup (p): `n Normal Text `pSup Text`p `n");
        $page->output("`n");

        $textsample = "`#54Das `#35i`#36s`#37t `#19ja `#99ein `bdi`#55c`#56k`#57e`#58r `#85`iHund`i`b!";

        $page->output("Zusammen: `n". $textsample ."`n");

        $page->output("Der gleiche Text nach einem purge: `n". BtCode::purgeTags($textsample) . "`n");

        $page->output("`n`nAJAX Examples`n");

        $page->output("<label for='chat'>Chatpreview: </label>", true);
        $page->output("<input type='text' id='chat' />", true);
        $page->output("<div id='chatpreview'></div>", true);
        $page->addJavaScriptFile("colorpreview.func.js");

        $page->addJavaScript("
            $(function() {
                setColorPreview('chat', 'chatpreview');
            });
         ");

        $page->output("<label for='searchField'>Charnames: </label>", true);
        $page->output("<input type='text' id='searchField' name='searchField'>", true);
        $page->addCSS("autocomplete.css");
        $page->addJavaScriptFile("autocomplete.func.js");

        $page->addJavaScript("
            $(function() {
                setAutoComplete('searchField', 'results', '".RequestManager::getWebBasePath()."/"."Json/Common/AutocompleteCharname?part=');
            });
        ");
        // btcode Test END

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // QueryBuilder && Table Test Start
        $em = Registry::getEntityManager();

        $position = "tabletest";

        $page->output("Starting Lists Test:`n`n");
        $page->output("Geordnet nach Gold:`n");

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

        $page->output($thetable->load()->getHTML(),true);

        // Case 2:
        $page->output("`nGeordnet nach HP:`n");
        $result = $qb    ->orderBy("char.healthpoints")
        ->getQuery()
        ->getResult();

        $newtab = new Table;
        //$newtab->setCSS("`~35");
        $newtab->setTabAttributes(false,2);
        $newtab->addTabHeader(array("Name","ID","Gold","LP","HP"),false,array("`b","`b`c","`b`c","`b`c","`b`c"));
        $newtab->addListArray($result);
        $page->output($newtab->load()->getHTML(),true);

        // Case 2 (alternative):
        $page->output("`nGeordnet nach HP:`n");
        $qb->orderBy("char.healthpoints");

        $newtab = new Table;
        //$newtab->setCSS("`~35");
        $newtab ->setTabAttributes(false,2)
        ->addTabHeader(array("Name","ID","Gold","LP","HP"),false,array("`b","`b`c","`b`c","`b`c","`b`c"))
        ->addListObject($qb);
        $page->output($newtab->load()->getHTML(),true);

        // Case 3:
        $page->output("`n`n");
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
        $page->output($design->load()->getHTML(),true);

        $page->output("`n`n");
        //$page->addform(true,"head","name","test.php","post");
        $design1 = new Table;
        //$design1->setCSS("`~35");
        $design1->setTabAttributes("40%",2);
        $design1->addTabSize(4,4);
        $design1->addFieldContent(1,1,"Oben links",false,"`~25",1,1);
        $design1->addFieldContent(2,1,"Oben unten links",false,false,1,1);
        $design1->addFieldContent(1,2,"Oben halb links",false,false,1,1);
        $design1->addFieldContent(1,3,"Oben halb rechts",false,false,1,1);
        $design1->addFieldContent(1,4,"Oben rechts",false,false,2,1);
        $design1->addFieldContent(2,2,/*$page->addform(false,"inputText","mitte")*/"",false,"`c",2,2);
        $design1->addFieldContent(3,1,"Unten links",false,false,2,1);
        $design1->addFieldContent(3,4,"Unten oben rechts",false,false,1,1);
        $design1->addFieldContent(4,4,"Unten rechts",false,false,1,1);
        $design1->addFieldContent(4,2,"Unten",false,false,1,2);
        $page->output($design1->load()->getHTML(),true);
        //$page->addform(true,"close");
        // Querytool & Table Test end

        // Neue Tabellenform:
        $page->output("`n`n");
        $testtable = HtmlElementManager::addTable("testtabelle", $this->getOutputObject());
        //$page->testtabelle->setCSS("`~35");
        $testtable  ->setTabAttributes("40%",2)
                    ->addTabSize(4,4)
                    ->addFieldContent(1,1,"Oben links",false,"`~25",1,1)
                    ->addFieldContent(2,1,"Oben unten links",false,false,1,1)
                    ->addFieldContent(1,2,"Oben halb links",false,false,1,1)
                    ->addFieldContent(1,3,"Oben halb rechts",false,false,1,1)
                    ->addFieldContent(1,4,"Oben rechts",false,false,2,1)
                    ->addFieldContent(2,2,/*$page->addform(false,"inputText","mitte")*/"",false,"`c",2,2)
                    ->addFieldContent(3,1,"Unten links",false,false,2,1)
                    ->addFieldContent(3,4,"Unten oben rechts",false,false,1,1)
                    ->addFieldContent(4,4,"Unten rechts",false,false,1,1)
                    ->addFieldContent(4,2,"Unten",false,false,1,2)
                    ->load();

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Link-Class + Rights-Class Test start
        $position = "link&rightstest";
        $page->output("Starting Links+Rights Test:`n");

        $user = $em->find("Main:User",1);
        $user->login();

        // Adding Group
        RightsManager::createGroup("TempGroup");

        // Adding User to Group
        RightsManager::addToGroup("TempGroup", $user->character);

        $page->output("Is Character {$user->getCharacter()->name} in Group TempGroup?`n");
        if (RightsManager::isInGroup("TempGroup", $user->character)) {
            $page->output("Yes!`n");
        } else {
            $page->output("No!`n");
        }

        foreach($user->getCharacter()->groups as $group) {
            $page->output("Character ".$user->getCharacter()->name." is in Group:" . $group->name . "`n");
        }

        // Removing User from Group
        RightsManager::removeFromGroup("TempGroup", $user->character);

        // Remove Group
        RightsManager::removeGroup("TempGroup", $user->character);

        // Link-Class + Rights-Class Test end

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Navigation Test start
        $position = "navtest";
        $page->output("Starting Navigation Test:`n");

        $page->output("see left side ;)");

        $page->getNavigation()
             ->addHead("Home")
             ->addLink("Login Page", "Page/Common/Login")
             ->addLink("Testpage", "Page/Developer/Test");

        $page->getNavigation("shared")
             ->addLink("Home", "Page/Developer/Test")
             ->addLink("Logout", "Page/Common/Logout");

        // Navigation Test end

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Money Test start
        $position = "moneytest";
        $page->output("Starting Money+Manager-Module Test:`n");

        $user = $em->find("Main:User",1);

        $page->output("`nStarting Money from {$user->getCharacter()->displayname}:`n");

        $page->output("Gold: ". $user->getCharacter()->money->getCurrency("gold") ."`n");
        $page->output("Silver: ". $user->getCharacter()->money->getCurrency("silver") ."`n");
        $page->output("Copper: ". $user->getCharacter()->money->getCurrency("copper") ."`n");

        $page->output("`nReceive 5 Gold from dead uncle:`n");
        if (!$user->getCharacter()->money->receive(5, "gold")) {
            $page->output("Transaction failed!`n");
        }

        $page->output("Gold: ". $user->getCharacter()->money->getCurrency("gold") ."`n");
        $page->output("Silver: ". $user->getCharacter()->money->getCurrency("silver") ."`n");
        $page->output("Copper: ". $user->getCharacter()->money->getCurrency("copper") ."`n");

        $page->output("`nPay 20 Silver for Ale:`n");
        if (!$user->getCharacter()->money->pay(20, "silver")) {
            $page->output("Transaction failed!`n");
        }

        $page->output("Gold: ". $user->getCharacter()->money->getCurrency("gold") ."`n");
        $page->output("Silver: ". $user->getCharacter()->money->getCurrency("silver") ."`n");
        $page->output("Copper: ". $user->getCharacter()->money->getCurrency("copper") ."`n");

        $page->output("`nTry to pay 100 Gold to Mafia via Check:`n");
        if (!$user->getCharacter()->money->pay(100, "gold")) {
            $page->output("Transaction failed! Oh Oh, Mafia is coming!`n");
        } else {
            $page->output("Transaction OK! The Mafia is pleased!`n");
        }

        $page->output("Gold: ". $user->getCharacter()->money->getCurrency("gold") ."`n");
        $page->output("Silver: ". $user->getCharacter()->money->getCurrency("silver") ."`n");
        $page->output("Copper: ". $user->getCharacter()->money->getCurrency("copper") ."`n");

        // Money Test end

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
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
        $page->output($newstab->load()->getHTML(),true);
        /*
         $news = new News;
        $news->setNewsAttributes(3,"80%","`~35","`~35","`~35");
        $page->output($news->load(),true);
        */
        // News Test End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Form Test Start

        $position = "formtest";

        $page->output("Formulartest!`n1.Formular:`nName");
        $testform = HtmlElementManager::addForm("testformular", $this->getOutputObject());
        $testform->head("form", "Page/Developer/Test")
                 ->selectStart("selectform")
                 ->selectOption("Jack")
                 ->selectOption("Jim", false, true)
                 ->selectOption("Johnny")
                 ->selectEnd();
        $page->output("`nBegründung`n");
        $testform->textArea("textareaform","Hier kannst du deine Auswahl begründen!");
        $page->output("`n");
        $testform->submitButton("Absenden")->close();

        if (isset($parameters['textareaform'])) $page->output("Wenn Formular 1 ausgeführt wurde, steht im folgenden die Begründung:`n`#25".$parameters['textareaform']."`n",true);


        // Form Test End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Message Test Start
        $page->output("Starting Message Test: `n");
        $position = "messagetest";

        $user = $em->find("Main:User",1);

        $page->output("Sending Message from {$user->getCharacter()->displayname} to {$user->getCharacter()->displayname}`n");
        MessageManager::write($user->character, $user->character, "du...", "...idiota!");

        $page->output("`bInbox of {$user->getCharacter()->displayname}:`b`n");
        $messagelist = MessageManager::getInbox($user->character);
        $showlist = array();
        foreach ($messagelist as $message) {
            $showmessage = array();
            $showmessage['id']          = $message->id;
            $showmessage['sender']      = $message->sender->name;
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
        $page->output($newtab->load()->getHTML(),true);

        $page->output("Deleting last Message`n");
        $lastMessage = MessageManager::getInbox($user->character, 1, false);
        MessageManager::delete($lastMessage);

        $page->output("`bInbox of {$user->getCharacter()->displayname}:`b`n");
        $messagelist = MessageManager::getInbox($user->character);
        $messagelist = MessageManager::getInbox($user->character);
        $showlist = array();
        foreach ($messagelist as $message) {
            $showmessage = array();
            $showmessage['id']          = $message->id;
            $showmessage['sender']      = $message->sender->name;
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
        $page->output($newtab->load()->getHTML(),true);

        // Message Test End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // StackObject Test Start

        $page->output("Starting Stack Object Test: `n");
        $position = "stackobjecttest";

        $array = array(1,2,3);

        $stack = new StackObject(3);
        $stack->import($array);

        $page->output("Initialize with 1,2,3 (maxed to 3 Elements!)`n");

        foreach($stack->export() as $element) {
            $page->output($element . "`n");
        }

        $page->output("`nStep 1: Add Element '4'`n");

        $stack->add(4);

        $page->output("Stackcontent:`n");
        foreach($stack->export() as $element) {
            $page->output($element . "`n");
        }

        $page->output("`nStep 2: Remove First Element`n");

        $stack->delFirst();

        $page->output("Stackcontent:`n");
        foreach($stack->export() as $element) {
            $page->output($element . "`n");
        }

        $page->output("`nStep 3: Get Last Element`n");

        $page->output($stack->getLast() . "`n");

        $page->output("`nStep 4: Is '2' inside the Stack?`n");

        $page->output(($stack->contains(2)?"Yes":"No") . "`n");

        $page->output("`nStep 5: Is '3' inside the Stack?`n");

        $page->output(($stack->contains(3)?"Yes":"No") . "`n");

        // StackObject Test End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Dice Test Start

        $page->output("Dice Roll Test: `n");
        $position = "stackobjecttest";

        $page->output("Roll 1D4:`n");
        $page->output(Dice::rollD4() . "`n");

        $page->output("Roll 2D6:`n");
        $page->output(Dice::rollD6(2) . "`n");

        $page->output("Roll 1D20:`n");
        $page->output(Dice::rollD20() . "`n");

        $page->output("Roll 2D100:`n");
        $page->output(Dice::rollD100(2) . "`n");

        // Dice Test End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // QueryBuilder Start
        $em = Registry::getEntityManager();

        $page->output("QueryBuilder Start: `n");
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
        $page->output($table->load()->getHTML(),true);

        // QueryBuilder End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Classic Chat Start

        $page->output("Classic Chat Start: `n");
        $position = "classicchattest";

        HtmlElementManager::addChat("testchat", $this->getOutputObject())->show();

        // Classic Chat End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // OpenID Start

        $page->output("Enter your OpenID: `n");

        $openidform = HtmlElementManager::addForm("openid", $this->getOutputObject());
        $openidform ->head("login", "Page/Developer/Test/checkopenid")
                    ->inputText("openid_identifier")
                    ->submitButton("Check")
                    ->close();

        if ($parameters['op'] == "checkopenid") {
            OpenIDSystem::checkOpenID($parameters['openid_identifier'], "Page/Developer/Test/returnopenid");

            if ($error = SessionStore::get("openiderror")) {
                $page->output($error);
                SessionStore::remove("openiderror");
            }

        } elseif ($parameters['op'] == "returnopenid") {

            $result = OpenIDSystem::evalTrustResult("Page/Developer/Test/returnopenid");

            if ($error = SessionStore::get("openiderror")) {
                $page->output($error);
                SessionStore::remove("openiderror");
            } else {
                $page->output("Successfull auth for ". $result['openid']);
            }
        }

        // OpenID End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Race Module Start
        /*
         $page->output("Race Definition Start: `n");
        $position = "racedefinitiontest";

        $user = $em->find("Main:User",1);
        $user->load(1);
        $user->loadCharacter();

        $page->output("Generate Stats:`n");
        $page->output("Race: " . $user->char->race->getHumanReadable() . "`n");
        $page->output("Sex: " . $user->char->sex . "`n");
        $page->output("Height: " . $user->char->race->generateHeight() . "cm `n");
        $page->output("Weight: " . $user->char->race->generateWeight() . "kg `n");
        $page->output("Age: " . $user->char->race->generateAge() . " years old `n");
        $page->output("Max. Age " . $user->char->race->generateMaxAge() . " years `n");
        */
        // Race Module End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Item Handling Start

        $page->output("Item Handling Start: `n");
        $position = "itemhandling";

        $item = $em->getRepository("Main:Item")->findOneByClass("fish");

        if ($item) {
            $page->output("`n");
            $page->output("Item Name: " . $item->name . " (Level: " . $item->level . ")`n");
            $page->output("Item Wert: " . $item->value->getAllCurrenciesWithPic() . "`n", true);
            $page->output("`n");
        }

        $weapon = $em->getRepository("Main:Items\Weapon")->findOneByClass("weapon");

        if ($weapon) {
            $page->output("`n");
            $page->output("Item Name: " . $weapon->name . " (Level: " . $weapon->level . ")`n");
            $page->output("Waffen Schaden: " . $weapon->showDamage(false) . "`n", true);
            $page->output("Item Wert: " . $weapon->value->getAllCurrenciesWithPic(). "`n", true);
            $page->output("`n");
        }

        $armorset = $em->find("Main:ArmorSet", 1);

        if ($armorset) {
            $page->output("`n");
            $page->output("Kopf: " . $armorset->head->name . " - RK: " . $armorset->head->getArmorClass() . " (Level: " . $armorset->head->level . ")`n");
            $page->output("Wert: " . $armorset->head->value->getAllCurrenciesWithPic() . "`n", true);
            $page->output("Oberkörper: " . $armorset->chest->name . " - RK: " . $armorset->chest->getArmorClass() . " (Level: " . $armorset->chest->level . ")`n");
            $page->output("Wert: " . $armorset->chest->value->getAllCurrenciesWithPic() . "`n", true);
            $page->output("Arme: " . $armorset->arms->name . " - RK: " . $armorset->arms->getArmorClass() . " (Level: " . $armorset->arms->level . ")`n");
            $page->output("Wert: " . $armorset->arms->value->getAllCurrenciesWithPic() . "`n", true);
            $page->output("Beine: " . $armorset->legs->name . " - RK: " . $armorset->legs->getArmorClass() . " (Level: " . $armorset->legs->level . ")`n");
            $page->output("Wert: " . $armorset->legs->value->getAllCurrenciesWithPic() . "`n", true);
            $page->output("Füße: " . $armorset->feet->name . " - RK: " . $armorset->feet->getArmorClass() . " (Level: " . $armorset->feet->level . ")`n");
            $page->output("Wert: " . $armorset->feet->value->getAllCurrenciesWithPic() . "`n", true);
            $page->output("Gesamt RK: " . $armorset->getTotalArmorClass());
        }

        // Itemhandling End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Output Module Start

        // OutputModules, which are 'enabled' at the Database, are loaded automatically
        // during the $page->create() Process

        // force loading of the 'example'-Module
        //ModuleSystem::enableOutputModule($page, "example");

        //$page->output("see links and top- and bottom-note");

        // Output Module End

        // *************************************
        //$res = microtime(true) - $mt;
        //$page->output("`n"."Dauer: ".$res." seconds");
        //$page->output("`n`n*************************************`n`n");
        //$mt = microtime(true);
        // *************************************

        // page Test Start (this has to be the last test)

        $page->output("Starting Page Test: `n");
        $position = "pagetest";
        $page->output("looks like it's working`n");

        // page Test END

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************


        /* Ende Ausklammern */

        // Waypoint Traveling System Start

        $travel = new Travel;
        $page->output("Der Startpunkt ist Ironlance. `n");
        $page->output("Der Zielort ist Derashok.`n");

        $ironlance = $em->getRepository("Main:Site")->findOneByName("ironlance/citysquare");
        $dunsplee  = $em->getRepository("Main:Site")->findOneByName("dunsplee/trail");
        $derashok  = $em->getRepository("Main:Site")->findOneByName("derashok/tribalcenter");

        if ($travel->isConnected($ironlance->waypoint, $derashok->waypoint)){
            $page->output("Sind verbunden. Die Reisedauer betraegt: ");
            $page->output($travel->findWay($ironlance->waypoint, $derashok->waypoint));
            $page->output(" Sekunden.`n`n");
        }else {
            $page->output("Nope, sind nicht verbunden.`n`n");
        }

        $page->output("Der Startpunkt ist Ironlance. `n");
        $page->output("Der Zielort ist Dunsplee.`n");
        if ($travel->isConnected($ironlance->waypoint, $dunsplee->waypoint)){
            $page->output("Sind verbunden. Die Reisedauer betraegt: ");
            $page->output($travel->findWay($ironlance->waypoint, $dunsplee->waypoint));
            $page->output(" Sekunden.`n`n");
        }else {
            $page->output("Nope, sind nicht verbunden.`n`n");
        }

        // Waypoint Traveling System End

        // *************************************
        $res = microtime(true) - $mt;
        $page->output("`n"."Dauer: ".$res." seconds");
        $page->output("`n`n*************************************`n`n");
        $mt = microtime(true);
        // *************************************

        // Remove any traces of a loggedin User
        SessionStore::remove('userid');
    }
}