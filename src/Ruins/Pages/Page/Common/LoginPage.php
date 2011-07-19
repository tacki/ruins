<?php
/**
 * Login Page
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Ruins\Pages\Page\Common;
use Ruins\Common\Controller\SessionStore;
use Ruins\Main\Entities\DebugLogEntity;
use Ruins\Main\Controller\Link;
use Ruins\Main\Manager\OpenIDManager;
use Ruins\Common\Controller\Registry;
use Ruins\Common\Controller\AbstractPageObject;

class LoginPage extends AbstractPageObject
{
    public $title  = "Login Page";

    public function createContent($page, $parameters)
    {
        $page->getNavigation()
             ->addHead("Ruins")
             ->addLink("Testpage", "Page/Developer/Test");

        $em = $this->getEntityManager();

        switch ($parameters['op']) {

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
            $loginform = $page->addForm("login")->head("login", "Page/Common/Login/checkpw");

            $logintable = $page->addSimpleTable("logintable")->setCSS("login");

            $logintable ->startRow()
                        ->startData();
            $page->output("Benutzername: ");
            $logintable->startData();
            $loginform->setCSS("input")->inputText("username");

            $logintable->startRow()
                       ->startData();
            $page->output("Passwort: ");
            $logintable->startData();
            $loginform->setCSS("input")->inputPassword("password");

            $logintable->startRow()
                       ->startData(false, 2);
            $loginform->setCSS("button")->submitButton("Einloggen");

            $logintable->close();

            $loginform->close();

            $page->output("`n`n");


            // OpenID Login
            $openidform = $page->addForm("openid")->head("openid_login", "Page/Common/Login/checkopenid");
            $openidform->setCSS("openid");

            $openidtable = $page->addSimpleTable("openidtable");
            $openidtable->setCSS("login");

            $openidtable->startRow()
                        ->startData();
            $openidform->inputText("openid_url");

            $openidform->setCSS("button")->submitButton("Einloggen");

            $openidtable->close();

            $openidform->close();

            break;

            case "checkpw":
                $page->output("`cChecking Password!`c`n");
                if ($user = $em->getRepository("Main:User")->checkPassword($_POST['username'], $_POST['password'])) {
                    $user->login();

                    $user->addDebugLog("Login via User/Pass");
                    $page->nav->redirect("Page/Common/Portal");
                } else {
                    SessionStore::set("logoutreason", "Username oder Passwort falsch!");
                    $page->nav->redirect("Page/Common/Login");
                }
                break;


            case "checkopenid":
                $page->output("`cChecking OpenID!`c`n");
                OpenIDManager::checkOpenID($_POST['openid_url'], "Page/Common/Login/checkopenid2");

                if (SessionStore::get("openiderror")) {
                    $page->nav->redirect("Page/Common/Login");
                }
                break;

            case "checkopenid2":
                $page->output("`cChecking OpenID!`c`n");
                $oldlevel = error_reporting(0);
                $result = OpenIDManager::evalTrustResult("Page/Common/Login/checkopenid2");
                error_reporting($oldlevel);
                if (is_array($result) && $result['result'] == "ok") {
                    $em = Registry::getEntityManager();
                    $qb = $em->createQueryBuilder();

                    $result = $qb   ->select("openid")
                    ->from("Main:OpenID", "openid")
                    ->where("openid.urlID LIKE ?1")->setParameter(1, $result['openid'])
                    ->getQuery()->getOneOrNullResult();

                    if ($result) {
                        $user = $result->user;
                        $user->login();

                        $user->addDebugLog("Login via OpenID (".$result->urlID.")");
                        $page->nav->redirect("Page/Common/Portal");
                    } else {
                        // OpenID is valid, but noone entered this url to his account
                        SessionStore::set("openiderror", "OpenID ". $result->urlID ." valid, but not mapped to any User");
                        $page->nav->redirect("Page/Common/Login");
                    }
                } else {
                    if (!(SessionStore::get("openiderror"))) {
                        SessionStore::set("openiderror", "Unknown OpenID Error");
                    }

                    $page->nav->redirect("Page/Common/Login");
                }
                break;
        }
    }
}
