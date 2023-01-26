<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->any('/m2mResponse', function(Request $request, Response $response)
{
    //declare vars for user login status and data from keyAuth, and errors from m2mresponse
    $loginStatus = false; $errors = ""; $userData = ""; $newMessage = false; $dbInsertSuccess = ""; $dbInsertErrors = "";

    //check if user is using offline mode
    if (!$GLOBALS['spoofDatabase']) {
        //check user login status with KeyAuth
        $keypairAuth = new Coursework\KeyAuth();
        if ($keypairAuth->exists()) {
            $loginStatus = true;$userData = $keypairAuth->getUserData();
        }

        //check and get $_POST data: if they are pushing m2mMessage to database
        if ($loginStatus == true && $_POST) {
            $postedMessage = new Coursework\Message();
            if ($postedMessage->addPost($_POST)) {
                $validatedMessage = $postedMessage->getMessage();
                $conn = new Coursework\DatabaseWrapper();
                $conn->setProcedure('getMessageByTimestamp');
                $conn->setArguments(["timestamp" => $validatedMessage['timestamp']]);
                $result = $conn->execute();
                if (count($result) > 0) {
                    $dbInsertErrors .= "Duplicate M2M Message exists in the database";
                    //log the message to the database + logger
                    $logMessage = date('m/d/Y h:i:s a', time()) . " :ALERT: M2MResponse Error: Someone tried logging a duplicate M2M message";
                    $log = new Coursework\Monologging();
                    $log->log("notice", $logMessage);
                    //make new connection to insert logs
                    $conn = new Coursework\DatabaseWrapper();
                    $conn->setProcedure("addLog");
                    $conn->setArguments(["message"=>$logMessage]);
                    $conn->execute();
                } else {
                    $conn->setProcedure('addMessage');
                    $conn->setArguments([
                        'timestamp' => $validatedMessage['timestamp'],
                        'phonenumber' => $validatedMessage['simNo'],
                        'sw1' => $validatedMessage['sw1'],
                        'sw2' => $validatedMessage['sw2'],
                        'sw3' => $validatedMessage['sw3'],
                        'sw4' => $validatedMessage['sw4'],
                        'fan1' => $validatedMessage['fan1'],
                        'heater1' => $validatedMessage['heater1'],
                        'keypad' => $validatedMessage['encKeypad'],
                        'name' => $userData['username'],
                        'email' => $userData['email'],
                    ]);
                    $result = $conn->execute();
                    $dbInsertSuccess = "M2M Message successfully inserted into database";
                }
            } else {
                $dbInsertErrors .= "Issue with the M2M Message - columns or login details not sent or empty";
                //log the message to the database + logger
                $logMessage = date('m/d/Y h:i:s a', time()) . " :ALERT: M2MResponse Error: Someone tried logging an M2M Message ~ " . $dbInsertErrors;
                $log = new Coursework\Monologging();
                $log->log("notice", $logMessage);
                //make new connection to insert logs
                $conn = new Coursework\DatabaseWrapper();
                $conn->setProcedure("addLog");
                $conn->setArguments(["message"=>$logMessage]);
                $conn->execute();
            }
        }

        //create a new m2mResponse object
        $m2mResponse = new Coursework\M2mResponse();
        //create a new message object
        $newMessage = new Coursework\Message();
        //pass the m2mResponse message to the new message thru getMessage method
        $newMessage->addM2M($m2mResponse->getMessage());
        //check for errors
        $errors = "";
        if ($newMessage->getErrors()!=="") {
            $errors = $newMessage->getErrors();
            //log the message to the database + logger
            $logMessage = date('m/d/Y h:i:s a', time()) . " :ALERT: " . $newMessage->getErrors();
            $log = new Coursework\Monologging();
            $log->log("notice", $logMessage);
            //make new connection to insert logs
            $conn = new Coursework\DatabaseWrapper();
            $conn->setProcedure("addLog");
            $conn->setArguments(["message"=>$logMessage]);
            $conn->execute();
        }
        $newMessage = $newMessage->getMessage();
    } else {
        //spoofData
        $loginStatus = true;
        $date = date(DATE_ATOM);
        $userData = [
            'id' => '1', 'username' => 'OfflineUser', 'email' => 'user@offline.com', 'lastLogin' => $date
        ];
        $newMessage = [
            'timestamp' => '16/01/2021 20:31:31', 'simNo' => '447817814149', 'sw1' => 'Off', 'sw2' => 'Off', 'sw3' => 'Off', 'sw4' => 'Off', 'fan1' => 'Off', 'heater1' =>  99, 'encKeypad' => '9999'
        ];
    }

    return $this->view->render($response,
        'm2mResponse.html.twig',
        [
            'document_title' => "Coursework M2M Response",
            'css_path' => CSS_PATH,
            'title' => "M2M Response",
            'author' => "23-3110-AI",
            'logged_in' => $loginStatus,
            'userData' => $userData,
            'errors' => $errors,
            'dbInsertErrors' => $dbInsertErrors,
            'dbInsertSuccess' => $dbInsertSuccess,
            'm2m' => $newMessage
        ]);
})->setName('m2mresponse');