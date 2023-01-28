<?php

namespace Coursework;

class Auth
{
    private $userInfo = array();
    private $exists = false;

    function __construct()
    {
        $session = new Session();
        $sessionKey = $session->getSessionData("login_key");

        if (isset($sessionKey) && strlen($sessionKey) > 0)
        {
            $checkKey = new DatabaseWrapper();
            $checkKey->setProcedure('GetUserByKey');
            $checkKey->setArguments(['key'=>$sessionKey]);
            $result = $checkKey->execute();

            if (isset($result[0]))
            {
                $this->userInfo = [
                    'id' => $result[0]['id'],
                    'username' => $result[0]['username'],
                    'email' => $result[0]['email'],
                    'lastLogin' => $result[0]['lastlogin']
                ];
                $this->exists = true;
            }
        }
        else
        {
            $this->exists = false;
        }
    }

    public function getUserData(): Array
    {
        return $this->userInfo;
    }

    public function exists(): Bool
    {       
        return $this->exists;
    }
}