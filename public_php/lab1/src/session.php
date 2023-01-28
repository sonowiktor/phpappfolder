<?php
/**
 *Setting/Getting session data securely
 */
namespace Coursework;

class session
{

    function __construct() {}

    function __destruct() {}

    //set server-side session date (needs pre-validation+sanitization)
    public function setSessionData($key, $value): Bool
    {
        //create temporary status variable
        $writeSuccess = false;
        //make sure session value is not empty
        if (!empty($value))
        {
            //create new/overwrite existing server side session variable (@key)
            $_SESSION[$key] = $value;
            //use binary comparison to check it was set correctly
            if (strcmp($_SESSION[$key], $value) == 0) {
                $writeSuccess = true;
            }
        }
        return $writeSuccess;
    }

    //get session data from key
    //returns multiple datatypes: Can't be declared
    public function getSessionData($key)
    {
        //create a status check variable that will be overwritten if data is found
        $val = false;
        //check if the session global key exists/has a set value
        if (isset($_SESSION[$key]))
        {
            //set val to stored session value (if isset)
            $val = $_SESSION[$key];
        }
        return $val;
    }

    //unset a specific stored session key
    //returns multiple datatypes Bool + Object, can't be declared
    public function unsetSession($key)
    {
        //status variable
        $unsetSuccess = false;
        //check if session is set first (before trying to unset)
        if (isset($_SESSION[$key]))
        {
            //unset the stored session key
            unset($_SESSION[$key]);
        }
        //check if it unset successfully
        if (!isset($_SESSION[$key]))
        {
            //change the status variable
            $unsetSuccess = true;
        }
        return $unsetSuccess;
    }
}