<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WPVQGR_type_user_meta
{
	function __construct()
	{
		add_action( 'carbon_fields_register_fields', array($this, 'generate_fields') );
	}

	public function generate_fields()
	{
	    Container::make( 'post_meta', __( "User's Profile", 'wpvq' ) )
	        ->where( 'post_type', '=', 'wpvqgr_user' )
	        ->add_fields( array(
	            Field::make( 'complex', 'wpvqgr_user_metas', __("All the user's inputs", 'wpvq') )
	            	->set_help_text( __('This profile has been generated when an user use one of your quizzes.', 'wpvq') )
	                ->setup_labels( array(
					    'plural_name' => 'data',
					    'singular_name' => 'data' ))
				    ->add_fields( array(
				        Field::make( 'text', 'wpvqgr_user_meta_key', __( "Data key", 'wpvq' ) ),
				        Field::make( 'text', 'wpvqgr_user_meta_value', __( "Data value", 'wpvq' ) ),
				    ))
	        ) );

	    Container::make( 'post_meta', __( "User's Answers", 'wpvq' ) )
	        ->where( 'post_type', '=', 'wpvqgr_user' )
	        ->add_fields( array(
	            Field::make( 'complex', 'wpvqgr_user_answers', __("All the user's answers", 'wpvq') )
	                ->setup_labels( array(
					    'plural_name' => 'answers',
					    'singular_name' => 'answer' ))
				    ->add_fields( array(
				        Field::make( 'text', 'wpvqgr_user_answer_key', __( "Question", 'wpvq' ) )
				        ->set_attribute('readOnly', true),
				        Field::make( 'text', 'wpvqgr_user_answer_value', __( "Answer", 'wpvq' ) )
				        ->set_attribute('readOnly', true),
				    ))
	        ) );
	}
}
