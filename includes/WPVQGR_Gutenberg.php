<?php 

use Carbon_Fields\Block;
use Carbon_Fields\Field;

class WPVQGR_Gutenberg {

	/**
	 * ----------------------------
	 * 	  ABSTRACT 	METHODS
	 * ----------------------------
	 */
	
	function __construct() 
	{
		Block::make( __( 'WP Viral Quiz' ) )
		    ->add_fields( array(
		        Field::make( 'association', 'wpvqgr_quiz_id', __( 'Select a quiz to display on this page:', 'wpvq' ) )
			        ->set_types( array(
				        array(
				            'type' => 'post',
				            'post_type' => 'wpvqgr_quiz_trivia',
				        ),
				        array(
				            'type' => 'post',
				            'post_type' => 'wpvqgr_quiz_perso',
				        ),
				    ) )
				    ->set_max( 1 )
				    ->set_min( 1 ),
		    ) )
		    ->set_description( __( 'A simple block to help you embeding your quizzes everywhere on your site.' ) )
		    ->set_category( 'common', $title = null, $icon = null )
		    ->set_icon( 'chart-line' )
		    ->set_keywords( [ __( 'viral' ), __( 'perso' ), __( 'trivia' ) ] )
		    ->set_preview_mode( false )
		    ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {

		    	if (!isset($fields['wpvqgr_quiz_id'][0]['id'])) {
		    		return;
		    	}

		    	$quizId = $fields['wpvqgr_quiz_id'][0]['id'];
				echo do_shortcode("[wpViralQuiz id={$quizId}]"); 

		    });
	}
}

