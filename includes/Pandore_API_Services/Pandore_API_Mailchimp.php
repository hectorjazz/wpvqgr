<?php

class Pandore_API_Mailchimp extends Pandore_API_Services
{
	private $apiKey = '';
	private $parameters = array();

	function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	public function configure($key, $value)
	{
		$this->parameters[$key] = $value;
	}

	public function syncUser($email, $listId)
	{
		// Connect to mailchimp
		try {
			$mc = new Mailchimp\Mailchimp($this->apiKey);	
		} 
		catch (Exception $e) {
			echo "Can't connect to MC.";
			return;
		}

		// Post to add member
		try 
		{
			$data = array(
				'email_address' => $email,
				'status' 		=> ($this->parameters['doubleoptin']) ? 'pending':'subscribed',
			);

			if (!empty($this->parameters['mergefields'])) {
				$data['merge_fields'] = $this->parameters['mergefields'];
			}

			$jsonReturn = $mc->post("lists/{$listId}/members", $data);	
		} 
		catch (Exception $e) 
		{
			$error = $e->getMessage();
			$error = json_decode($error);

			$status = array('status' => 'error', 'content' => $error->title . ' - ' . $error->detail);
			return $status;
		}

		$mailchimpReturn = json_decode($jsonReturn);
		$status = array('status' => 'ok', 'content' => $mailchimpReturn->id);
		return $status;
	}
}
?>