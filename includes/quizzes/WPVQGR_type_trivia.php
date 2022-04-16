<?php

class WPVQGR_type_trivia
{
	static public function create_wordpress_type() 
	{
		$labels = array(
			'name'                  => _x( 'Trivia Quiz', 'Post Type General Name', 'wpvqgr' ),
			'singular_name'         => _x( 'Quiz', 'Post Type Singular Name', 'wpvqgr' ),
			'menu_name'             => __( 'WP Viral Quiz', 'wpvqgr' ),
			'name_admin_bar'        => __( 'Post Type', 'wpvqgr' ),
			'archives'              => __( 'Item Archives', 'wpvqgr' ),
			'attributes'            => __( 'Item Attributes', 'wpvqgr' ),
			'parent_item_colon'     => __( 'Parent Item:', 'wpvqgr' ),
			'all_items'             => __( 'All bots', 'wpvqgr' ),
			'add_new_item'          => __( 'Create a New Quiz', 'wpvqgr' ),
			'add_new'               => __( 'Create New Quiz', 'wpvqgr' ),
			'new_item'              => __( 'New Quiz', 'wpvqgr' ),
			'edit_item'             => __( 'Edit Quiz', 'wpvqgr' ),
			'update_item'           => __( 'Update Quiz', 'wpvqgr' ),
			'view_item'             => __( 'View Item', 'wpvqgr' ),
			'view_items'            => __( 'View Items', 'wpvqgr' ),
			'search_items'          => __( 'Search Item', 'wpvqgr' ),
			'not_found'             => __( 'Not found', 'wpvqgr' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wpvqgr' ),
			'featured_image'        => __( 'Featured Image', 'wpvqgr' ),
			'set_featured_image'    => __( 'Set featured image', 'wpvqgr' ),
			'remove_featured_image' => __( 'Remove featured image', 'wpvqgr' ),
			'use_featured_image'    => __( 'Use as featured image', 'wpvqgr' ),
			'insert_into_item'      => __( 'Insert into item', 'wpvqgr' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'wpvqgr' ),
			'items_list'            => __( 'Items list', 'wpvqgr' ),
			'items_list_navigation' => __( 'Items list navigation', 'wpvqgr' ),
			'filter_items_list'     => __( 'Filter items list', 'wpvqgr' ),
		);

		$rewrite = array(
			'slug'                  => 'wpquizzes',
		);

		$args = array(
			'label'                 => __( 'Quiz', 'wpvqgr' ),
			'description'           => __( 'A quiz', 'wpvqgr' ),
			'labels'                => $labels,
			'supports'              => array( 'title', ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => false,
			'menu_position'         => 80,
			'menu_icon'             => 'dashicons-welcome-view-site',
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,		
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'rewrite'               => $rewrite,
			'capability_type'     	=> array('wpvqgr_quiz', 'wpvqgr_quizzes'),
			'map_meta_cap'        	=> true,
		);		

		register_post_type( 'wpvqgr_quiz_trivia', $args );
		add_filter( 'gettext', 'WPVQGR_type_trivia::change_publish_button', 10, 2 );
	}

	static public function change_publish_button( $translation, $text ) {
	    if ( 'wpvqgr_quiz_trivia' == get_post_type() && ($text == 'Publish' || $text == 'Update') ) {
	        return __('Save', 'wpvq');
	    } else {
	        return $translation;
	    }
	}

	/**
	 * Add to the custom post type submenu
	 */
	public static function add_submenu()
	{
		add_submenu_page( 'wpvqgr-main', __( 'Trivia Quizzes', 'wpvq' ), __( 'Trivia Quizzes', 'wpvq' ), 'read', 'edit.php?post_type=wpvqgr_quiz_trivia', NULL );
	}
}