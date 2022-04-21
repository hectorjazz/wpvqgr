<?php

use Carbon_Fields\Field;
use Carbon_Fields\Container;

class WPVQGR_Draw
{
	private $draw_id 	=  NULL;
	private $metas 		=  array();
	private $winners 	=  array();

	public function getId() {
		return $this->draw_id;
	}

	public function getMetas() {
		return $this->metas;
	}

	public function getWinners() {
		return $this->winners;
	}

	// Construct
	function __construct($draw_id)
	{
		if (is_numeric($draw_id))
		{
			$this->draw_id = intval($draw_id);
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
		$this->metas = $this->get('wpvqgr_draw_metas', '');
		
		// Winners
		$this->winners = $this->get('wpvqgr_draw_winners', '');
	}

	/**
	 * Return the JSON structure of the main app
	 * @return [type] [description]
	 */
	public function generateJson()
	{
		$json = array();

		$json['draw_id'] 	=  $this->draw_id;
		$json['metas'] 		=  $this->metas;
		$json['winners']	=  $this->winners;

		return json_encode($json);
	}

	/**
	 * Try to find the winner email in the param
	 * @return [type] [description]
	 */
	public function detectEmails($returnFirst = false)
	{
		$emails = array();

		foreach($this->metas as $meta)
		{
			if( filter_var($meta['wpvqgr_draw_meta_value'], FILTER_VALIDATE_EMAIL) ) {
				$emails[] = $meta['wpvqgr_draw_meta_value'];
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
		return (($value = carbon_get_post_meta($this->draw_id, $key)) != '') ? $value : $default;
	}

}