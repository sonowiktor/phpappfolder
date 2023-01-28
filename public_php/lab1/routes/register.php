<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//create the Slim route with the request and response interfaces
$app->any('/register', function(Request $request, Response $response)
{
    //declare variables to be used within this route
    $errors = ""; $regSuccess = false; $loginStatus = false; $login_msg = "";

    //NOTE: this page loads and can be edited without offline mode
    //use new SessionWrapper and KeyAuth to check the db+session keypair is valid
    $session = new Coursework\SessionWrapper();
    $keypairAuth = new Coursework\KeyAuth();
    if ($keypairAuth->exists()) {
        //set login status to true (for twig)
        $loginStatus = true;
        //redirect user if they are logged in so they can't register while logged in
        $response->withHeader('Location', '.');
    } else {
        //if there was an issue with the session key or keypair match with database, unset the session key (they will need to login again to generate a new session keypair for session+db)
        $session->unsetSession("login_key");
        //log the message to the database + logger
        $logMessage = date('m/d/Y h:i:s a', time()) . " :ALERT: User's session key was unset. If this happens a lot there might be an error with sessions";
        $log = new Coursework\monolog();
        $log->log("notice", $logMessage);
        //make new connection to insert logs
        $conn = new Coursework\DatabaseWrapper();
        $conn->setProcedure("addLog");
        $conn->setArguments(["message"=>$logMessage]);
        $conn->execute();
    }

    //check if POST data is sent with the request to do relevant actions
    //	the form on this page will loopback to this script on the same page (to avoid excessive files)
    if (isset($_POST['iusername']) && isset($_POST['ipassword']) && isset($_POST['ipassword2']) && isset($_POST['iemail'])) {
        //after isset check, get the _POST form data into "Input" objects
        //username and email are not case sensitive (will be made lowercase)
        $inputUsername = new Coursework\InputContainer(strtolower($_POST['iusername']));
        $inputPassword = new Coursework\InputContainer($_POST['ipassword']);
        $inputPassword2 = new Coursework\InputContainer($_POST['ipassword2']);
        $inputEmail = new Coursework\InputContainer(strtolower($_POST['iemail']));

        //validate and sanitize
        if ($inputUsername->validate() !== true) {$inputUsername->sanitize();}
        if ($inputPassword->validate() !== true) {$inputPassword->sanitize();}
        if ($inputPassword2->validate() !== true) {$inputPassword2->sanitize();}
        if ($inputEmail->validateEmail() !== true) {$inputEmail->sanitizeEmail();}

        //create temp array for error messages across both user/pass objects ^
        $tempErrorsArray = Array();

        //check inputs are right string length
        //only need to check 1 password because they both need to match anyway
        if ($inputPassword->checkLength(6, 24)) {array_push($tempErrorsArray, "Password needs to be 6-24 characters");}
        if ($inputUsername->checkLength(4, 24)) {array_push($tempErrorsArray, "Username needs to be 4-24 characters");}
        if ($inputEmail->checkLength(5, 50)) {array_push($tempErrorsArray, "Email needs to be 5-50 characters");}

        //check if errors exist in username input and add to tempArray if exists
        if(count($inputUsername->getErrorsArray()) > 0)
        {
            for ($i = 0;$i < count($inputUsername->getErrorsArray());$i++)
            {
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
        //check if errors exist in password input and add to tempArray if exists
        if(count($inputPassword2->getErrorsArray()) > 0) {
            for ($i = 0;$i < count($inputPassword2->getErrorsArray());$i++)
            {
                //push user-friendly error message to array
                array_push($tempErrorsArray, "Repeat Password ".$inputPassword2->getErrorsArray()[$i]);
            }
        }
        //check the passwords match
        if(strcmp($inputPassword->getInput(), $inputPassword2->getInput())) {
            array_push($tempErrorsArray, "Passwords do not match");
        }

        //check if errors exist in password input and add to tempArray if exists
        if(count($inputEmail->getErrorsArray()) > 0) {
            for ($i = 0;$i < count($inputEmail->getErrorsArray());$i++) {
                //push user-friendly error message to array
                array_push($tempErrorsArray, $inputEmail->getErrorsArray()[$i]);
            }
        }

        //final check for errors in errorsArray (if no errors, do login),
        //loop through the array and add each string to the $login_msg var
        if (count($tempErrorsArray) > 0) {
            for ($i = 0;$i < count($tempErrorsArray);$i++) {
                //add comma and space to $login_msg (except first index)
                if ($i !== 0) { $login_msg .= ", "; }
                //add actual error message to the output string
                $login_msg .= $tempErrorsArray[$i];
            }
        } else {
            //quick check if the username is already being used in the database
            $usernameCheck = new Coursework\DatabaseWrapper();
            $usernameCheck->setProcedure('findUserByUsername');
            $usernameCheck->setArguments([
                "username" => $inputUsername->getInput()
            ]);
            $usernameCheckResult = $usernameCheck->execute();
            if (isset($usernameCheckResult)) {
                if (count($usernameCheckResult) > 0) {
                    array_push($tempErrorsArray, ("Username already in use"));
                    //log the message to the database + logger
                    $logMessage = date('m/d/Y h:i:s a', time()) . " :REG ERROR: " . $inputUsername->getInput() . " tried to create account - username alreadu in use";
                    $log = new Coursework\monolog();
                    $log->log("notice", $logMessage);
                    //make new connection to insert logs
                    $conn = new Coursework\DatabaseWrapper();
                    $conn->setProcedure("addLog");
                    $conn->setArguments(["message"=>$logMessage]);
                    $conn->execute();
                }
            }

            //quick check if the email is already being used in the database
            $emailCheck = new Coursework\DatabaseWrapper();
            $emailCheck->setProcedure('findUserByEmail');
            $emailCheck->setArguments([
                "email" => $inputEmail->getInput()
            ]);
            $emailCheckResult = $emailCheck->execute();
            if (isset($emailCheckResult)) {
                if (count($emailCheckResult) > 0) {
                    array_push($tempErrorsArray, ("Email already in use"));
                }
            }

            //do a quick check to make sure no errors exist before adding to db
            if (count($tempErrorsArray) > 0) {
                for ($i = 0;$i < count($tempErrorsArray);$i++) {
                    if ($i !== 0) { $login_msg .= ", "; }
                    $login_msg .= $tempErrorsArray[$i];
                    //log the message to the database + logger
                    $logMessage = date("Y-m-d h:i:sa", time()) . " ::: Registation Error: " . $inputUsername->getInput() . ", tried to log in unnsuccessfully";
                    $log = new Coursework\monolog();
                    $log->log("notice", $logMessage);
                }
            } else {
                //make a password salt to prevent a dictionary/rainbow table attack. Would be just as inefficient as bruteforcing a completely new password
                //needs to be be MIME base64 for crypt() function
                $passwordSalt = base64_encode(random_bytes(32));
                $passwordHash = hash("sha512", $inputPassword->getInput().$passwordSalt);
                //create new databaseWrapper
                $conn = new Coursework\DatabaseWrapper();
                //set the procedure and arguments
                $conn->setProcedure('registerUser');
                $conn->setArguments([
                    'username'=>$inputUsername->getInput(),
                    'password'=>$passwordHash,
                    'passwordSalt'=>$passwordSalt,
                    'email'=>$inputEmail->getInput()
                ]);
                $result = $conn->execute();
                //check and output errors to variable
                $regSuccess = true;
                //log the message to the database + logger
                $logMessage = date('m/d/Y h:i:s a', time()) . " :NOTICE: " . $inputUsername->getInput() . ", has successfully registered a new account. Congratulation!";
                $log = new Coursework\monolog();
                $log->log("notice", $logMessage);
                //make new connection to insert logs
                $conn = new Coursework\DatabaseWrapper();
                $conn->setProcedure("addLog");
                $conn->setArguments(["message"=>$logMessage]);
                $conn->execute();
            }
        }
    }

    return $this->view->render($response,
        'register.html.twig',
        [
            'document_title' => "Coursework Register",
            'css_path' => CSS_PATH,
            'title' => "Coursework Register",
            'author' => "23-3110-AI",
            'logged_in' => $loginStatus,
            'login_msg' => $login_msg,
            'reg_success' => $regSuccess
        ]);
})->setName('register');