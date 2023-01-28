<?php
namespace Coursework;

class m2mResponse
{
    //soap parameters for the m2m connect wsdl call
    private $soap_call_parameters;
    //message storage
    private $message = "";
    //error string
    private $error = "";

    // returns response, or false if error exists
    function __construct()
    {
        //set the m2mconnect settings
        $this->soap_call_parameters = $GLOBALS['settings']['settings']['m2m_settings'];

        //do soap call
        try
        {
            //create new instance of the createSoap method
            $soap_client_handle = $this->createSoap();
            //store the response of the 'peekMessages' function
            $soap_results = $soap_client_handle->__soapCall('peekMessages', $this->soap_call_parameters);

            //loop through results to get newest Coursework message
            if (isset($soap_results))
            {
                for ($i = 0;$i < count($soap_results);$i++)
                {
                    //set the var lastMsg, if a message containing "Coursework" is found
                    if (strpos($soap_results[$i], "Coursework") > 0)
                    {
                        $this->message = $soap_results[$i];
                    }
                }
            }
            else
            {
                $this->error .= "SOAP response was blank or null";
            }

            //check if a message was found, if not return false and add error
            if ($this->message !== "")
            {
                return true;
            }
            else
            {
                //make sure error displays nicely
                if (strlen($this->error) > -1) {$this->error .= ", ";}
                $this->error .= "No message found for Coursework! Make sure the correct M2MConnect details are in src/M2mResponse.php variables, and send a message to your MSISDN in this format: gobbwobblers(0,0,0,0,0,99,9999)";
                return false;
            }

        } catch(\SoapFault $exception) {
            //catch a SoapFault and add error to error variable
            //trigger_error($exception);
            if (strlen($this->error) > -1)
            {
                $this->error.=", ";
            }
            $this->error .= $exception;
            return false;
        }
    }

    //get just the message field
    public function getMessage(): String
    {
        return $this->message;
    }

    //method for soap call to m2m connect server
    public function createSoap(): Object
    {
        $soap_client_handle;
        $soapclient_attributes = ['trace' => true, 'exceptions' => true];
        $wsdl = WSDL;

        try
        {
            $soap_client_handle = new \SoapClient($wsdl, $soapclient_attributes);
        } catch (\SoapFault $exception) {
            trigger_error($exception);
        }

        return $soap_client_handle;
    }

    //retrieve the private error fields
    public function getError(): String
    {
        return $this->error;
    }
}