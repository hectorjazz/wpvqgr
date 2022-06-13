<?php

class WPVQGR_ajax_controller
{
	private $user_id 	=  0;
	private $nb_fields 	=  0;
	private $nb_steps 	=  0;
	private $draw_id    =  0;
	private $draw_number    =  0;

	function __construct()
	{
		add_action( 'wp_ajax_wpvqgr_register_in_draw', array($this, 'register_in_draw') );
		add_action( 'wp_ajax_nopriv_wpvqgr_register_in_draw', array($this, 'register_in_draw') );

		add_action( 'wp_ajax_wpvqgr_check_in_draw', array($this, 'check_in_draw') );
		add_action( 'wp_ajax_nopriv_wpvqgr_check_in_draw', array($this, 'check_in_draw') );

		add_action( 'wp_ajax_wpvqgr_add_user_info', array($this, 'add_user_info') );
		add_action( 'wp_ajax_nopriv_wpvqgr_add_user_info', array($this, 'add_user_info') );

		add_action( 'wp_ajax_wpvqgr_eval_message_content', array($this, 'eval_message_content') );
		add_action( 'wp_ajax_nopriv_wpvqgr_eval_message_content', array($this, 'eval_message_content') );

		add_action( 'wp_ajax_wpvqgr_save_answers', array($this, 'save_answers') );
		add_action( 'wp_ajax_nopriv_wpvqgr_save_answers', array($this, 'save_answers') );

		add_action( 'wp_ajax_wpvqgr_end_quiz', array($this, 'end_quiz') );
		add_action( 'wp_ajax_nopriv_wpvqgr_end_quiz', array($this, 'end_quiz') );

		add_action( 'wp_ajax_wpvqgr_bo_get_aweber_auth', array($this, 'bo_get_aweber_auth') );
		add_action( 'wp_ajax_nopriv_wpvqgr_bo_get_aweber_auth', array($this, 'bo_get_aweber_auth') );

		$this->synchronize_session('download');
		$this->get_current_draw_info();
	}

	/**
	 * Get the Aweber Auth Code
	 * @return [type] [description]
	 */
	public function bo_get_aweber_auth()
	{
		// Block bad request.
		if ( !isset($_POST['auth']) || !isset($_POST['wpvqgr_bo_nounce']) || ! wp_verify_nonce( $_POST['wpvqgr_bo_nounce'], 'wpvqgr_bo_nounce' ) ) {
 			die ( 'Not authorized.');
 		}

 		// Get creds
 		$keys = Pandore_API_Aweber::getCreds($_POST['auth']);
 		die(implode('|', $keys));
	}

