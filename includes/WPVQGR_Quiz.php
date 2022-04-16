<?php 

class WPVQGR_Quiz {

	/**
	 * ----------------------------
	 * 		  ATTRIBUTS
	 * ----------------------------
	 */

	/**
	 * ID
	 * @var int
	 */
	protected $id;

	/**
	 * Quiz Name
	 * @var string
	 */
	protected $name;

	/**
	 * Quiz Type
	 * @var [type]
	 */
	protected $type;

	/**
	 * WP Author Id
	 * @var int
	 */
	protected $authorId;

	/**
	 * Quiz creation date
	 * @var int (timestamp UNIX)
	 */
	protected $dateCreation;


	/**
	 * Questions object array
	 */
	protected $questions;

	/**
	 * Appreciations based on score
	 */
	protected $appreciations;

	/**
	 * Quizz' settings
	 * @var array
	 */
	protected $settings;


	/**
	 * ----------------------------
	 * 		    GETTERS
	 * ----------------------------
	 */
	
	public function getSetting($key, $default = false) {
		return (isset($this->settings[$key])) ? $this->settings[$key] : $default;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getAuthorId() {
		return $this->authorId;
	}

	public function getPageCounter() {
		$count = 1;
		foreach($this->questions as $question)
		{
			if ($question['wpvqgr_quiz_questions_addpage'] == 'yes') {
				$count++;
			}
		}	

		return $count;
	}

	/**
	 * Get the WP user for $this->authorId
	 * @return Object WP_User
	 */
	public function getUser() {
		return get_user_by('id', $this->authorId);
	}

	public function getDateCreation() {
		return $this->dateCreation;
	}

	public function getQuestions() 
	{
		$questions = [];

		foreach($this->questions as $q_id => $question) {
			if ($question['_type'] == 'wpvqgr_quiz_questions') {
				$questions[] = $question;
			}
		}

		return $questions;
	}

	public function getQuestionsAndBlocks() 
	{
		return $this->questions;
	}

	public function getAppreciations() 
	{
		$appreciations = [];

		// Retro-compatibility with the old/new key of appreciation fields
		
		// 
		// Fix1:
		// 	Some plugins versions used "wpvqgr_quiz_appreciation_*", other didn't.
		//	Current: everybody without.
		//	 
		// Fix2:
		// 	Add default fields (especially "redirect") for everybody by default.
		// 	
		foreach($this->appreciations as $key => $appreciation) {

			// Fix2
			$newAppreciation = ['content'=>'', 'redirect'=>'', 'picture'=>'', 'score'=>''];

			// Fix1
			foreach($appreciation as $key => $value) {
				$newKey = str_replace('wpvqgr_quiz_appreciation_', '', $key);
				$newAppreciation[$newKey] = $value;
			}

			$appreciations[] = $newAppreciation;
		}

		return $appreciations;
	}

	public function getSettings() {
		return $this->settings;
	}

	public function getPublicSettings() 
	{
		$publicSettings = $this->settings;
		$hiddenSettings = array('syncuser_mailchimp_apikey', 'syncuser_mailchimp_listid', 'syncuser_aweber_apikey', 'syncuser_aweber_listid', 'syncuser_activecampaign_urlendpoint', 'syncuser_activecampaign_apikey', 'syncuser_activecampaign_listid', 'syncuser_activecampaign_tags', 'syncuser_webhooks_url');

		foreach($hiddenSettings as $toHide) {
			if (isset($publicSettings[$toHide])) {
				unset($publicSettings[$toHide]);
			}
		}

		return $publicSettings;
	}

	/**
	 * Return questions counter
	 * @return int
	 */
	public function countQuestions()
	{
		return count($this->questions);
	}

	/**
	 * Return appreciations counter
	 * @return int
	 */
	public function countAppreciations()
	{
		return count($this->appreciations);
	}


	/**
	 * ----------------------------
	 * 	  ABSTRACT 	METHODS
	 * ----------------------------
	 */
	
	function __construct() 
	{
		$this->id 				=  0;
		$this->name 			=  '';
		$this->type 			=  '';
		$this->authorId 		=  0;
		$this->dateCreation 	=  0;
		$this->questions 		=  array();
		$this->appreciations 	=  array();
		$this->settings 		=  array();
	}

	public function getType() {
		return $this->type;
	}

	/**
	 * Load an existing game
	 * @param  int $id Game ID
	 * @return $this
	 */
	public function load($id) 
	{
		if (!is_numeric($id)) {
			throw new Exception("Need numeric ID on load.");
		}

		$this->id 				= $id;
		$this->type 			= get_post_type($id); // wpvqgr_quiz_{perso|trivia}
		$this->name 			= get_the_title($id);
		$this->authorId 		= get_post_field($id, 'post_author');
		$this->dateCreation 	= get_the_date('U', $id);

		$this->settings = array
		(
			'skin' 					=>  $this->get('settings_skin'),
			'saveanswers' 			=>  $this->get('settings_saveanswers'),
			'randomquestions' 		=>  $this->get('settings_randomquestions'),
			'randomquestionscount' 	=>  $this->get('settings_randomquestionscount'),
			'autoscroll' 			=>  $this->get('settings_autoscroll'),
			'autoscroll_offset' 	=>  $this->get('settings_autoscroll_offset', 0),
			'randomanswers' 		=>  $this->get('settings_randomanswers'),
			'startbutton' 			=>  $this->get('settings_startbutton'),
			'startbuttonintro' 		=>  $this->get('settings_startbuttonintro'),
			'playagain' 			=>  $this->get('settings_playagain'),
			'redirect' 				=>  $this->get('settings_redirect'),
			'redirecturl' 			=>  $this->get('settings_redirecturl'),
			'refresh' 				=>  $this->get('settings_refresh'),
			'forcecontinue' 		=>  $this->get('settings_forcecontinue'),
			'progessbar' 			=>  $this->get('settings_progessbar', array()),
			'progessbarcontent' 	=>  $this->get('settings_progessbarcontent'),
			'progessbarcolor' 		=>  $this->get('settings_progessbarcolor'),

			// Custom Labels
			'customlabel_startbutton' 		=>  $this->get('settings_customlabel_startbutton', __("Start the quiz!", 'wpvq')),
			'customlabel_playagainbutton' 	=>  $this->get('settings_customlabel_playagainbutton', __("↺ &nbsp; PLAY AGAIN !", 'wpvq')),
			'customlabel_continuebutton' 	=>  $this->get('settings_customlabel_continuebutton', __("Continue >>", 'wpvq')),
			'customlabel_askinfotitle' 		=>  $this->get('settings_customlabel_askinfotitle', __("Subscribe to see your results", 'wpvq')),
			'customlabel_askinfobutton' 	=>  $this->get('settings_customlabel_askinfobutton', __('Show my results &nbsp;<i class="fa fa-arrow-right" aria-hidden="true"></i>', 'wpvq')),
			'customlabel_askinfoignore' 	=>  $this->get('settings_customlabel_askinfoignore', __('Ignore & go to results <i class="fa fa-angle-double-right" aria-hidden="true"></i>','wpvq')),
			'customlabel_right'		 		=>  $this->get('settings_customlabel_right', __('Correct!', 'wpvq')),
			'customlabel_wrong' 			=>  $this->get('settings_customlabel_wrong', __('Wrong!', 'wpvq')),

			'forcefacebook' 		=>  $this->get('settings_forcefacebook'),
			'displaysharing' 		=>  $this->get('settings_displaysharing'),
			'promote' 				=>  $this->get('settings_promote'),
			'twittermention' 		=>  str_replace('@', '', $this->get('settings_twittermention')),
			'twitterhashtag' 		=>  str_replace('#', '', $this->get('settings_twitterhashtag')),

			'askinfo' 				=>  $this->get('settings_askinfo'),
			'askinfo_localsave' 	=>  $this->get('settings_askinfo'), // askinfo = askinfo_localsave (hotfix)
			'askinfo_fields' 		=>  $this->get('settings_askinfo_fields'),
			'askinfo_ignore' 		=>  $this->get('settings_askinfo_ignore'),
			
			// Sync User Settings
			'syncuser' 									=>  $this->get('settings_syncuser', 'nosync'),
			'syncuser_mailchimp_apikey' 				=>  $this->get('settings_syncuser_mailchimp_apikey'),
			'syncuser_mailchimp_listid' 				=>  $this->get('settings_syncuser_mailchimp_listid'),
			'syncuser_mailchimp_doubleoptin' 			=>  $this->get('settings_syncuser_mailchimp_doubleoptin'),
			'syncuser_aweber_apikey' 					=>  $this->get('settings_syncuser_aweber_apikey'),
			'syncuser_aweber_listid' 					=>  $this->get('settings_syncuser_aweber_listid'),
			'syncuser_activecampaign_urlendpoint' 		=>  $this->get('settings_syncuser_activecampaign_urlendpoint'),
			'syncuser_activecampaign_apikey' 			=>  $this->get('settings_syncuser_activecampaign_apikey'),
			'syncuser_activecampaign_listid' 			=>  $this->get('settings_syncuser_activecampaign_listid'),
			'syncuser_activecampaign_tags' 				=>  $this->get('settings_syncuser_activecampaign_tags'),
			'syncuser_thirdparty_saveresult' 			=>  $this->get('settings_syncuser_thirdparty_saveresult'),
			'syncuser_webhooks_url' 					=>  $this->get('settings_syncuser_webhooks_url'),

			// Ads and code
			'ads_before' 			=>  $this->get('settings_ads_before'),
			'ads_after' 			=>  $this->get('settings_ads_after'),
			'ads_aboveresults' 		=>  $this->get('settings_ads_aboveresults'),
			'ads_afterresults' 		=>  $this->get('settings_ads_afterresults'),
			'ads_trigger_onanswer' 	=>  $this->get('settings_ads_trigger_onanswer'),
			'ads_trigger_onstart' 	=>  $this->get('settings_ads_trigger_onstart'),
			'ads_trigger_onend' 	=>  $this->get('settings_ads_trigger_onend'),

			// Trivia
			'trivia_hiderightwrong' 		=>  $this->get('settings_trivia_hiderightwrong'),
			'trivia_showpersonalities' 		=>  $this->get('settings_trivia_showpersonalities'),
			'trivia_resulttext' 			=>  $this->get('settings_trivia_resulttext'),
			'trivia_resulttexttwitter' 		=>  $this->get('settings_trivia_resulttexttwitter'),
			'trivia_resulttextfacebook' 	=>  $this->get('settings_trivia_resulttextfacebook'),
			'trivia_resulttextfacebookdesc' =>  $this->get('settings_trivia_resulttextfacebookdesc'),

			// Perso
			'perso_showpersonalities' 			=>  $this->get('settings_perso_showpersonalities', 1),
			'perso_resulttext' 					=>  $this->get('settings_perso_resulttext'),
			'perso_resulttexttwitter' 			=>  $this->get('settings_perso_resulttexttwitter'),
			'perso_resulttextfacebook' 			=>  $this->get('settings_perso_resulttextfacebook'),
			'perso_resulttextfacebookdesc' 		=>  $this->get('settings_perso_resulttextfacebookdesc'),

			// GLOBAL SETTINGS
			'global_ganalytics' 							=>  $this->getOption('ganalytics'),
			'global_socialmedia_hide' 						=>  $this->getOption('socialmedia_hide'),
			'global_facebook_appid' 						=>  $this->getOption('facebook_appid'),
			'global_gdpr_enabled' 							=>  $this->getOption('gdpr_enabled'),
			'global_gdpr_message' 							=>  $this->getOption('gdpr_message'),
			// —— Ads
			'global_ads_before' 							=>  $this->getOption('ads_before'),
			'global_ads_after' 								=>  $this->getOption('ads_after'),
			'global_ads_aboveresults' 						=>  $this->getOption('ads_aboveresults'),
			'global_ads_afterresults' 						=>  $this->getOption('ads_afterresults'),
			'global_ads_between_count' 						=>  intval($this->getOption('ads_between_count', 0)),
			'global_ads_between_content' 					=>  $this->getOption('ads_between_content'),
			// —— Text Template Sharing
			'global_template_result' 						=>  $this->getOption($this->type . '_template_result', '', false),
			'global_template_additional_results' 			=>  $this->getOption($this->type . '_template_additional_results', '', false),
			'global_template_twitter' 						=>  $this->getOption($this->type . '_template_twitter', '', false),
			'global_template_facebook_title' 				=>  $this->getOption($this->type . '_template_facebook_title', '', false),
			'global_template_facebook_description' 			=>  $this->getOption($this->type . '_template_facebook_description', '', false),
			'global_template_vk_title' 						=>  $this->getOption($this->type . '_template_vk_title', '', false),
			'global_template_vk_description' 				=>  $this->getOption($this->type . '_template_vk_description', '', false),
			// —— Under the hood
			'global_custom_css' 							=>  $this->getOption('custom_css'),
		);

		$this->appreciations 	=  $this->get($this->type . '_appreciations', [], false);
		$this->questions 		=  $this->get($this->type . '_questions', [], false);

		// Crappy cleaner to fix the CarbonFields loading problem
		// Delete all 100 empty personalities, then create a nice array for the view
		if ($this->type == 'wpvqgr_quiz_perso')
		{
			foreach($this->questions as $q_id => $question)
			{
				// Navigating between answers and html-pure-content.
				if (!isset($question['wpvqgr_quiz_questions_answers'])) {
					continue;
				}

				foreach($question['wpvqgr_quiz_questions_answers'] as $a_id => $a_data)
				{
					foreach($a_data as $key => $value)
					{
						if (strstr($key, 'wpvqgr_quiz_questions_answers_perso_') !== FALSE) 
						{
							if ($value != '') {
								$perso_id = (int) str_replace('wpvqgr_quiz_questions_answers_perso_', '', $key);
								$this->questions[$q_id]['wpvqgr_quiz_questions_answers'][$a_id]['wpvqgr_quiz_questions_answers_multipliers'][$perso_id] = $value;
							}

							unset($this->questions[$q_id]['wpvqgr_quiz_questions_answers'][$a_id][$key]);
						}
					}
				}
			}
		}

		// Randomize questions
		if ($this->settings['randomquestions']) 
		{
			$Random = new WPVQGR_Random();
			WPVQGR_Snippets::shuffle_with_seed($this->questions, $Random->getSeed());

			// Just use first X questions
			if ($this->settings['randomquestionscount'] != '' && is_numeric($this->settings['randomquestionscount'])) {
				$this->questions = array_slice($this->questions, 0, $this->settings['randomquestionscount']);
			}
		}

		// Programmatic settings
		if ($this->type == 'wpvqgr_quiz_trivia' && $this->getPageCounter() > 1) {
			$this->settings['forcecontinue'] = true;
		}

		return $this;
	}

	/**
	 * Return the JSON array for the front view
	 * @return [type] [description]
	 */
	public function getAllParameters()
	{
		$parameters['general'] = array(
			'id'			=>  $this->id,
			'name' 			=>  $this->name,
			'type' 			=>  $this->getSetting('type'),
			'namespace' 	=>  $this->type . '-' . $this->id,
		);

		$parameters['settings'] 		=  $this->getPublicSettings();
		$parameters['questions'] 		=  $this->getQuestions();
		$parameters['appreciations'] 	=  $this->getAppreciations();

		// Enable shortcode + linebreaks in appreciation
		if (isset($parameters['appreciations']))
		{
			foreach($parameters['appreciations'] as $a_id => $appreciation)
			{
				if (isset($appreciation['wpvqgr_quiz_appreciation_content'])) {
					$raw = wpautop(do_shortcode($appreciation['wpvqgr_quiz_appreciation_content']));
					$parameters['appreciations'][$a_id]['wpvqgr_quiz_appreciation_content'] = $raw;
				}	
			}
		}

		// Enable shortcode + line breaks in explanation
		if (isset($parameters['questions']))
		{
			foreach($parameters['questions'] as $q_id => $question)
			{
				if (isset($question['wpvqgr_quiz_questions_explanation'])) {
					$raw = wpautop(do_shortcode($question['wpvqgr_quiz_questions_explanation']));
					$parameters['questions'][$q_id]['wpvqgr_quiz_questions_explanation'] = $raw;
				}
			}
		}

 		return $parameters;
	}

	/**
	 * Carbon API
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	private function get($key, $default = '', $prefix = true) {
		return (($value = carbon_get_post_meta($this->id, (($prefix) ? 'wpvqgr_':'') . $key)) != '') ? $value : $default;
	}

	private function getOption($key, $default = '', $prefix = true) {
		return WPVQGR_Quiz::getThemeOption($key, $default, $prefix);
	}

	public static function getThemeOption($key, $default = '', $prefix = true) {
		return (($value = carbon_get_theme_option( (($prefix) ? 'wpvqgr_':'') . $key)) != '') ? $value : $default;
	}
}

