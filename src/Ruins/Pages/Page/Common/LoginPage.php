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
use Ruins\Main\Controller\Page;
use Ruins\Common\Interfaces\PageObjectInterface;

class LoginPage extends Page implements PageObjectInterface
{
    protected $pagetitle  = "Login Page";

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
        $em = Registry::getEntityManager();

        switch ($parameters['op']) {

            default:
            $this->output("`cWillkommen bei Ruins!`c`n");

            // Check if a logoutreason exists (show only once)
            if ($logoutreason = SessionStore::get("logoutreason")) {
                $this->output("`c`b`#25" . $logoutreason . "`b`c");
                SessionStore::remove("logoutreason");
            }

            // Check if a openiderror exists (show only once)
            if ($openiderror = SessionStore::get("openiderror")) {
                $this->output("`c`b`#25" . $openiderror . "`b`c");
                SessionStore::remove("openiderror");
            }

            $this->output("`c Gib deinen Namen und dein Passwort ein, um diese Welt zu betreten.`c`n");

            // Normal Login
            $loginform = $this->addForm("login")->head("login", "Page/Common/LoginPage/checkpw");

            $logintable = $this->addSimpleTable("logintable")->setCSS("login");

            $logintable ->startRow()
                        ->startData();
            $this->output("Benutzername: ");
            $logintable->startData();
            $loginform->setCSS("input")->inputText("username");

            $logintable->startRow()
                       ->startData();
            $this->output("Passwort: ");
            $logintable->startData();
            $loginform->setCSS("input")->inputPassword("password");

            $logintable->startRow()
                       ->startData(false, 2);
            $loginform->setCSS("button")->submitButton("Einloggen");

            $logintable->close();

            $loginform->close();

            $this->output("`n`n");


            // OpenID Login
            $openidform = $this->addForm("openid")->head("openid_login", "Page/Common/LoginPage/checkopenid");
            $openidform->setCSS("openid");

            $openidtable = $this->addSimpleTable("openidtable");
            $openidtable->setCSS("login");

            $openidtable->startRow()
                        ->startData();
            $openidform->inputText("openid_url");

            $openidform->setCSS("button")->submitButton("Einloggen");

            $openidtable->close();

            $openidform->close();

            break;

            case "checkpw":
                $this->output("`cChecking Password!`c`n");
                if ($user = $em->getRepository("Main:User")->checkPassword($_POST['username'], $_POST['password'])) {
                    $user->login();

                    $user->addDebugLog("Login via User/Pass");
                    $this->nav->redirect("Page/Common/PortalPage");
                } else {
                    SessionStore::set("logoutreason", "Username oder Passwort falsch!");
                    $this->nav->redirect("Page/Common/LoginPage");
                }
                break;


            case "checkopenid":
                $this->output("`cChecking OpenID!`c`n");
                OpenIDManager::checkOpenID($_POST['openid_url'], "Page/Common/LoginPage/checkopenid2");

                if (SessionStore::get("openiderror")) {
                    $this->nav->redirect("Page/Common/LoginPage");
                }
                break;

            case "checkopenid2":
                $this->output("`cChecking OpenID!`c`n");
                $oldlevel = error_reporting(0);
                $result = OpenIDManager::evalTrustResult("Page/Common/LoginPage/checkopenid2");
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
                        $this->nav->redirect("Page/Common/PortalPage");
                    } else {
                        // OpenID is valid, but noone entered this url to his account
                        SessionStore::set("openiderror", "OpenID ". $result->urlID ." valid, but not mapped to any User");
                        $this->nav->redirect("Page/Common/LoginPage");
                    }
                } else {
                    if (!(SessionStore::get("openiderror"))) {
                        SessionStore::set("openiderror", "Unknown OpenID Error");
                    }

                    $this->nav->redirect("Page/Common/LoginPage");
                }
                break;
        }
    }
}