	/**
	 * Add user form data to WP database (just form data, not answers)
	 * Triggered on form submit if needed (no need more verification inside)
	 *
	 * $data is wpvqgr-askinfo form (email, name, whatever)
	 * 
	 * @param [type] $key   [description]
	 * @param [type] $value [description]
	 */
	public function add_user_info()
	{
		// Block bad request.
		if ( !isset($_POST['quiz_id']) || !isset($_POST['data']) || !isset($_POST['wpvqgr_nounce']) 
		//|| ! wp_verify_nonce( $_POST['wpvqgr_nounce'], 'wpvqgr_nounce' ) 
		) {
 			die ( 'Not authorized.');
 		}

		$user = wp_get_current_user();
		if(!($user && $user->ID > 0)){
			die ( 'Not authorized.');
		}

 		// Parse data.
		$data = array();
		parse_str($_POST['data'], $data);

		$this->create_user( (int)$_POST['quiz_id'] );

		// Update user
		$carbon_data = array();

		if(count($data) == 0){
			if($user){
				$data = array(
					'User Email' => $user->data->user_email,
					'User Name' => $user->data->user_nicename,
				);
			}
		}

		add_post_meta($this->user_id, "_wpvqgr_draw_meta_id_value", $this->draw_id);
		add_post_meta($this->user_id, "_wpvqgr_user_meta_id_value", $user->data->ID);
		add_post_meta($this->user_id, "_wpvqgr_draw_meta_number_value", $this->draw_number);

		carbon_set_post_meta( $this->user_id, 'wpvqgr_draw_metas[0]/' . 'wpvqgr_draw_meta_key', 'Draw Number' );
		carbon_set_post_meta( $this->user_id, 'wpvqgr_draw_metas[0]/' . 'wpvqgr_draw_meta_value', $this->draw_number );

		// $this->nb_fields = 0;
		carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas[0]/' . 'wpvqgr_user_meta_key', "User Name" );
		carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas[0]/' . 'wpvqgr_user_meta_value', $user->data->user_nicename );
		$this->nb_fields++;

		// $this->nb_fields = 1;
		carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas[1]/' . 'wpvqgr_user_meta_key', "User Email" );
		carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas[1]/' . 'wpvqgr_user_meta_value', $user->data->user_email );
		$this->nb_fields++;

		// foreach($data as $field_name => $field_value)
		// {
		// 	// Save data
		// 	carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas['.$this->nb_fields.']/' . 'wpvqgr_user_meta_key', $field_name );
		// 	carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas['.$this->nb_fields.']/' . 'wpvqgr_user_meta_value', $field_value );

		// 	$this->nb_fields++;
		// }

		// $this->add_draw_entrant();

		$this->nb_steps = 0;

		// Sync session
		$this->synchronize_session('upload');

		$draw_total_entrant_setting = 1;
		$ret0 = carbon_get_theme_option( 'wpvqgr_entrant_count');
		if($ret0 > 0) $draw_total_entrant_setting = $ret0;

		$current_draw_total_entrant = get_post_meta( $this->user_id, '_wpvqgr_draw_meta_register_order_value', true);
		carbon_set_post_meta( $this->user_id, 'wpvqgr_draw_metas[1]/' . 'wpvqgr_draw_meta_key', 'Regiser Order' );
		carbon_set_post_meta( $this->user_id, 'wpvqgr_draw_metas[1]/' . 'wpvqgr_draw_meta_value', $current_draw_total_entrant );

		if($draw_total_entrant_setting <= $current_draw_total_entrant){
			$this->draw_winner_close($current_draw_total_entrant);
		}

		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[0]/' . 'wpvqgr_draw_meta_key', 'Draw Number' );
		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[0]/' . 'wpvqgr_draw_meta_value', $this->draw_number );

		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[2]/' . 'wpvqgr_draw_meta_key', 'Current Entrant Quiz/Answer Count' );
		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[2]/' . 'wpvqgr_draw_meta_value', $current_draw_total_entrant );

		// Return data to view
		return json_encode(array('status' => true));
	}

