<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WPVQGR_type_draw_meta
{
	function __construct()
	{
		add_action( 'carbon_fields_register_fields', array($this, 'generate_fields') );
	}

	public function generate_fields()
	{
	    Container::make( 'post_meta', __( "Draw Information", 'wpvq' ) )
	        ->where( 'post_type', '=', 'wpvqgr_draw' )
	        ->add_fields( array(
	            Field::make( 'complex', 'wpvqgr_draw_metas', __("Draw details", 'wpvq') )
	            	->set_help_text( __('This draw has been generated when the total number of participants is reached', 'wpvq') )
	                ->setup_labels( array(
					    'plural_name' => 'data',
					    'singular_name' => 'data' ))
				    ->add_fields( array(

				        Field::make( 'text', 'wpvqgr_draw_meta_key', __( "Data key", 'wpvq' ) )->set_attribute('readOnly', true),
				        Field::make( 'text', 'wpvqgr_draw_meta_value', __( "Data value", 'wpvq' ) )
						->set_attribute('readOnly', true),
				    ))
	        ) );

	    Container::make( 'post_meta', __( "Draw Winners", 'wpvq' ) )
	        ->where( 'post_type', '=', 'wpvqgr_draw' )
	        ->add_fields( array(
	            Field::make( 'complex', 'wpvqgr_draw_winners', __("Draw's winners", 'wpvq') )
	                ->setup_labels( array(
					    'plural_name' => 'winners',
					    'singular_name' => 'winner' ))
				    ->add_fields( array(
				        Field::make( 'text', 'wpvqgr_draw_winner_name', __( "Winner Name", 'wpvq' ) )
				        ->set_attribute('readOnly', true),
				        Field::make( 'text', 'wpvqgr_draw_winner_email', __( "Winner Email", 'wpvq' ) )
				        ->set_attribute('readOnly', true),
				        Field::make( 'text', 'wpvqgr_draw_winner_order', __( "Random Selected Order", 'wpvq' ) )
				        ->set_attribute('readOnly', true),
				    ))
	        ) );
	}
}
