<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app ->get('/', function (Request $request, Response $response) {
    $errors = ""; $loginStatus = false; $userData = ""; $login_msg = "";

    //check if the application is in offline mode/databaseSpoofing mode (when no connection to a local database)
    if (!$GLOBALS['spoofDatabase']) {
        //check user is logged in with session key
        $keypairAuth = new Coursework\KeyAuth();
        if ($keypairAuth->exists()) {
            //set loginStatus variable to true for Twig and other checks
            $loginStatus = true;
            //set the userData from the KeyAuth SQL response
            $userData = $keypairAuth->getUserData();
            //redirect to the homepage if the user is already logged in
            return $response->withHeader('Location', '.');
        }

        //check if POST data is sent with the request to do relevant actions
        //	the form on this page will loopback to this script on the same page (to avoid excessive files)
        if (isset($_POST['iusername']) && isset($_POST['ipassword'])) {
            //after isset check, get the _POST form data into "Input" objects
            $inputUsername = new Coursework\InputContainer(strtolower($_POST['iusername']));
            $inputPassword = new Coursework\InputContainer($_POST['ipassword']);

            //validation and sanitize these objects
            if ($inputUsername->validate() !== true) {$inputUsername->sanitize();}
            if ($inputPassword->validate() !== true) {$inputPassword->sanitize();}

            //create temp array for error messages across both user/pass objects ^
            $tempErrorsArray = Array();

            //check inputs are correct length; not necessary here, but is necessary on the register form
            if ($inputUsername->checkLength(4, 24)) {array_push($tempErrorsArray, "Password needs to be 6-24 characters");}
            if ($inputPassword->checkLength(6, 24)) {array_push($tempErrorsArray, "Password needs to be 6-24 characters");}

            //check if errors exist in username input and add to tempArray if exists
            if(count($inputUsername->getErrorsArray()) > 0) {
                for ($i = 0;$i < count($inputUsername->getErrorsArray());$i++) {
                    //push user-friendly error message to array
                    array_push($tempErrorsArray, "Username ".$inputUsername->getErrorsArray()[$i]);
                }
            }
            //check if errors exist in password input and add to tempArray if exists
            if(count($inputPassword->getErrorsArray()) > 0) {
                for ($i = 0;$i < count($inputPassword->getErrorsArray());$i++) {
                    //push user-friendly error message to array
                    array_push($tempErrorsArray, "Password ".$inputPassword->getErrorsArray()[$i]);
                }
            }

            //final check for errors in array (if no errors, do login),
            //loop through the array and add each string to the $login_msg var
            if (count($tempErrorsArray) == 0) {
                //if no errors exist thus far, check the database to see if username exists:
                $usernameCheck = new Coursework\DatabaseWrapper();
                $usernameCheck->setProcedure('findUserByUsername');
                $usernameCheck->setArguments(["username"=>$inputUsername->getInput()]);
                $usernameCheckResult = $usernameCheck->execute();
                if (isset($usernameCheckResult)) {
                    if (count($usernameCheckResult) == 0) {
                        array_push($tempErrorsArray, "User doesn't exist");
                    } else {
                        //check password if username exists
                        //use salt from previous db response to encrypt the password
                        $passwordHash = hash("sha512", $inputPassword->getInput().$usernameCheckResult[0]['passwordSalt']);
                        //binary compare the db passwordHash and user's passwordHash
                        if (strcmp($passwordHash, $usernameCheckResult[0]['password'])) {
                            //password incorrect
                            array_push($tempErrorsArray, "Password incorrect");
                            //log the message to the database + logger
                            $logMessage = date('m/d/Y h:i:s a', time()) . " :NOTICE: Login Error: Someone tried logging into account " . $inputUsername->getInput() . " with wrong username.";
                            $log = new Coursework\Monologging();
                            $log->log("notice", $logMessage);
                            //make new connection to insert logs
                            $conn = new Coursework\DatabaseWrapper();
                            $conn->setProcedure("addLog");
                            $conn->setArguments(["message"=>$logMessage]);
                            $conn->execute();
                        } else {
                            //password correct route
                            //generate matching session and database login_keys, needs to be unique
                            //generate key to be used
                            $randomKey = substr(base64_encode(random_bytes(192)), 0, 200);
                            //get any rows from database users with this key
                            $checkSessionKey = new Coursework\DatabaseWrapper();
                            $checkSessionKey->setProcedure('GetUserByKey');
                            $checkSessionKey->setArguments(["key"=>$randomKey]);
                            $checkSessionKeyResult = $checkSessionKey->execute();
                            if (sizeof($checkSessionKeyResult) > 0) {
                                //if key already exists push an error for user (this will definitely NEVER happen!)
                                array_push($tempErrorsArray, "Ermm... error? Your randomely generated key that keeps you logged in accidentally matched with another key from the database. The chances of this happening is 1 in 1,580,681,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000,000. Please try again.");

                                //log the message to the database + logger
                                $logMessage = date('m/d/Y h:i:s a', time()) . " :ALERT: Login Error: Generated session key was duplicate. If this happens a lot, check the code for 'login.php'";
                                $log = new Coursework\Monologging();
                                $log->log("notice", $logMessage);
                                //make new connection to insert logs
                                $conn = new Coursework\DatabaseWrapper();
                                $conn->setProcedure("addLog");
                                $conn->setArguments(["message"=>$logMessage]);
                                $conn->execute();
                            } else {
                                //if no users exist with this key, brilliant - update key column in the database with the new generated key
                                $updateKey = new Coursework\DatabaseWrapper();
                                $updateKey->setProcedure('updateSessionKey');
                                $updateKey->setArguments([
                                    "key" => $randomKey,
                                    "username" => $inputUsername->getInput()
                                ]);
                                $updateKeyResult = $updateKey->execute();
                                //next set the user's session key to the same as the database - making a keypair for future KeyAuth validations (on page load)
                                $session = new Coursework\SessionWrapper();
                                $session->setSessionData("login_key", $randomKey);
                                //set login status to true
                                $loginStatus = true;
                                //log the message to the database + logger
                                $logMessage = date('m/d/Y h:i:s a', time()) . " :INFO: Login success: " . $inputUsername->getInput() . " logged in successfully";
                                $log = new Coursework\Monologging();
                                $log->log("notice", $logMessage);
                                //make new connection to insert logs
                                $conn = new Coursework\DatabaseWrapper();
                                $conn->setProcedure("addLog");
                                $conn->setArguments(["message"=>$logMessage]);
                                $conn->execute();
                                //redirect the user to the homepage - they should now be logged in
                                return $response->withHeader('Location', '.');
                            }
                        }
                    }
                }
            }

            //final errors pushed to twig template
            if (count($tempErrorsArray) > 0) {
                for ($i = 0;$i < count($tempErrorsArray);$i++) {
                    //add comma and space to $login_msg (except first index)
                    if (strlen($login_msg) > 0) { $login_msg .= ", "; }
                    //add actual error message to the output string
                    $login_msg .= $tempErrorsArray[$i];
                }
                //log the message to the database + logger
                $logMessage = date('m/d/Y h:i:s a', time()) . " :NOTICE: Login Errors: " . $login_msg;
                $log = new Coursework\Monologging();
                $log->log("notice", $logMessage);
                //make new connection to insert logs
                $conn = new Coursework\DatabaseWrapper();
                $conn->setProcedure("addLog");
                $conn->setArguments(["message"=>$logMessage]);
                $conn->execute();
            }
        }
    } else {
        //set variable values if offline mode is true
        $loginStatus = false;
        $login_msg = "Offline mode enabled";
    }


    return $this->view->render($response,
        'login.html.twig',
        [
            'document_title' => "Coursework Login",
            'css_path' => CSS_PATH,
            'title' => "Coursework Login",
            'author' => "23-3110-AI",
            'logged_in' => $loginStatus,
            'login_msg' => $login_msg
        ]);
})->setName('login');
