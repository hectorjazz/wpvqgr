<?php

class Pandore_API_Activecampaign extends Pandore_API_Services
{
	private $apiKey = '';
	private $urlEndpoint = '';
	private $parameters = array();

	function __construct($apiKey, $urlEndpoint)
	{
		$this->apiKey 		=  $apiKey;
		$this->urlEndpoint 	=  $urlEndpoint;
	}
	
	public function configure($key, $value)
	{
		$this->parameters[$key] = $value;
	}

	public function syncUser($email, $listId)
	{
		$ac = new ActiveCampaign($this->urlEndpoint, $this->apiKey);
		if (!(int)$ac->credentials_test()) {
			die("Access denied: Invalid credentials (URL and/or API key).");
			// return;
		}

		$contact = array(
			// Special fixed field
			"first_name"              	=> isset($this->parameters['FIRSTNAME']) ? $this->parameters['FIRSTNAME'] : '',
			"last_name"              	=> isset($this->parameters['LASTNAME']) ? $this->parameters['LASTNAME'] : '',
			"phone"              		=> isset($this->parameters['PHONE']) ? $this->parameters['PHONE'] : '',

			"email"              		=> $email,
			"p[{$listId}]"      		=> $listId,
			"tags" 						=> isset($this->parameters['tags']) ? $this->parameters['tags'] : '',
			"status[{$list_id}]" 		=> 1, // "Active" status
		);
		$contact = array_merge($contact, $this->parameters['mergefields']);
		$contact_sync = $ac->api("contact/sync", $contact);

		// If fails
		if (!(int)$contact_sync->success) {
			$status = [ 'status' => 'error', 'content' => "Syncing contact failed. Error returned: " . $contact_sync->error ];
			return $status;
		} else {
			$status = [ 'status' => 'ok', 'content' => (int)$contact_sync->subscriber_id ];
			return $status;
		}

	}

}

?>