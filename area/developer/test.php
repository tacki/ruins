<?php
/**
 * Testpage
 *
 * Simple Testpage of 'Ruins'
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: test.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Global Includes
 */
require_once("config/dirconf.cfg.php");
require_once(DIR_INCLUDES."includes.inc.php");

// CLEAR CACHE
SessionStore::pruneCache();

$page->set("headtitle", "Ruins Testpage");
$page->set("pagetitle", "This is the Ruins Testpage");

$mt = getMicroTime();

// Config-Test START
$page->output("Starting Config Test: `n");
$position = "configtest";

$config = new Config;

$config->set("test", "123");
$config->set("test", "321");
if ($config->get("test") == "321") {
    $page->output("Configtest successful `n");
}

// Config-Test END

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

/* Start ausklammern */


// User-Test START
$page->output("Starting User Test: `n");
$position = "usertest";

$user = new User(1);

if ($user->login == "anonymous") {
    $page->output("Usertest successful `n");
}

// User-Test END

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Global Timer-Test START
$page->output("Starting Global Timer Test: `n");
$position = "globaltimertest";

$timer = new Timer("1hourtimertest");

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
    $page->output("1 Hour Timer: ". $timer->get(), true);
} else {
    $timer->set(0, 0, 1);
    $page->output("1 Hour Timer: ". $timer->get(), true);
}

$tempform = new Form($page);
if ($_GET['op'] == "stoptimer") {
    $tempform->head("", $page->url->base."&op=starttimer");
    $tempform->submitButton("Start");
} else {
    $tempform->head("", $page->url->base."&op=stoptimer");
    $tempform->submitButton("Stop");
}
$tempform->close();

unset ($timer);

// Global Timer-Test END

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
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

$page->output("Der gleiche Text nach einem purge: `n". btcode::purgeTags($textsample) . "`n");

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
$page->addCommonCSS("autocomplete.css");
$page->addJavaScriptFile("autocomplete.func.js");
$page->addJavaScriptFile("jquery.plugin.dimensions.js");

