<?php
/**
 * OpenID System Class
 *
 * Class to handle OpenID-Checks etc.
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2008 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Main\Manager;
use Common\Controller\SessionStore,
    Auth_OpenID_FileStore,
    Auth_OpenID_Consumer,
    Auth_OpenID_SRegRequest,
    Auth_OpenID_SRegResponse,
    Auth_OpenID_PAPE_Request,
    Auth_OpenID;

/**
 * Class Defines
 */
define("OPENID_STORAGE_DIR",	DIR_TEMP."openid");

/**
 * OpenID System Class
 *
 * Class to handle OpenID-Checks etc.
 * @package Ruins
 */
class OpenID
{
    /**
     * Get Protocol (HTTP/HTTPS)
     * @return string http or https
     */
    private function _getScheme() {
        $scheme = 'http';
        if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
            $scheme .= 's';
        }
        return $scheme;
    }

    /**
     * Generate Return URL
     * @param string $returnurl
     * @return string Complete Return URL
     */
    private function _getReturnTo($returnurl) {
        return sprintf("%s://%s:%s%s/?".$returnurl,
                       self::_getScheme(), $_SERVER['SERVER_NAME'],
                       $_SERVER['SERVER_PORT'],
                       dirname($_SERVER['PHP_SELF']));
    }

    /**
     * Generate Root of OpenID-Trustserver
     * @return string Root of the OpenID-Trustserver
     */
    private function _getTrustRoot() {
        return sprintf("%s://%s:%s%s/",
                       self::_getScheme(), $_SERVER['SERVER_NAME'],
                       $_SERVER['SERVER_PORT'],
                       dirname($_SERVER['PHP_SELF']));
    }

    /**
     * Initialize Filestorage
     *
     * FileStorage is the only Storage supported
     * @return Auth_OpenID_FileStore
     */
    private function _initStore() {
        return new Auth_OpenID_FileStore(OPENID_STORAGE_DIR);
    }

    /**
     * Initialize Consumer
     * @return Auth_OpenID_Consumer
     */
    private function _initConsumer() {
        return new Auth_OpenID_Consumer(self::_initStore());
    }

    /**
     * Set ErrorMessage for later use
     * @param string $error ErrorMessage
     */
    private function _throwError($error) {
        SessionStore::set("openiderror", "OpenID Error: " . $error);
    }

    /**
     * Check given OpenID with the Trustserver
     * @param string $openid The OpenID-URL to check
     * @param string $returnpage The URL that expects the result
     * @return bool false if something went wrong (see _throwError())
     */
    public function checkOpenID($openid, $returnpage) {
        $consumer = self::_initConsumer();

        $auth_request = $consumer->begin($openid);

        // No auth request means we can't begin OpenID.
        if (!$auth_request) {
            self::_throwError("Not a valid OpenID or cannot connect to the OpenID-Sever.");
            return false;
        }

        $sreg_request = Auth_OpenID_SRegRequest::build(
                                                        // Required
                                                        array('nickname'),
                                                        // Optional
                                                        array('fullname', 'email') );

        if ($sreg_request) {
            $auth_request->addExtension($sreg_request);
        }

        $policy_uris = null;
        if (isset($_GET['policies'])) {
             $policy_uris = $_GET['policies'];
        }

        $pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
        if ($pape_request) {
            $auth_request->addExtension($pape_request);
        }

        // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
        // form to send a POST request to the server.
        if ($auth_request->shouldSendRedirect()) {
            $redirect_url = $auth_request->redirectURL(self::_getTrustRoot(),
                                                       self::_getReturnTo($returnpage));

            // If the redirect URL can't be built, display an error
            // message.
            if (Auth_OpenID::isFailure($redirect_url)) {
                self::_throwError("Could not redirect to server: " . $redirect_url->message);
                return false;
            } else {
                // Send redirect.
                header("Location: ".$redirect_url);
            }
        } else {
            // Generate form markup and render it.
            $form_id = 'openid_message';
            $form_html = $auth_request->htmlMarkup(self::_getTrustRoot(),
                                                   self::_getReturnTo($returnpage),
                                                   false,
                                                   array('id' => $form_id));

            // Display an error if the form markup couldn't be generated;
            // otherwise, render the HTML.
            if (Auth_OpenID::isFailure($form_html)) {
                self::_throwError("Could not redirect to server: " . $redirect_url->message);
                return false;
            } else {
                print $form_html;
            }
        }

    }

    /**
     * Evaluate the Result from the Trust Server
     * @param $returnpage The Page which received the Result
     * @return array/bool Array of Values if successfull, else false
     */
    public function evalTrustResult($returnpage) {
        $consumer = self::_initConsumer();

        // Complete the authentication process using the server's
        // response.
        $return_to = self::_getReturnTo($returnpage);
        $response = $consumer->complete($return_to);

        // Check the response status.
        if ($response->status == Auth_OpenID_CANCEL) {
            // This means the authentication was cancelled.
            self::_throwError("Verification cancelled.");
            return false;
        } else if ($response->status == Auth_OpenID_FAILURE) {
            // Authentication failed; display the error message.
            self::_throwError("Authentication failed: " . $response->message);
            return false;
        } else if ($response->status == Auth_OpenID_SUCCESS) {
            // This means the authentication succeeded; extract the
            // identity URL and Simple Registration data (if it was
            // returned).
            $successresult = array();
            $successresult['result'] = "ok";
            $successresult['openid'] = htmlentities($response->getDisplayIdentifier());

            if ($response->endpoint->canonicalID) {
                $escaped_canonicalID = htmlentities($response->endpoint->canonicalID);
                $successresult['XRI CanonicalID'] = $escaped_canonicalID;
            }

            $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
            $sreg = $sreg_resp->contents();

            if (@$sreg['email']) {
                $successresult['email'] = htmlentities($sreg['email']);
            }

            if (@$sreg['nickname']) {
                $successresult['nickname'] = htmlentities($sreg['nickname']);
            }

            if (@$sreg['fullname']) {
                $successresult['fullname'] = htmlentities($sreg['fullname']);
            }

            return $successresult;
        }
    }

}
?>
