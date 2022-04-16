<?php

class Pandore_API_Aweber extends Pandore_API_Services
{
    private $apiKey           = '';
    private $accessKeys       = '';
    private $consumerKey      = '';
    private $consumerSecret   = '';
    private $accessKey        = '';
    private $accessSecret     = '';

    private $parameters = array();

    function __construct($apiKey)
    {
        $this->apiKey           =  $apiKey;
        $_accessKeys            =  explode('|', $this->apiKey);

        $this->consumerKey     =  $_accessKeys[0];
        $this->consumerSecret  =  $_accessKeys[1];
        $this->accessKey       =  $_accessKeys[2];
        $this->accessSecret    =  $_accessKeys[3];
    }

    public function configure($key, $value)
    {
        $this->parameters[$key] = $value;
    }

	public function syncUser($email, $listId)
	{
        // Clean List ID 
        $listId = str_replace('awlist', '', $listId);

        // Auth
        $application    =  new AWeberAPI($this->consumerKey, $this->consumerSecret);
        $account        =  $application->getAccount($this->accessKey, $this->accessSecret);

        try 
        {
            $listUrl = "/accounts/{$account->id}/lists/$listId";
            $list = $account->loadFromUrl($listUrl);

            $subscriber = array(
			    'email' => $email,
			    'name'  => $listId,
			);

            if (!empty($this->parameters['custom_fields'])) {
                $subscriber['custom_fields'] = $this->parameters['custom_fields'];
            }

            $newSubscriber = $list->subscribers->create($subscriber);
        }
        catch(Exception $exc) {
            print $exc;
            die();
        }

        echo "<pre>";
        echo "— DEBUG —";
        print_r($newSubscriber->data);
        echo "</pre>";
        die();
	}

	/**
	 * Generate Aweber Creds via Ajax
	 * @return json with status=OK or FAIL
	 */
	public static function getCreds($authCode)
	{
		$authCode 	=  htmlspecialchars($authCode);

		$credentials 				=  AWeberAPI::getDataFromAweberID($authCode);
		$values['consumerKey']      =  $credentials[0];
        $values['consumerSecret']   =  $credentials[1];
        $values['accessKey']        =  $credentials[2];
        $values['accessSecret']     =  $credentials[3];

        return $values;
	}
}

?>