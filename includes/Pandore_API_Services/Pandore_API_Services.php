<?php

require_once 'Pandore_API_Mailchimp.php';
require_once 'Pandore_API_Aweber.php';
require_once 'Pandore_API_Activecampaign.php';
require_once 'Pandore_API_Webhooks.php';

abstract class Pandore_API_Services 
{
	protected $api;
	protected $User;

	/**
	 * Sync the user using the right API
	 * @param  User 		$User   
	 * @param  string 		$listId 
	 * @return Array       	['status' => 'ok|error', 'content' => '....'] 
	 */
	abstract public function syncUser($email, $listId);

	/**
	 * Configure the service with some custom variables
	 * @param  [type] $key   [description]
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	abstract public function configure($key, $value);
}

?>