	public function draw_winner_close($current_draw_total_entrant){
		$winner_order = rand(1 ,$current_draw_total_entrant);

		$args1 = array( 
			'post_type'      => 'wpvqgr_user',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'no_found_rows'  => true,   // optimize query since no pagination .needed.
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'      => '_wpvqgr_draw_meta_id_value',
					'value'    =>  $this->draw_id,
					'compare'  => '=',
				),
				array(
					'key'      => '_wpvqgr_draw_meta_register_order_value',
					'value'    =>  $winner_order,
					'compare'  => '=',
				),
			),
		);

		$winner_info = new WP_Query( $args1 );

		if($winner_info->have_posts()){
			$winner_name = carbon_get_post_meta($winner_info->posts[0]->ID, 'wpvqgr_user_metas[0]/' . 'wpvqgr_user_meta_value');//name
			$winner_email = carbon_get_post_meta($winner_info->posts[0]->ID, 'wpvqgr_user_metas[1]/' . 'wpvqgr_user_meta_value');//email

			carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_winners[0]/' . 'wpvqgr_draw_winner_name', $winner_name);
			carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_winners[0]/' . 'wpvqgr_draw_winner_email', $winner_email);
			carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_winners[0]/' . 'wpvqgr_draw_winner_order', $winner_order);

			$this->send_congratulation_email($winner_email, $winner_name);
		}

		//rename (remove open state)
		wp_update_post( array(
			'ID'           => $this->draw_id,
			'post_title'   => 'Draw ' . $this->draw_number,
		));	
		update_post_meta($this->draw_id, '_wpvqgr_draw_state', 'closed');
	}

	public function register_in_draw(){

		$user = wp_get_current_user();
		if(!$user || $user->ID == 0){
			die ( 'Not authorized.');
		}

		if(WPVQGR_User::check_in_draw($user->data->ID) == 1){
			die ('Already Registered!');
		}

		$draw_total_entrant_setting = 1;
		$ret0 = carbon_get_theme_option( 'wpvqgr_entrant_count');
		if($ret0 > 0) $draw_total_entrant_setting = $ret0;

		$current_draw_total_entrant = 0;
		$ret1 = carbon_get_post_meta( $this->draw_id, 'wpvqgr_draw_metas[2]/' . 'wpvqgr_draw_meta_value');
		if($ret1 > 0){
			$current_draw_total_entrant = $ret1;
		}

		if($draw_total_entrant_setting <= $current_draw_total_entrant){
			if(get_post_meta($this->draw_id, '_wpvqgr_draw_state', true) != 'closed'){
				$this->draw_winner_close($current_draw_total_entrant);
			}
			$this->create_new_draw();
			$current_draw_total_entrant = 0;
		}else if(get_post_meta($this->draw_id, '_wpvqgr_draw_state', true) == 'closed'){
			$this->create_new_draw();
			$current_draw_total_entrant = 0;
		}

		if($draw_total_entrant_setting > $current_draw_total_entrant){
			$current_draw_total_entrant  = $current_draw_total_entrant + 1;

			if($this->draw_id == 0){
				$this->create_new_draw();
			}

			$this->add_user_info();

			$current_draw_total_user = 0;
			$ret1 = carbon_get_post_meta( $this->draw_id, 'wpvqgr_draw_metas[1]/' . 'wpvqgr_draw_meta_value');
			if($ret1 > 0){
				$current_draw_total_user = $ret1;
			}
			carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[1]/' . 'wpvqgr_draw_meta_key', 'Current Entrant User Count' );
			carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[1]/' . 'wpvqgr_draw_meta_value', $current_draw_total_user + 1 );

		}
	}

	public function check_in_draw(){

		$user = wp_get_current_user();
		if(!$user || $user->ID == 0){
			die ( 'Not authorized.');
		}

		if(WPVQGR_User::check_in_draw($user->data->ID) == 1){
			die('yes');
		}
		die('no');
	}

	/**
	 * Save the answer to the answers' path
	 */
	public function save_answers()
	{
		// Block bad request.
		if ( !isset($_POST['finalScore']) || !isset($_POST['quiz_questions']) || !isset($_POST['user_answers']) 
		//|| !isset($_POST['wpvqgr_nounce']) || ! wp_verify_nonce( $_POST['wpvqgr_nounce'], 'wpvqgr_nounce' ) 
		) {
 			die ( 'Not authorized.');
 		}
		$user = wp_get_current_user();
		if(!($user && $user->ID > 0)){
			die ( 'Not authorized.');
		}

		// add check demo data
 
 		if ($this->user_id == 0) {
			$this->create_user( (int)$_POST['quiz_id'] );
			$this->add_user_info();
		}

		// Save every answers
		$user_answers 	 =  $_POST['user_answers'];
		$quiz_questions  =  $_POST['quiz_questions'];
		foreach($user_answers['questions'] as $q_id => $answer)
		{
			carbon_set_post_meta( $this->user_id, 'wpvqgr_user_answers['.$this->nb_steps.']/' . 'wpvqgr_user_answer_key', strip_tags($quiz_questions[$q_id]['wpvqgr_quiz_questions_content']) );
			carbon_set_post_meta( $this->user_id, 'wpvqgr_user_answers['.$this->nb_steps.']/' . 'wpvqgr_user_answer_value', $quiz_questions[$q_id]['wpvqgr_quiz_questions_answers'][(int)$answer['answer_id']]['wpvqgr_quiz_questions_answers_answer'] );
			$this->nb_steps++;
		}

		// Save final score
		carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas['.$this->nb_fields.']/' . 'wpvqgr_user_meta_key', 'Final Result' );
		carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas['.$this->nb_fields.']/' . 'wpvqgr_user_meta_value', $_POST['finalScore'] );
		$this->nb_fields++;
		
		// Sync session and nb_steps value
		$this->synchronize_session('upload');

		die(json_encode(array('status' => true, 'id' => $this->user_id)));
	}

	/**
	 * Save every answers
	 */
	public function end_quiz()
	{
		// Block bad request.
		if ( !isset($_POST['finalScore']) || !isset($_POST['quiz_id']) || !isset($_POST['wpvqgr_nounce']) || ! wp_verify_nonce( $_POST['wpvqgr_nounce'], 'wpvqgr_nounce' ) ) {
 			die ( 'Not authorized.');
 		}
		
		$finalScore = $_POST['finalScore'];

		$Quiz 	=  new WPVQGR_Quiz();
		$Quiz->load((int)$_POST['quiz_id']);

		$User = NULL;
		if ( $Quiz->getSetting('saveanswers') && $this->user_id == 0 ) {
			// $this->create_user( (int)$_POST['quiz_id'] );
		}

		// If user created here or at ::add_user_info
		if ($this->user_id != 0) {
			$User = new WPVQGR_User($this->user_id);
		}

		// /!\ $User can be NULL if no user created.
		do_action('wpvqgr_end_quiz', $Quiz, $User, $finalScore);
		die();
	}

	/**
	 * Create a new user
	 * @return [type] [description]
	 */
	private function create_user($tag, $register_order = "")
	{
		$user = wp_get_current_user();

		$draw_total_entrant_setting = 1;
		$ret0 = carbon_get_theme_option( 'wpvqgr_entrant_count');
		if($ret0 > 0) $draw_total_entrant_setting = $ret0;
 
		$current_draw_total_entrant = 0;
		if($register_order != ""){
			$current_draw_total_entrant = $register_order;
		}else{

			$args1 = array( 
				'post_type'      => 'wpvqgr_user',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC', // in OP you're using ASC which will get earliest not latest.
				'no_found_rows'  => true,   // optimize query since no pagination .needed.
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'      => '_wpvqgr_draw_meta_id_value',
						'value'    =>  $this->draw_id,
						'compare'  => '=',
					),
					array(
						'key'      => '_wpvqgr_draw_meta_number_value',
						'value'    =>  $this->draw_number,
						'compare'  => '=',
					),
				),
			);

			$last_answer_info = new WP_Query( $args1 );

			if($last_answer_info->have_posts()){

				$last_answer_post_id = $last_answer_info->posts[0]->ID;
				$last_register_order_value = get_post_meta($last_answer_post_id, '_wpvqgr_draw_meta_register_order_value')[0];
				$current_draw_total_entrant = $last_register_order_value;
			}

			if($draw_total_entrant_setting > $current_draw_total_entrant ){
				$current_draw_total_entrant = $current_draw_total_entrant + 1;
			}else{
				$this->create_new_draw();
				$current_draw_total_entrant = 1;
			}
		}

		$this->user_id = wp_insert_post(array(
			'post_type' 		 =>  'wpvqgr_user',
			'post_title' 	 =>  '',
			'post_content' 	 =>  '',
			'post_status' 	 =>  'publish',
			'comment_status'  =>  'closed',
			'ping_status' 	 =>  'closed',
		 ));
		add_post_meta( $this->user_id, '_wpvqgr_draw_meta_register_order_value', $current_draw_total_entrant);
		
		update_user_meta( $user->ID, '_wpvqgr_draw_mata_quiz_last_id', $this->user_id);

		$title = "User ". $this->draw_number;
		$title = $title . "-" .$current_draw_total_entrant.($user? ' ('.$user->data->user_nicename.') ' : " ");

		// Change User Title
		wp_update_post( array(
			'ID'           => $this->user_id,
			'post_title'   => $title,
	    ));

	    wp_set_object_terms( $this->user_id, 'Quiz #' . $tag, 'wpvqgr_tag1' );
	}

	private function create_new_draw(){
		$this->draw_id = wp_insert_post(array(
			'post_type' 		 =>  'wpvqgr_draw',
			'post_title' 	 =>  '',
			'post_content' 	 =>  '',
			'post_status' 	 =>  'publish',
			'comment_status'  =>  'closed',
			'ping_status' 	 =>  'closed',
		));
		add_post_meta($this->draw_id, '_wpvqgr_draw_state', 'open', true);

		$this->draw_number = intval($this->draw_number) + 1;
		add_post_meta($this->draw_id, "_wpvqgr_draw_number_value", $this->draw_number);

		wp_update_post( array(
			'ID'           => $this->draw_id,
			'post_title'   => 'Draw ' . $this->draw_number . " (Open)",
	    ));

		wp_set_object_terms( $this->draw_id, 'Draw #'.$this->draw_id, 'wpvqgr_tag2' );

		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[0]/' . 'wpvqgr_draw_meta_key', 'Draw Number' );
		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[0]/' . 'wpvqgr_draw_meta_value', $this->draw_number );
		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[1]/' . 'wpvqgr_draw_meta_key', 'Current Entrant User Count' );
		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[1]/' . 'wpvqgr_draw_meta_value', 0 );
		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[2]/' . 'wpvqgr_draw_meta_key', 'Current Entrant Quiz/Answer Count' );
		carbon_set_post_meta( $this->draw_id, 'wpvqgr_draw_metas[2]/' . 'wpvqgr_draw_meta_value', 0 );

		return $this->draw_id;
	}

	private function get_last_draw(){
		$current_draw_id = 0;
		$latest = new WP_Query( array(
			'post_type'      => 'wpvqgr_draw',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC', // in OP you're using ASC which will get earliest not latest.
			//'offset'         => 1,      // skip over the first post.
			'no_found_rows'  => true,   // optimize query since no pagination .needed.
		));

		if($latest->have_posts()){
			$current_draw_id = $latest->posts[0]->ID;
		}
		return $current_draw_id;
	}

	private function get_current_draw_info(){
		$last_draw_id = $this->get_last_draw();
		if($last_draw_id == 0){
			$this->draw_id = 0;
			$this->draw_number = 0;
			return;
		}

		$this->draw_id = $last_draw_id;
		$meta_draw_number = get_post_meta($this->draw_id, '_wpvqgr_draw_number_value');
		$this->draw_number = $meta_draw_number[0];
	}

	private function send_congratulation_email($winner_email, $winner_name){

		// $user = get_user_by('email' ,$winner_email );

		$email_subject = "";
		$email_subject = carbon_get_theme_option( 'wpvqgr_quiz_trivia_winner_email_subject');
		if($email_subject == "") $email_subject = "Quizzes Winner!";

		// $email_header = "";
		// $email_header = carbon_get_theme_option( 'wpvqgr_quiz_trivia_winner_email_header');
		// if($email_header == "") $email_header = "Trivia Quizzes Winner!";

		$email_message = "";
		$email_message = carbon_get_theme_option( 'wpvqgr_quiz_trivia_winner_email_content');
		if($email_message == "") $email_message = "Congratulations on winning the lottery.";
		// $message  = sprintf( __( 'Username: %s' ), $winner_name ) . "\r\n\r\n";
		// $message .= __( 'Congratulations on winning the lottery.' ) . "\r\n\r\n";

		$email_subject = str_replace("%%winnername%%", $winner_name, $email_subject);
		$email_subject = str_replace("%%winneremail%%", $winner_email, $email_subject);
		// $email_header = str_replace("%%winnername%%", $winner_name, $email_header);
		// $email_header = str_replace("%%winneremail%%", $winner_email, $email_header);
		$email_message = str_replace("%%winnername%%", $winner_name, $email_message);
		$email_message = str_replace("%%winneremail%%", $winner_email, $email_message);

		$wp_user_notification_email = array(
			'to'      => $winner_email,
			'subject' => $email_subject,
			'message' => $email_message,
			'headers' => "",
		);

		wp_mail(
			$wp_user_notification_email['to'],
			$wp_user_notification_email['subject'],
			$wp_user_notification_email['message'],
			$wp_user_notification_email['headers']
		);
	}

	/**
	 * Upload or download data session
	 * $direction : 
	 * 	'upload' 	=  local to session
	 * 	'download' 	=  session to local
	 * @return [type] [description]
	 */
	private function synchronize_session($direction)
	{
		if (!session_id()) {
        	// session_start(['read_and_close' => true]);
        }

		if ($direction == 'upload')
		{
			// $_SESSION['wpvqgr']['user_id'] 		=  $this->user_id;
			// $_SESSION['wpvqgr']['nb_fields'] 	=  $this->nb_fields;
			// $_SESSION['wpvqgr']['nb_steps'] 	=  $this->nb_steps;
		}
		else if ($direction = 'download')
		{
			// if (isset($_SESSION['wpvqgr']['user_id']) && is_numeric($_SESSION['wpvqgr']['user_id'])) {
			// 	$this->user_id = intval($_SESSION['wpvqgr']['user_id']);
			// }
			$user = wp_get_current_user();
			if(!($user && $user->ID > 0)){
				die();
			}

			$last_id =  get_user_meta( $user->ID, '_wpvqgr_draw_mata_quiz_last_id', true);
			if($last_id > 0)
				$this->user_id = $last_id;

			// if (isset($_SESSION['wpvqgr']['nb_fields']) && is_numeric($_SESSION['wpvqgr']['nb_fields'])) {
			// 	$this->nb_fields = intval($_SESSION['wpvqgr']['nb_fields']);
			// }

			// if (isset($_SESSION['wpvqgr']['nb_steps']) && is_numeric($_SESSION['wpvqgr']['nb_steps'])) {
			// 	$this->nb_steps = intval($_SESSION['wpvqgr']['nb_steps']);
			// }
		}
	}

	/**
	 * When a message contains a shortcode, eval and return the content
	 * @return [type] [description]
	 */
	public function eval_message_content()
	{
		// Block bad request.
		if ( !isset($_POST['message']) || !isset($_POST['wpvqgr_nounce']) || ! wp_verify_nonce( $_POST['wpvqgr_nounce'], 'wpvqgr_nounce' ) ) {
 			die ( 'Not authorized.');
 		}

 		$message = do_shortcode($_POST['message']);

 		die(stripslashes($message));
	}

}