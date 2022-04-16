<?php

use Carbon_Fields\Field;
use Carbon_Fields\Container;

class WPVQGR_User
{
	private $user_id 	=  NULL;
	private $metas 		=  array();
	private $answers 	=  array();

	public function getId() {
		return $this->user_id;
	}

	public function getMetas() {
		return $this->metas;
	}

	public function getAnswers() {
		return $this->answers;
	}

	// Construct
	function __construct($user_id)
	{
		if (is_numeric($user_id))
		{
			$this->user_id = intval($user_id);
			$this->load();
		}
		else {
			throw new Exception("Bad ID.", 1);
		}
	}

	/**
	 * Load stuff in DB
	 * @return [type] [description]
	 */
	private function load()
	{
		// Metas
		$this->metas = $this->get('wpvqgr_user_metas', '');
		
		// Answer
		$this->answers = $this->get('wpvqgr_user_answers', '');
	}

	/**
	 * Return the JSON structure of the main app
	 * @return [type] [description]
	 */
	public function generateJson()
	{
		$json = array();

		$json['user_id'] 	=  $this->user_id;
		$json['metas'] 		=  $this->metas;
		$json['answers']	=  $this->answers;

		return json_encode($json);
	}

	/**
	 * Try to find the user email in the param
	 * @return [type] [description]
	 */
	public function detectEmails($returnFirst = false)
	{
		$emails = array();

		foreach($this->metas as $meta)
		{
			if( filter_var($meta['wpvqgr_user_meta_value'], FILTER_VALIDATE_EMAIL) ) {
				$emails[] = $meta['wpvqgr_user_meta_value'];
			}
		}

		if ($returnFirst) 
		{
			if (isset($emails[0]))
				return $emails[0];
			else
				return '';
		} 
		else 
		{
			return $emails;
		}
	}

	/**
	 * Carbon API
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	private function get($key, $default = '') {
		return (($value = carbon_get_post_meta($this->user_id, $key)) != '') ? $value : $default;
	}

}