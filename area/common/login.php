<?php
/**
 * Login Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version SVN: $Id: login.php 326 2011-04-19 20:19:34Z tacki $
 * @package Ruins
 */

/**
 * Namespaces
 */
use Common\Controller\SessionStore,
    Main\Entities\DebugLogEntity,
    Main\Controller\Link,
    Main\Manager;

/**
 * Page Content
 */
$page->set("pagetitle", "Login Page");
$page->set("headtitle", "Login Page");


$page->nav->add(new Link("Ruins"));
$page->nav->add(new Link("Testpage", "page=developer/test"));

switch ($_GET['op']) {

    default:
        $page->output("`cWillkommen bei Ruins!`c`n");

        // Check if a logoutreason exists (show only once)
        if ($logoutreason = SessionStore::get("logoutreason")) {
            $page->output("`c`b`#25" . $logoutreason . "`b`c");
            SessionStore::remove("logoutreason");
        }

        // Check if a openiderror exists (show only once)
        if ($openiderror = SessionStore::get("openiderror")) {
            $page->output("`c`b`#25" . $openiderror . "`b`c");
            SessionStore::remove("openiderror");
        }

        $page->output("`c Gib deinen Namen und dein Passwort ein, um diese Welt zu betreten.`c`n");

        // Normal Login
        $page->addForm("loginform");
        $page->loginform->head("login", "page=common/login&op=checkpw");

        $page->addSimpleTable("logintable");
        $page->logintable->setCSS("login");

        $page->logintable->startRow();
        $page->logintable->startData();
        $page->output("Benutzername: ");
        $page->logintable->startData();
        $page->loginform->setCSS("input");
        $page->loginform->inputText("username");

        $page->logintable->startRow();
        $page->logintable->startData();
        $page->output("Passwort: ");
        $page->logintable->startData();
        $page->loginform->setCSS("input");
        $page->loginform->inputPassword("password");

        $page->logintable->startRow();
        $page->logintable->startData(false, 2);
        $page->loginform->setCSS("button");
        $page->loginform->submitButton("Einloggen");

        $page->logintable->close();

        $page->loginform->close();

        $page->output("`n`n");


        // OpenID Login
        $page->addForm("openidform");
        $page->openidform->head("openid_login", "page=common/login&op=checkopenid");
        $page->openidform->setCSS("openid");

        $page->addSimpleTable("openidtable");
        $page->openidtable->setCSS("login");

        $page->openidtable->startRow();
        $page->openidtable->startData();
        $page->openidform->inputText("openid_url");

        $page->openidform->setCSS("button");
        $page->openidform->submitButton("Einloggen");

        $page->openidtable->close();

        $page->openidform->close();

        break;

    case "checkpw":
        $page->output("`cChecking Password!`c`n");
        if ($userid = Manager\User::checkPassword($_POST['username'], $_POST['password'])) {
            $user = $em->find("Main:User",$userid);
            $user->login();

            $user->addDebugLog("Login via User/Pass");
            $page->nav->redirect("page=common/portal");
        } else {
            SessionStore::set("logoutreason", "Username oder Passwort falsch!");
            $page->nav->redirect("page=common/login");
        }
        break;


    case "checkopenid":
        $page->output("`cChecking OpenID!`c`n");
        Manager\OpenID::checkOpenID($_POST['openid_url'], "page=common/login&op=checkopenid2");

        if (SessionStore::get("openiderror")) {
            $page->nav->redirect("page=common/login");
        }
        break;

    case "checkopenid2":
        $page->output("`cChecking OpenID!`c`n");
        $oldlevel = error_reporting(0);
        $result = Manager\OpenID::evalTrustResult("page=common/login&op=checkopenid2");
        error_reporting($oldlevel);
        if (is_array($result) && $result['result'] == "ok") {
            $qb = getQueryBuilder();

            $result = $qb   ->select("openid")
                            ->from("Main:OpenID", "openid")
                            ->where("openid.urlID LIKE ?1")->setParameter(1, $result['openid'])
                            ->getQuery()->getOneOrNullResult();

            if ($result) {
                $user = $result->user;
                $user->login();

                $user->addDebugLog("Login via OpenID (".$result->urlID.")");
                $page->nav->redirect("page=common/portal");
            } else {
                // OpenID is valid, but noone entered this url to his account
                SessionStore::set("openiderror", "OpenID ". $result->urlID ." valid, but not mapped to any User");
                $page->nav->redirect("page=common/login");
            }
        } else {
            if (!(SessionStore::get("openiderror"))) {
                SessionStore::set("openiderror", "Unknown OpenID Error");
            }

            $page->nav->redirect("page=common/login");
        }
        break;
}

?>
