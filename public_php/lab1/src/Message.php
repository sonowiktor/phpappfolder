<?php
namespace Coursework;

class Message
{
    // property declaration
    private $timestamp = "";
    private $simNo = 0;
    //private $name = "";
    //private $email = "";
    private $sw1 = false;
    private $sw2 = false;
    private $sw3 = false;
    private $sw4 = false;
    private $fan1 = false;
    private $heater1 = 0;
    private $encKeypad = "";
    private $errors = "";

    //construction method
    function __construct() {}

    //use soap $response (from Coursework\M2mResponse() method) as input for the object
    //this object's fields will be filled with the response data
    public function addM2M($response)
    {
        if (isset($response) && $response !== "")
        {
            //"explode" the xml into an accessable array
            $unpackedResponse = simplexml_load_string($response);
            //check if the message body contains the required name
            //(this happens in M2MResponse class, but do it again here for extra-validation)
            if (strpos($unpackedResponse->message, "Coursework") == -1)
            {

                if (strlen($this->errors) > 0)
                {
                    $this->errors .= ", ";
                }
                $this->errors .= "M2M Response exists, but no message with 'Coursework' identification";

            }
            else
            {
                //check if the message body contains the right components "(", ")", commas.
                if (
                    strpos($unpackedResponse->message, "(") > -1 &&
                    strpos($unpackedResponse->message, ")") > -1 &&
                    strpos($unpackedResponse->message, ",") > -1 )
                {
                    //traversal of the message body with custom format e.g "name(args, args)"
                    $explodedInput = explode(",", explode("(", explode(")", $unpackedResponse->message)[0])[1]);
                    $this->sw1 = boolval($explodedInput[0]) ? 'On' : 'Off';
                    $this->sw2 = boolval($explodedInput[1]) ? 'On' : 'Off';
                    $this->sw3 = boolval($explodedInput[2]) ? 'On' : 'Off';
                    $this->sw4 = boolval($explodedInput[3]) ? 'On' : 'Off';
                    $this->fan1 = boolval($explodedInput[4]) ? 'On' : 'Off';
                    $this->heater1 = intval($explodedInput[5]);
                    $this->encKeypad = strval($explodedInput[6]);

                    //add message metadata
                    $this->simNo = $unpackedResponse->sourcemsisdn;
                    $this->timestamp = $unpackedResponse->receivedtime;
                }
                else
                {
                    if (strlen($this->errors) > 0)
                    {
                        $this->errors .= ", ";
                    }
                    $this->errors .= "M2M Response exists, but message isn't formatted correctly";
                }
            }
        }
        else
        {
            if (strlen($this->errors) > 0)
            {
                $this->errors .= ", ";
            }
            $this->errors .= "SOAP Error (Empty response)";
        }
    }

    //validate, sanitize, check datatypes and convert to database datatypes
    public function addPost($post): Bool
    {
        $return = false;
        //a really stupidly manual way to check the isset and strlength of the addPost
        if (
            isset($post['simNo']) && strlen($post['simNo']) &&
            isset($post['sw1']) && strlen($post['sw1']) &&
            isset($post['sw2']) && strlen($post['sw2']) &&
            isset($post['sw3']) && strlen($post['sw3']) &&
            isset($post['sw4']) && strlen($post['sw4']) &&
            isset($post['fan1']) && strlen($post['fan1']) &&
            isset($post['heater1']) && strlen($post['heater1']) &&
            isset($post['encKeypad']) && strlen($post['encKeypad']) &&
            isset($post['timestamp']) && strlen($post['timestamp']))
        {
            //another really stupidly manual way to make sure the datatypes are correct
            if (
                //check booleans
                (($post['sw1']=="On"||$post['sw1']) == "Off") &&
                (($post['sw2']=="On"||$post['sw2']) == "Off") &&
                (($post['sw3']=="On"||$post['sw3']) == "Off") &&
                (($post['sw4']=="On"||$post['sw4']) == "Off") &&
                (($post['fan1']=="On"||$post['fan1']) == "Off") &&
                //check ints and string length
                (strlen($post['heater1']) > 0 && strlen($post['heater1']) < 5) &&
                (strlen($post['timestamp']) > 0 && strlen($post['timestamp']) < 60) &&
                strlen($post['simNo']) == 12 &&
                strlen($post['encKeypad']) == 4)
            {
                //set the attributes as message!
                $this->timestamp = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $post['timestamp'])));
                $this->sw1 = filter_var(strtolower($post['sw1']), FILTER_VALIDATE_BOOLEAN);
                $this->sw2 = filter_var(strtolower($post['sw2']), FILTER_VALIDATE_BOOLEAN);
                $this->sw3 = filter_var(strtolower($post['sw3']), FILTER_VALIDATE_BOOLEAN);
                $this->sw4 = filter_var(strtolower($post['sw4']), FILTER_VALIDATE_BOOLEAN);
                $this->fan1 = filter_var(strtolower($post['fan1']), FILTER_VALIDATE_BOOLEAN);
                $this->heater1 = intval($post['heater1']);
                $this->encKeypad = intval($post['encKeypad']);
                $this->simNo = intval($post['simNo']);
                $return = true;
            }
            else
            {
                $errors = "M2M attributes are incorrect length/datatype";
            }
        }
        return $return;
    }

    //sanitizes all the fields, make sure they don't contain regex
    //return true if errors exist
    public function sanitize(): Bool
    {
        $regex = '/[^a-z0-9 -]+/';
        if (preg_match($regex, $this->sw1)||
            preg_match($regex, $this->sw2)||
            preg_match($regex, $this->sw3)||
            preg_match($regex, $this->sw4)||
            preg_match($regex, $this->fan1)||
            preg_match($regex, $this->heater1)||
            preg_match($regex, $this->encKeypad))
        {
            if (strlen($this->errors) > 0)
            {
                $this->errors .= ", ";
            }
            $this->errors .= 'Message Sanitization failed';
            return true;
        }
        else
        {
            return false;
        }
    }

    //validate ensures all fields are the correct type
    //return true if errors exist
    public function validate(): Bool
    {
        if ($this->sw1 === boolean||
            $this->sw2 === boolean||
            $this->sw3 === boolean||
            $this->sw4 === boolean||
            $this->fan1 === boolean||
            $this->heater1 === int||
            $this->encKeypad === int||
            $this->encKeypad === date)
        {
            if (strlen($this->errors) > 0) { $this->errors .= ", "; }
            $this->errors .= 'Message validation failed';
            return true;
        }
        else
        {
            return false;
        }
    }

    //get message object array
    public function getMessage()
    {
        return [
            "timestamp" => $this->timestamp,
            "simNo" => $this->simNo,
            "sw1" => $this->sw1,
            "sw2" => $this->sw2,
            "sw3" => $this->sw3,
            "sw4" => $this->sw4,
            "fan1" => $this->fan1,
            "heater1" => $this->heater1,
            "encKeypad" => $this->encKeypad
        ];
    }

    //method returns errorsArray
    public function getErrors(): String
    {
        return $this->errors;
    }

    //method dumps this object
    public function dump(): Object
    {
        return $this;
    }
}