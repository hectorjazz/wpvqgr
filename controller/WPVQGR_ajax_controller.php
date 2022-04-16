<?php

class WPVQGR_ajax_controller
{
	private $user_id 	=  0;
	private $nb_fields 	=  0;
	private $nb_steps 	=  0;

	function __construct()
	{
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
		if ( !isset($_POST['quiz_id']) || !isset($_POST['data']) || !isset($_POST['wpvqgr_nounce']) || ! wp_verify_nonce( $_POST['wpvqgr_nounce'], 'wpvqgr_nounce' ) ) {
 			die ( 'Not authorized.');
 		}

 		// Parse data.
		$data = array();
		parse_str($_POST['data'], $data);

		if ($this->user_id == 0) {
			$this->create_user( (int)$_POST['quiz_id'] );
		}

		// Update user
		$carbon_data = array();
		foreach($data as $field_name => $field_value)
		{
			// Save data
			carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas['.$this->nb_fields.']/' . 'wpvqgr_user_meta_key', $field_name );
			carbon_set_post_meta( $this->user_id, 'wpvqgr_user_metas['.$this->nb_fields.']/' . 'wpvqgr_user_meta_value', $field_value );

			$this->nb_fields++;
		}

		// Sync session
		$this->synchronize_session('upload');

		// Return data to view
		die(json_encode(array('status' => true)));
	}

	/**
	 * Save the answer to the answers' path
	 */
	public function save_answers()
	{
		// Block bad request.
		if ( !isset($_POST['finalScore']) || !isset($_POST['quiz_questions']) || !isset($_POST['user_answers']) || !isset($_POST['wpvqgr_nounce']) || ! wp_verify_nonce( $_POST['wpvqgr_nounce'], 'wpvqgr_nounce' ) ) {
 			die ( 'Not authorized.');
 		}

 		if ($this->user_id == 0) {
			$this->create_user( (int)$_POST['quiz_id'] );
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
			$this->create_user( (int)$_POST['quiz_id'] );
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
	private function create_user($tag)
	{
		// Create the user if needed
		$this->user_id = wp_insert_post(array(
		   'post_type' 		 =>  'wpvqgr_user',
		   'post_title' 	 =>  '',
		   'post_content' 	 =>  '',
		   'post_status' 	 =>  'publish',
		   'comment_status'  =>  'closed',
		   'ping_status' 	 =>  'closed',
		));

		// Change User Title
		wp_update_post( array(
			'ID'           => $this->user_id,
			'post_title'   => 'User ' . $this->user_id,
	    ));	

	    wp_set_object_terms( $this->user_id, 'Quiz #' . $tag, 'wpvqgr_tag' );
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
        	session_start();
        }

		if ($direction == 'upload')
		{
			$_SESSION['wpvqgr']['user_id'] 		=  $this->user_id;
			$_SESSION['wpvqgr']['nb_fields'] 	=  $this->nb_fields;
			$_SESSION['wpvqgr']['nb_steps'] 	=  $this->nb_steps;
		}
		else if ($direction = 'download')
		{
			if (isset($_SESSION['wpvqgr']['user_id']) && is_numeric($_SESSION['wpvqgr']['user_id'])) {
				$this->user_id = intval($_SESSION['wpvqgr']['user_id']);
			}

			if (isset($_SESSION['wpvqgr']['nb_fields']) && is_numeric($_SESSION['wpvqgr']['nb_fields'])) {
				$this->nb_fields = intval($_SESSION['wpvqgr']['nb_fields']);
			}

			if (isset($_SESSION['wpvqgr']['nb_steps']) && is_numeric($_SESSION['wpvqgr']['nb_steps'])) {
				$this->nb_steps = intval($_SESSION['wpvqgr']['nb_steps']);
			}
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