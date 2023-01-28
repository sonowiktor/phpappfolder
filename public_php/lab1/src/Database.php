<?php
namespace Coursework;

//use the PDO namespace (\PDO is necessary)
use \PDO;

class Database
{
    //get database connection settings from the settings file in app directory
    private $database_connection_settings;
    private $procedure;
    private $args;
    private $errors;

    function __construct()
    {
        //get the database_connection_settings from settings file
        $this->database_connection_settings = $GLOBALS['settings']['settings']['pdo_settings'];
    }

    function __destruct() {}

    //set the procedure to take place
    public function setProcedure($procedure)
    {
        //check procedure is actually set and not null or empty
        if ($procedure !== "" && isset($procedure)) {
            $this->procedure = $procedure;
        } else {
            //add error to errors string on the object (adds a ", " if it's not the first)
            if (strlen($this->errors) > 0) {
                $this->errors .= ", ";
            }
            $this->errors .= "Database connection error - Invalid connection procedures";
        }
    }

    //set the arguments for the sql procedure
    public function setArguments($args)
    {
        //make sure arguments isn't empty
        if ($args !== "" && isset($args))
        {
            $this->args = $args;
        }
        else
        {
            //add error to errors string on the object (adds a ", " if it's not the first)
            if (strlen($this->errors) > 0)
            {
                $this->errors .= ", ";
            }
            $this->errors .= "Database connection error - Invalid connection arguments";
        }
    }

    //after attaching the procedure and arguments, exec the sql procedure
    //multiple return types, so no return type can be declared
    public function execute()
    {
        //bypass if spookDatabase is set to true
        if (!$GLOBALS['spoofDatabase'])
        {
            //check the procedures and arguments are set
            if ($this->procedure !== "" && $this->args !== "")
            {
                //the variable procedure to set
                $chosenProcedure = "";
                switch ($this->procedure)
                {
                    case "GetUserByKey":
                        if (count($this->args) == 1)
                        {
                            $chosenProcedure = "SELECT * FROM users WHERE `key`=:key LIMIT 1";
                        }
                        break;
                    case "registerUser":
                        if (count($this->args) == 4)
                        {
                            $chosenProcedure = "INSERT INTO users (`username`, `password`, `passwordSalt`, `email`) VALUES (:username, :password, :passwordSalt, :email)";
                        }
                        break;
                    case "checkSalt":
                        if (count($this->args) == 1)
                        {
                            $chosenProcedure = "SELECT * FROM users WHERE `passwordSalt`=:passwordSalt LIMIT 1";
                        }
                        break;
                    //INDIVIDUAL FINDS (arguments cannot be used as column identifiers for security reasons in PDO, so need to be individually switched)
                    case "findUserByUsername":
                        if (count($this->args) == 1)
                        {
                            $chosenProcedure = "SELECT * FROM users WHERE `username`=:username LIMIT 1";
                        }
                        break;
                    case "findUserByEmail":
                        if (count($this->args) == 1)
                        {
                            $chosenProcedure = "SELECT * FROM users WHERE `email`=:email LIMIT 1";
                        }
                        break;
                    //UPDATES
                    case "updateSessionKey":
                        if (count($this->args) == 2)
                        {
                            $chosenProcedure = "UPDATE users SET `key`=:key WHERE `username`=:username";
                        }
                        break;
                    case "getAllMessages":
                        $chosenProcedure = "SELECT * FROM messages";
                        break;
                    case "getMessageByTimestamp":
                        if (count($this->args) == 1)
                        {
                            $chosenProcedure = "SELECT * FROM messages WHERE `timestamp` = :timestamp";
                        }
                        break;
                    case "addMessage":
                        if (count($this->args) == 11)
                        {
                            $chosenProcedure = "INSERT INTO messages (`timestamp`, `phonenumber`, `heater1`, `fan1`, `name`, `email`, `sw1`, `sw2`, `sw3`, `sw4`, `keypad`) VALUES (:timestamp, :phonenumber, :heater1, :fan1, :name, :email, :sw1, :sw2, :sw3, :sw4, :keypad)";
                        }
                        break;
                    case "addLog":
                        if (count($this->args) == 1)
                        {
                            $chosenProcedure = "INSERT INTO log (`message`) VALUES (:message)";
                        }
                        break;
                    default:
                        $chosenProcedure = "";
                        //non-valid procedure. add error to errors string + adds a ", " if it's not the first
                        if (strlen($this->errors) > 0)
                        {
                            $this->errors .= ", ";
                        }
                        $this->errors .= "Database connection error - Procedure doesn't exist";
                }

                //try pdo function
                if (strlen($this->errors) < 1)
                {
                    try
                    {
                        //create conn PDO object, with database settings
                        $conn = new PDO(
                            "mysql:host={$this->database_connection_settings['host']};dbname={$this->database_connection_settings['db_name']}",
                            $this->database_connection_settings['user_name'],
                            $this->database_connection_settings['user_password']
                        );

                        //set PDO specific attributes for error mode and modes to return
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        //prepare PDO SQL connection with the SQL procedure
                        $stmt = $conn->prepare($chosenProcedure);

                        //EXECUTE!
                        $stmt->execute($this->args);

                        //PDO change just return the object (bool)
                        if ($this->procedure=="registerUser"||
                            $this->procedure=="updateSessionKey"||
                            $this->procedure=="addMessage"||
                            $this->procedure=="addLog")
                        {
                            return $stmt;
                        }
                        else
                        {
                            //PDO set fetch mode to associative indices row['col'=>'val']
                            $stmt->setFetchMode(PDO::FETCH_ASSOC);
                            return $stmt->fetchAll();
                        }

                    }
                    catch(PDOException $e)
                    {
                        //PDO exception for backend
                        //$messageErrors .= $e->getMessage();
                        //user friendly output:
                        if (strlen($this->errors) > 0) { $this->errors .= ", "; }
                        $messageErrors .= "Database connection error - This has been logged and the admin has been notified";
                        return false;
                    }
                    $conn=null;
                }
                else
                {
                    //if procedures aren't set
                    if (strlen($this->errors) > 0) { $this->errors .= ", "; }
                    $this->errors .= "Invalid DatabaseWrapper procedure or arguments";
                    return false;
                }
            }
        }
    }

    //return the errors
    public function getErrors(): String
    {
        return $this->errors;
    }
}