$page->addJavaScript("
    $(function() {
        setAutoComplete('searchField', 'results', '" . htmlpath(DIR_INCLUDES."helpers/ajax/autocomplete_charname.ajax.php?part=") ."');
    });
");
// btcode Test END

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// QueryBuilder && Table Test Start
$position = "tabletest";

$page->output("Starting Lists Test:`n`n");
$page->output("Geordnet nach Gold:`n");

$qb = getQueryBuilder();

$result = $qb   ->select("char.displayname, char.id, char.money, char.lifepoints, char.healthpoints")
                ->from("Entities\Character", "char")
                ->where("char.money >= 0")
                ->orderBy("char.money", "DESC")
                ->addorderBy("char.lifepoints", "ASC")
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
$page->output($thetable->load(),true);

$page->output("`nGeordnet nach HP:`n");
$result = $qb	->orderBy("char.healthpoints")
                ->getQuery()
                ->getResult();

$newtab = new Table;
//$newtab->setCSS("`~35");
$newtab->setTabAttributes(false,2);
$newtab->addTabHeader(array("Name","ID","Gold","LP","HP"),false,array("`b","`b`c","`b`c","`b`c","`b`c"));
$newtab->addListArray($result);
$page->output($newtab->load(),true);

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
$page->output($design->load(),true);

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
$page->output($design1->load(),true);
//$page->addform(true,"close");
// Querytool & Table Test end

// Neue Tabellenform:
$page->output("`n`n");
$page->addTable("testtabelle");
//$page->testtabelle->setCSS("`~35");
$page->testtabelle->setTabAttributes("40%",2);
$page->testtabelle->addTabSize(4,4);
$page->testtabelle->addFieldContent(1,1,"Oben links",false,"`~25",1,1);
$page->testtabelle->addFieldContent(2,1,"Oben unten links",false,false,1,1);
$page->testtabelle->addFieldContent(1,2,"Oben halb links",false,false,1,1);
$page->testtabelle->addFieldContent(1,3,"Oben halb rechts",false,false,1,1);
$page->testtabelle->addFieldContent(1,4,"Oben rechts",false,false,2,1);
$page->testtabelle->addFieldContent(2,2,/*$page->addform(false,"inputText","mitte")*/"",false,"`c",2,2);
$page->testtabelle->addFieldContent(3,1,"Unten links",false,false,2,1);
$page->testtabelle->addFieldContent(3,4,"Unten oben rechts",false,false,1,1);
$page->testtabelle->addFieldContent(4,4,"Unten rechts",false,false,1,1);
$page->testtabelle->addFieldContent(4,2,"Unten",false,false,1,2);
$page->testtabelle->load();

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Link-Class + Rights-Class Test start
$position = "link&rightstest";
$page->output("Starting Links+Rights Test:`n");

/*
$char = new GPCharacter;
$char->load(1);

$char->rightgroups->add("Developers");
$char->rightgroups->add("Administrators;Gamemasters");
$char->rightgroups->add( array("Gameops") );

$link1 = new Link("Admingrotte", "admin.php", "main", "", "Administrators");
$link2 = new Link("Gameopgebiet", "gameop.php", "main", "","Gameops");
$link3 = new Link("Forum", "forum.php");

if ($link1->isAllowedBy($char->rightgroups) && $link2->isAllowedBy($char->rightgroups) && $link3->isAllowedBy($char->rightgroups)) {
    $page->output("Test1 successful...`n");
} else {
    $page->output("Test1 failed!`n");
}

$char->rightgroups->remove("Administrators");
$char->rightgroups->remove("Gamemasters");
$char->rightgroups->remove("Developers");

if (!$link1->isAllowedBy($char->rightgroups) && $link2->isAllowedBy($char->rightgroups) && $link3->isAllowedBy($char->rightgroups)) {
    $page->output("Test2 successful...`n");
} else {
    $page->output("Test2 failed!`n");
}

$char->rightgroups->set(array());

if (!$link1->isAllowedBy($char->rightgroups) && !$link2->isAllowedBy($char->rightgroups) && $link3->isAllowedBy($char->rightgroups)) {
    $page->output("Test3 successful...");
} else {
    $page->output("Test3 failed!`n");
}

unset($char);
unset($link1, $link2, $link3);
*/
// Link-Class + Rights-Class Test end

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Navigation Test start
$position = "navtest";
$page->output("Starting Navigation Test:`n");

$page->output("see left side ;)");

$page->nav->disableValidation();
$page->nav->add(new Link("Home"));
$page->nav->add(new Link("Login Page", "page=common/login","main"));
$page->nav->add(new Link("Testpage", "page=developer/test","main"));
$page->nav->add(new Link("Dorf"));
$page->nav->add(new Link("Ausgang", "page=common/logout","main"));
$page->nav->add(new Link("Home", "page=developer/test","shared"));
$page->nav->add(new Link("Logout", "page=common/logout","shared"));

$page->nav->save();

// Navigation Test end

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Money Test start
$position = "moneytest";
$page->output("Starting Money+Manager-Module Test:`n");
/*
$char = new GPCharacter;
$char->load(1);

$page->output("`nStarting Money from $char->displayname:`n");

$page->output("Gold: ". $char->money->detailed("gold") ."`n");
$page->output("Silver: ". $char->money->detailed("silver") ."`n");
$page->output("Copper: ". $char->money->detailed("copper") ."`n");

$page->output("`nReceive 5 Gold from dead uncle:`n");
if (!$char->money->receive(5, "gold")) {
    $page->output("Transaction failed!`n");
}

$page->output("Gold: ". $char->money->detailed("gold") ."`n");
$page->output("Silver: ". $char->money->detailed("silver") ."`n");
$page->output("Copper: ". $char->money->detailed("copper") ."`n");

$page->output("`nPay 20 Silver for Ale:`n");
if (!$char->money->pay(20, "silver")) {
    $page->output("Transaction failed!`n");
}

$page->output("Gold: ". $char->money->detailed("gold") ."`n");
$page->output("Silver: ". $char->money->detailed("silver") ."`n");
$page->output("Copper: ". $char->money->detailed("copper") ."`n");

$page->output("`nTry to pay 100 Gold to Mafia via Check:`n");
if (!$char->money->pay(100, "gold")) {
    $page->output("Transaction failed! Oh Oh, Mafia is coming!`n");
} else {
    $page->output("Transaction OK! The Mafia is pleased!`n");
}

$page->output("Gold: ". $char->money->detailed("gold") ."`n");
$page->output("Silver: ". $char->money->detailed("silver") ."`n");
$page->output("Copper: ". $char->money->detailed("copper") ."`n");

$char->save();
*/
// Money Test end

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// News Test Start

$position = "newstest";

$newstab = new Table;
//$newtab->setCSS("`~35");
$newstab->setTabAttributes(false,2);
$newstab->addTabHeader(array("ID","Datum","Author","Titel","HP", "Inhalt", "Ort"));

if (!($newslist = EnvironmentSystem::getNews())) {
    EnvironmentSystem::addNews("Skandal!", "Heute wieder kein Weltuntergang");
} else {
    $newstab->addListArray($newslist);
}
$page->output($newstab->load(),true);
/*
$news = new News;
$news->setNewsAttributes(3,"80%","`~35","`~35","`~35");
$page->output($news->load(),true);
*/
// News Test End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Form Test Start

$position = "formtest";

/*
$page->output("Formulartest!`n1.Formular:`nName");
$page->addform(true,"head","form","test.php","post");
$page->addform(true,"selectStart","selectform",false,1);
$page->addform(true,"selectOption","Jack",false);
$page->addform(true,"selectOption","Jim",false,false,true);
$page->addform(true,"selectOption","Johnny",false);
$page->addform(true,"selectEnd");
$page->output("`nBegründung`n");
$page->addform(true,"textArea","textareaform","Hier kannst du deine Auswahl begründen!");
$page->output("`n");
$page->addform(true,"submitButton","subbiname","Absenden");
$page->addform(true,"close");
if ($_POST['textareaform']!="") $page->output("Wenn Formular 1 ausgeführt wurde, steht im folgenden die Begründung:`n`25".$_POST['textareaform']."`n",true);
$page->output("`n2.Formular:`nName");
$page->addform(true,"head","form","test.php","post");
$page->addform(true,"selectStart","selectform",false,1);
$page->addform(true,"selectOption","DF",false);
$page->addform(true,"selectOption","Ruins",false,false,true);
$page->addform(true,"selectOption","SN",false);
$page->addform(true,"selectEnd");
$page->output("`nBegründung?`n");
$page->addform(true,"textArea","textareaform2","Auch hier kannst du deine Auswahl begründen!");
$page->output("`n");
$page->addform(true,"submitButton","submitname","Absenden");
$page->addform(true,"resetButton","resetname","Abbrechen");
    $page->addform(true,"close");
if (!empty($_POST['textareaform2'])) $page->output("Wenn Formular 2 ausgeführt wurde, steht im folgenden die Begründung:`n`25".$_POST['textareaform2']."`n",true);
*/
/*	// lange form
$page->output("Formulartest!`n1.Formular:`nName");
$formular = $page->addForm();
$formular->head("form", "test.php", "post");
$formular->selectStart("selectform", 1);
$formular->selectOption("Jack", false);
$formular->selectOption("Jim", false, false, true);
$formular->selectOption("Johnny", false);
$formular->selectEnd();
$page->output("`nBegründung`n");
$formular->textArea("textareaform","Hier kannst du deine Auswahl begründen!", 50, 10);
$page->output("`n");
$formular->submitButton("Absenden");
$formular->close();
if ($_POST['textareaform']!="") $page->output("Wenn Formular 1 ausgeführt wurde, steht im folgenden die Begründung:`n`25".$_POST['textareaform']."`n",true);

// kurze form
$page->output("Formulartest!`n1.Formular:`nName");
$formular = $page->addForm();
$formular->head("form", "test.php");
$formular->selectStart("selectform");
$formular->selectOption("Jack");
$formular->selectOption("Jim", false, false, true);
$formular->selectOption("Johnny");
$formular->selectEnd();
$page->output("`nBegründung`n");
$formular->textArea("textareaform","Hier kannst du deine Auswahl begründen!");
$page->output("`n");
$formular->submitButton();
$formular->close();
if ($_POST['textareaform']!="") $page->output("Wenn Formular 1 ausgeführt wurde, steht im folgenden die Begründung:`n`25".$_POST['textareaform']."`n",true);
*/

$page->output("Formulartest!`n1.Formular:`nName");
$page->addForm("testformular");
$page->testformular->head("form", "test.php");
$page->testformular->selectStart("selectform");
$page->testformular->selectOption("Jack");
$page->testformular->selectOption("Jim", false, true);
$page->testformular->selectOption("Johnny");
$page->testformular->selectEnd();
$page->output("`nBegründung`n");
$page->testformular->textArea("textareaform","Hier kannst du deine Auswahl begründen!");
$page->output("`n");
$page->testformular->submitButton("Absenden");
$page->testformular->close();

if (isset($_POST['textareaform'])) $page->output("Wenn Formular 1 ausgeführt wurde, steht im folgenden die Begründung:`n`#25".$_POST['textareaform']."`n",true);


// Form Test End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Message Test Start
$page->output("Starting Message Test: `n");
$position = "messagetest";
/*
$character = new RPGCharacter;

$page->output("Sending Message from anonymous(1) to anonymous(1)`n");
$messageid1 = MessageSystem::write(1, 1, "du...", "...idiota!");

//$page->output("Sending Message from anonymous(1) to all Users`n");
//$messageid2 = MessageSystem::write(1, "all", "global warning", "you all suck!");

$page->output("`bInbox of anonymous(1):`b`n");
$character->load(1);
$messagelist = MessageSystem::getInbox($character);
$newtab = new Table;
$newtab->setCSS("`~35");
$newtab->setTabAttributes(false,2);
$newtab->addTabHeader(array("id","sender","receiver","subject","text","date","status"),false,array("`b","`b`c","`b`c","`b`c","`b`c","`b`c","`b`c","`b`c"));
$newtab->addListArray($messagelist);
$page->output($newtab->load(),true);

$page->output("Deleting Message $messageid1`n");
MessageSystem::delete($messageid1);
//$page->output("Deleting Message $messageid2`n");
//MessageSystem::delete($messageid2);

$page->output("`bInbox of anonymous(1):`b`n");
$character->load(1);
$messagelist = MessageSystem::getInbox($character);
$newtab = new Table;
$newtab->setCSS("`~35");
$newtab->setTabAttributes(false,2);
$newtab->addTabHeader(array("id","sender","receiver","subject","text","date","status"),false,array("`b","`b`c","`b`c","`b`c","`b`c","`b`c","`b`c","`b`c"));
$newtab->addListArray($messagelist);
$page->output($newtab->load(),true);
*/
// Message Test End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
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
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// UniqueID Test Start

$page->output("Starting UniqueID Test: `n");
$position = "uniqueidtest";

$uniqueIDs = new UniqueIDStack(3);

$page->output("Start with empty Stack`n");

for ($i=1; $i<=3; $i++) {

    $uniqueid = md5(getMicroTime());
    $page->output("`nStep $i: Add UniqueID '" . $uniqueid . "'`n");
    $uniqueIDs->add($uniqueid);

    $page->output("Stackcontent:`n");
    foreach($uniqueIDs->export() as $uniqueid) {
        $page->output("Date UniqueID added: " . date("Y-m-d H:i:s", $uniqueid['date']) . "`n");
        $page->output("UniqueID: " . $uniqueid['data'] . "`n");
    }
}

$page->output("`nStep 4: Get Last UniqueID`n");
$page->output($uniqueIDs->getLast("data") . "`n");

$page->output("`nStep 5: Get First UniqueID`n");
$page->output($uniqueIDs->getFirst("data") . "`n");

$page->output("`nStep 6: Get Next UniqueID`n");
$page->output($uniqueIDs->getNext("data") . "`n");

$page->output("`nStep 6: Get Previous UniqueID`n");
$page->output($uniqueIDs->getPrev("data") . "`n");

$page->output("`nStep 7: Remove Last UniqueID`n");
$uniqueIDs->delLast();

$page->output("Stackcontent:`n");
foreach($uniqueIDs->export() as $uniqueid) {
    $page->output("Date UniqueID added: " . date("Y-m-d H:i:s", $uniqueid['date']) . "`n");
    $page->output("UniqueID: " . $uniqueid['data'] . "`n");
}

// UniqueID Test End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
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
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// QueryTool Start
/*
$page->output("QueryTool Start: `n");
$position = "QueryTooltest";

$dbqt = new QueryTool();

$result = $dbqt	->select("id, displayname")
                ->from("characters")
                ->where("id > 0")
                ->order("id")
                ->exec()
                ->fetchAll();

$table = new Table();
//$table->setCSS("`~35");
$table->setTabAttributes(false,2);
$table->addTabHeader(array("id","displayname"),false,array("`b","`b`c"));
$table->addListArray($result);
$page->output($table->load(),true);
*/
// QueryTool End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Classic Chat Start

$page->output("Classic Chat Start: `n");
$position = "classicchattest";

$page->addChat("testchat");
$page->testchat->show();

// Classic Chat End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// OpenID Start

$page->output("Enter your OpenID: `n");

$page->addForm("openidform");
$page->openidform->head("login", "page=developer/test&op=checkopenid");
$page->openidform->inputText("openid_identifier");
$page->openidform->submitButton("Check");

if ($_GET['op'] == "checkopenid") {
    OpenIDSystem::checkOpenID($_POST['openid_identifier'], "page=developer/test&op=returnopenid");

    if ($error = SessionStore::get("openiderror")) {
        $page->output($error);
        SessionStore::remove("openiderror");
    }

} elseif ($_GET['op'] == "returnopenid") {

    $result = OpenIDSystem::evalTrustResult("page=developer/test&op=returnopenid");

    if ($error = SessionStore::get("openiderror")) {
        $page->output($error);
        SessionStore::remove("openiderror");
    } else {
        $page->output("Successfull auth for ". $result['openid']);
    }
}

// OpenID End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Race Module Start
/*
$page->output("Race Definition Start: `n");
$position = "racedefinitiontest";

$user = new User();
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
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Item Handling Start

$page->output("Item Handling Start: `n");
$position = "itemhandling";
/*
$item = new Item;
$item->load(2);

$page->output("`n");
$page->output("Item Name: " . $item->name . " (Level: " . $item->level . ")`n");
$page->output("Item Wert: " . $item->value->fullDetailedWithPic() . "`n", true);
$page->output("`n");

$item->unload();

$weapon = new Weapon;
$weapon->load(1);

$page->output("`n");
$page->output("Item Name: " . $weapon->name . " (Level: " . $weapon->level . ")`n");
$page->output("Waffen Schaden: " . $weapon->showDamage(false) . "`n", true);
$page->output("Item Wert: " . $weapon->value->fullDetailedWithPic() . "`n", true);
$page->output("`n");

$weapon->unload();

$weapon = new Weapon;
$weapon->load(2);

$page->output("`n");
$page->output("Item Name: " . $weapon->name . " (Level: " . $weapon->level . ")`n");
$page->output("Waffen Schaden: " . $weapon->showDamage(false) . "`n", true);
$page->output("Item Wert: " . $weapon->value->fullDetailedWithPic() . "`n", true);

$weapon->unload();

$armorset = new Armorset;
$armorset->load(1);
$page->output("`n");
$page->output("Kopf: " . $armorset->head->name . " - RK: " . $armorset->head->getArmorClass() . " (Level: " . $armorset->head->level . ")`n");
$page->output("Wert: " . $armorset->head->value->fullDetailedWithPic() . "`n", true);
$page->output("Oberkörper: " . $armorset->chest->name . " - RK: " . $armorset->chest->getArmorClass() . " (Level: " . $armorset->chest->level . ")`n");
$page->output("Wert: " . $armorset->chest->value->fullDetailedWithPic() . "`n", true);
$page->output("Arme: " . $armorset->arms->name . " - RK: " . $armorset->arms->getArmorClass() . " (Level: " . $armorset->arms->level . ")`n");
$page->output("Wert: " . $armorset->arms->value->fullDetailedWithPic() . "`n", true);
$page->output("Beine: " . $armorset->legs->name . " - RK: " . $armorset->legs->getArmorClass() . " (Level: " . $armorset->legs->level . ")`n");
$page->output("Wert: " . $armorset->legs->value->fullDetailedWithPic() . "`n", true);
$page->output("Füße: " . $armorset->feet->name . " - RK: " . $armorset->feet->getArmorClass() . " (Level: " . $armorset->feet->level . ")`n");
$page->output("Wert: " . $armorset->feet->value->fullDetailedWithPic() . "`n", true);
$page->output("Gesamt RK: " . $armorset->getTotalArmorClass());
$armorset->unload();
*/
// Itemhandling End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

// Output Module Start

// OutputModules, which are 'enabled' at the Database, are loaded automatically
// during the $page->create() Process

// force loading of the 'example'-Module
//ModuleSystem::enableOutputModule($page, "example");

//$page->output("see links and top- and bottom-note");

// Output Module End

// *************************************
//$res = getMicroTime() - $mt;
//$page->output("`n"."Dauer: ".$res." seconds");
//$page->output("`n`n*************************************`n`n");
//$mt = getMicroTime();
// *************************************

// page Test Start (this has to be the last test)

$page->output("Starting Page Test: `n");
$position = "pagetest";
$page->output("looks like it's working`n");

// page Test END

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************


/* Ende Ausklammern */

// Waypoint Traveling System Start
/*
$travel = new traveling;
$travel->load();
$page->output("Der Startpunkt ist Ironlance. `n");
$page->output("Der Zielort ist Derashok.`n");
//$page->output("<label for='searchField'>Wohin soll die Reise gehen?: </label>", true);
//$page->output("<input type='text' id='searchField' name='searchField'>", true);
//$page->addCommonCSS("autocomplete.css");
//$page->addJavaScriptFile("autocomplete.func.js");
//$page->addJavaScriptFile("jquery.plugin.dimensions.js");
//
//$page->addJavaScript("
//	$(function() {
//		setAutoComplete('searchField', 'results', '" . htmlpath(DIR_INCLUDES."helpers/ajax/autocomplete_locationname.ajax.php?part=") ."');
//	});
//");

if ($travel->check_target(2,7)){
    $page->output("Sind verbunden. Die Reisedauer betraegt: ");
    $page->output($travel->find_way(2,7));
    $page->output(" Sekunden.`n`n");
}else {
    $page->output("Nope, sind nicht verbunden.`n`n");
}

$page->output("Der Startpunkt ist Ironlance. `n");
$page->output("Der Zielort ist Dunsplee.`n");
if ($travel->check_target(3,3)){
    $page->output("Sind verbunden.`n`n");
}else {
    $page->output("Nope, sind nicht verbunden.`n`n");
}
*/
// Waypoint Traveling System End

// *************************************
$res = getMicroTime() - $mt;
$page->output("`n"."Dauer: ".$res." seconds");
$page->output("`n`n*************************************`n`n");
$mt = getMicroTime();
// *************************************

?>
