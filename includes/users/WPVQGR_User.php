<?php

use Carbon_Fields\Field;
use Carbon_Fields\Container;

class WPVQGR_User
{
	private $user_id 	=  NULL;
	private $metas 		=  array();
	private $answers 	=  array();

	public static function register_in_draw($user_id){

		$current_draw_id = 0;
		$latest_draw = new WP_Query( array( 
			'post_type'      => 'wpvqgr_draw',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC', // in OP you're using ASC which will get earliest not latest.
			//'offset'         => 1,      // skip over the first post.
			'no_found_rows'  => true,   // optimize query since no pagination .needed.
		) );

		if($latest_draw->have_posts()){
			$current_draw_id = $latest_draw->posts[0]->ID;
		}else{
			return 0;
		}

		$args1 = array( 
			'post_type'      => 'wpvqgr_user',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'no_found_rows'  => true,   // optimize query since no pagination .needed.
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'      => '_wpvqgr_user_meta_id_value',
					'value'    =>  $user_id,
					'compare'  => '=',
				),
				array(
					'key'      => '_wpvqgr_draw_meta_id_value',
					'value'    =>  $current_draw_id,
					'compare'  => '=',
				),
			),
		);

		$take_info = new WP_Query( $args1 );
		if($take_info->have_posts()){
			$current_draw_state = get_post_meta($current_draw_id, '_wpvqgr_draw_state', true);
			if($current_draw_state == 'closed'){
				return 2;// reached limit and registered
			}else if($current_draw_state == 'open'){
				return 1;// still open and registered
			}else{
				return 0;
			}
		}
		return 0;//
	}

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