<?php

use GuzzleHttp\Client;

class Pandore_API_Webhooks extends Pandore_API_Services
{
	private $webhooks_url = '';
	private $parameters = array();

	function __construct($webhooks_url)
	{
		$this->webhooks_url = $webhooks_url;
	}

	public function configure($key, $value)
	{
		$this->parameters[$key] = $value;
	}

	public function syncUser($email, $listId)
	{
		$client = new Client([
		  'base_uri' => $this->webhooks_url,
		]);

		$response = $client->post('', array(
		  'form_params' => array(
		  	'quiz_name' 			=>  $this->parameters['quiz']->getName(),
		  	'quiz_id' 				=>  $this->parameters['quiz']->getId(),
		  	'final_score' 			=>  $this->parameters['final_score'],
		  	'user' 					=>  array(
		  		'email' 	=>  $this->parameters['user']->detectEmails(true),
		  		'data'		=>  json_decode($this->parameters['user']->generateJson(), true),
		    ),
		  ),
		));

		$status = array('status' => 'ok', 'content' => '.');
		return $status;
	}
}
?>