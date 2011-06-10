<?php
/**
 * Global Error Class
 *
 * Class to create/call/print Errors
 * @author Markus Schlegel <g42@gmx.net>
 * @copyright Copyright (C) 2006 Markus Schlegel
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package Ruins
 */

/**
 * Namespaces
 */
namespace Common\Controller;
use Exception;

/**
 * Global Error Class
 *
 * Class to create/call/print Errors
 * @package Ruins
 */
class Error extends Exception
{

    /**
     * constructor - load the default values and initialize the attributes
     * @param string $message Given Errormessage
     * @param int $code Errorcode
     */
    public function __construct($message, $code = 0) {
        // Call Constructor of the Parent-Class
        parent::__construct($message, $code);
    }

    /**
     * Define our own Debugmessage
     */
    public function __toString() {
        return "Exception cought in {$this->file}({$this->line}):\n"
                                . "{$this->message}";
    }

}
?>
