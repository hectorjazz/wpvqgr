<?php 

class WPVQGR_hook_results_API_services
{
	function __construct()
	{
		add_action('wpvqgr_end_quiz', array($this, 'syncUser'), 1000, 3);
	}

	public function syncUser($Quiz, $User, $finalScore)
	{
		if ($Quiz->getSetting('syncuser') == 'nosync') {
			return;
		}
		
		// Mail
		$email 	=  $User->detectEmails(true);

		// Service configuration
		$serviceName  =  $Quiz->getSetting('syncuser');
		if ($serviceName == 'mailchimp') 
		{
			$apiKey = $Quiz->getSetting('syncuser_mailchimp_apikey');
			$listId = $Quiz->getSetting('syncuser_mailchimp_listid');

			$api = new Pandore_API_Mailchimp($apiKey);
			$api->configure('doubleoptin', $Quiz->getSetting('syncuser_mailchimp_doubleoptin'));

			$mergeFields = array();
			$quizFields = $Quiz->getSetting('askinfo_fields');

			foreach($User->getMetas() as $key => $value)
			{
				$mergeName = $quizFields[$key]['wpvqgr_settings_askinfo_syncuser_mapfields'];
				if ($value['wpvqgr_user_meta_value'] == $email) {
					continue;
				}
				else if ($mergeName != '') {
					$mergeFields[$mergeName] = $value['wpvqgr_user_meta_value'];
				}
			}

			// Save RESULT if needed
			if ($Quiz->getSetting('syncuser_thirdparty_saveresult')) {
				$mergeFields['RESULT'] = $finalScore;
			}

			$api->configure('mergefields', $mergeFields);
		} 
		else if ($serviceName == 'activecampaign') 
		{
			$apiKey 		= $Quiz->getSetting('syncuser_activecampaign_apikey');
			$urlEndpoint 	= $Quiz->getSetting('syncuser_activecampaign_urlendpoint');
			$listId 		= $Quiz->getSetting('syncuser_activecampaign_listid');

			$api = new Pandore_API_Activecampaign($apiKey, $urlEndpoint);

			$mergeFields = array();
			$quizFields = $Quiz->getSetting('askinfo_fields');

			foreach($User->getMetas() as $key => $value)
			{
				$mergeName = $quizFields[$key]['wpvqgr_settings_askinfo_syncuser_mapfields'];

				if ($value['wpvqgr_user_meta_value'] == $email) {
					continue;
				}
				else if ($mergeName != '') 
				{
					// Specific field on ActiveCampaign
					if (in_array($mergeName, array('FIRSTNAME', 'LASTNAME', 'PHONE'))) {
						$api->configure($mergeName, $value['wpvqgr_user_meta_value']);
					} 
					// User's fields
					else {
						$mergeFields["field[%{$mergeName}%,0]"] = $value['wpvqgr_user_meta_value'];
					}
				}
			}

			// Save RESULT if needed
			if ($Quiz->getSetting('syncuser_thirdparty_saveresult')) {
				$mergeFields["field[%RESULT%,0]"] = $finalScore;
			}

			$api->configure('mergefields', $mergeFields);
			$api->configure('tags', $Quiz->getSetting('syncuser_activecampaign_tags'));
		} 
		else if ($serviceName == 'aweber') 
		{
			$apiKey = $Quiz->getSetting('syncuser_aweber_apikey');
			$listId = $Quiz->getSetting('syncuser_aweber_listid');
			
			$api = new Pandore_API_Aweber($apiKey);

			$mergeFields = array();
			$quizFields = $Quiz->getSetting('askinfo_fields');
			foreach($User->getMetas() as $key => $value)
			{
				$mergeName = $quizFields[$key]['wpvqgr_settings_askinfo_syncuser_mapfields'];
				if ($value['wpvqgr_user_meta_value'] == $email) {
					continue;
				}
				else if ($mergeName != '') 
				{
					$mergeFields[$mergeName] = $value['wpvqgr_user_meta_value'];
				}
			}

			// Save RESULT if needed
			if ($Quiz->getSetting('syncuser_thirdparty_saveresult')) {
				$mergeFields['RESULT'] = $finalScore;
			}

			$api->configure('custom_fields', $mergeFields);
		}
		else if ($serviceName == 'webhooks') 
		{
			$webhooks_url = $Quiz->getSetting('syncuser_webhooks_url');
			$api = new Pandore_API_Webhooks($webhooks_url);
			$api->configure('quiz', $Quiz);
			$api->configure('user', $User);
			$api->configure('final_score', $finalScore);
		}

		$status = $api->syncUser($email, $listId);
		die(json_encode($status));
	}
}

