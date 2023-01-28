<?php
namespace Coursework;

class inputContainer
{
    // property declaration
    private $input = "";
    private $errorsArray = array();

    // construction method - returns true if no errors
    function __construct($user_input)
    {
        //set placeholder input data
        $this->input = $user_input;
    }

    //sanitizes input field, make sure doesn't contain regex
    //return true if errors exist
    public function sanitize(): Bool
    {
        $regex = "!\" #$%^&*()+=-[]';,./{}|:<>?~";
        if (strpbrk($regex, $this->input))
        {
            array_push($this->errorsArray, "Please avoid using the following characters: #$%^&*()+=-[]';,./{}|:<>?~");
            return true;
        }
        else
        {
            return false;
        }
    }

    //validate -  makes sure that the input is not empty
    //if errors exist - return true
    public function validate(): Bool
    {
        if ($this->input == "")
        {
            array_push($this->errorsArray, 'input is empty');
            return true;
        }
        else
        {
            return false;
        }
    }
/**
   DO WE NEED THIS?
    //special validate for email (needs "." and "@" min)
    public function validateEmail(): Bool
    {
        if ($this->input == "")
        {
            array_push($this->errorsArray, 'input is empty');
            return true;
        }
        else
        {
            //get pos of @ in input
            if (strpos($this->input, "@") > -1 && strpos($this->input, ".") > -1)
            {
                return false;
            }
            else
            {
                array_push($this->errorsArray, 'Email has incorrect format, must use @ and .co');
                return true;
            }
        }
    }

    //checks the lengths of the input is between to lengths
    public function checkLength($min, $max): Bool
    {
        $return = false;
        if (strlen($this->input) < $min || strlen($this->input) > $max)
        {
            $return = true;
        }
        return $return;
    }

    //special sanitization for email! (excludes -.)
    public function sanitizeEmail(): Bool
    {
        $regex = " !\"#$%^&*()+=[]';,/{}|:<>?~";
        if (strpbrk($regex, $this->input))
        {
            array_push($this->errorsArray, 'contains illegal characters');
            return true;
        }
        else
        {
            return false;
        }
    }
*/
    //method returns errorsArray
    //multiple return type declarations
    public function getErrors()
    {
        //check errorsArray isn't empty
        $return = "Error 201: No errors (apart from this one)";
        if (count($this->errorsArray) > -1)
        {
            $return = "";
            for ($i = 0;$i < count($this->errorsArray);$i++) {
                if ($i == count($this->errorsArray)) {
                    $return = $return . $this->errorsArray[$i];
                } else {
                    $return = $return . $this->errorsArray[$i] .  ", ";
                }
            }
            return $return;
        }
    }

    //get the array of errors attribute from objects
    public function getErrorsArray(): array
    {
        return $this->errorsArray;
    }

    //method dumps this object
    public function dump(): Object
    {
        return $this;
    }

    //get input attribute
    public function getInput(): String
    {
        return $this->input;
    }
}