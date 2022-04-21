<?php

/*
	Plugin Name: WP Viral Quiz (Great Reboot)
	Plugin URI: https://www.ohmyquiz.io
	Description: Create awesome and viral quizzes on your blog, as Buzzfeed does.
	Author: OhMyQuiz!
	Version: 4.06
	Author URI: https://www.ohmyquiz.io
	Text Domain: wpvq
	Domain Path: wpvq
*/

define('WPVQGR_VERSION', '4.06');
define('WPVQGR_PLUGIN_URL', plugin_dir_url( __FILE__ ));

// Tools and settings
require_once 'includes/vendor/autoload.php';
require_once 'includes/WPVQGR_Snippets.php';
require_once 'includes/WPVQGR_Settings.php';

// Quiz types
require_once 'includes/quizzes/WPVQGR_type_trivia.php';
require_once 'includes/quizzes/WPVQGR_type_perso.php';
require_once 'includes/quizzes/WPVQGR_type_global_meta.php';
require_once 'includes/quizzes/WPVQGR_type_perso_meta.php';
require_once 'includes/quizzes/WPVQGR_type_trivia_meta.php';

// Users
require_once 'includes/users/WPVQGR_type_user.php';
require_once 'includes/users/WPVQGR_type_user_meta.php';
require_once 'includes/users/WPVQGR_User.php';

// Draws
require_once 'includes/draws/WPVQGR_type_draw.php';
require_once 'includes/draws/WPVQGR_type_draw_meta.php';
require_once 'includes/draws/WPVQGR_Draw.php';

// Model
require_once 'includes/WPVQGR_Random.php';
require_once 'includes/WPVQGR_Quiz.php';
require_once 'includes/WPVQGR_Gutenberg.php';
require_once 'includes/Pandore_API_Services/Pandore_API_Services.php';

// Controller
require_once 'controller/WPVQGR_BackendCustomContent.php';
require_once 'controller/WPVQGR_BlankTemplate.php';
require_once 'controller/WPVQGR_Shortcode.php';
require_once 'controller/WPVQGR_ajax_controller.php';

// Hooks
require_once 'hooks/WPVQGR_hook_results_API_services.php';

class WPViralQuizGR {

	/**
	 * Init the plugin
	 */
	function __construct() 
	{
		// Update mechanism
		$updateChecker = Puc_v4_Factory::buildUpdateChecker('https://update.ohmyquiz.io/wpvqgr/update.php', __FILE__, 'wp-viral-quiz-gr', 24);
		$updateChecker->addQueryArgFilter(array($this, 'addSecretKeyForUpdate'));

		// Install + Uninstall
		register_activation_hook( __FILE__, array( $this, 'install' ) );	
		register_uninstall_hook( __FILE__, array( 'WPViralQuiz', 'uninstall' ) );

		// Add role and capabilities
		register_activation_hook( __FILE__, array($this, 'add_role') );
		add_action('admin_init', array($this, 'add_role_caps'),999);

		// Custom Post Type
		add_action( 'admin_menu', array($this, 'add_main_menu') );

		add_action( 'init', array('WPVQGR_type_trivia', 'create_wordpress_type'), 0);
		add_action( 'admin_menu', array('WPVQGR_type_trivia', 'add_submenu'));
		add_action( 'init', array('WPVQGR_type_perso', 'create_wordpress_type'), 0);
		add_action( 'admin_menu', array('WPVQGR_type_perso', 'add_submenu'));
		add_action( 'init', array('WPVQGR_type_user', 'create_wordpress_type'), 0);
		add_action( 'init', array('WPVQGR_type_user', 'create_wordpress_tag'), 0);
		add_action( 'admin_menu', array('WPVQGR_type_user', 'add_submenu'));

		add_action( 'init', array('WPVQGR_type_draw', 'create_wordpress_type'), 0);
		add_action( 'init', array('WPVQGR_type_draw', 'create_wordpress_tag'), 0);
		add_action( 'admin_menu', array('WPVQGR_type_draw', 'add_submenu'));


		// Custom script JS
		add_action( 'admin_enqueue_scripts', array($this, 'load_scripts_bo'));

		// Create Shortcode
		add_action( 'init', array('WPVQGR_Shortcode', 'register_scripts') );
		add_action( 'wp_footer', array('WPVQGR_Shortcode', 'print_scripts') );
		add_shortcode( 'wpViralQuiz', array('WPVQGR_Shortcode', 'print_shortcode') );
		add_shortcode( 'wpViralQuiz_countUsers', array('WPVQGR_Shortcode', 'print_shortcode_countusers') );

	    // i18n support
    	add_action( 'plugins_loaded', array($this, 'load_textdomain') );

    	// Image Size for Answer
    	add_action( 'after_setup_theme', array($this, 'create_thumbnail') );

    	// Create a sharing page for Facebook
		if ( isset($_GET['wpvqgrogtags']) ) {
			add_action( 'template_redirect', array( 'WPVQGR_Shortcode', 'generate_ogtags_page' ) );
		}

    	// Controller and session
    	add_action( 'after_setup_theme', array($this, 'init_controllers') );
    	add_action( 'template_redirect', array($this, 'reset_session') );

		// Create new post with quiz shortcode
		add_filter( 'default_content', array($this, 'default_editor_content_shortcode') );

		// Add custom content to edit.php pages
		add_filter( 'admin_notices', array('WPVQGR_BackendCustomContent', 'printBannerAds'), 999 );
	}

	/**
	 * The Welcome page and the menu
	 */
	public function add_main_menu()
	{
	    add_menu_page(
	        'WP Viral Quiz',
	        'WP Viral Quiz',
	        'read',
	        'wpvqgr-main',
	        array('WPVQGR_BackendCustomContent', 'printWelcomePage'),
	        'dashicons-chart-line',
	        null
	    );
	}	

	// Add role
	public function add_role() 
	{
		add_role('wpvqgr_manager',
	        'Quiz Manager',
	        array(
	            'read' => true,
	            'edit_posts' => false,
	            'delete_posts' => false,
	            'publish_posts' => false,
	            'upload_files' => true,
	        )
	    );
	}

	/**
	 * Add role caps
	 */
	function add_role_caps() 
	{
		// Add the roles you'd like to administer the custom post types
		$roles = array('wpvqgr_manager','editor','administrator');
		
		// Loop through each role and assign capabilities
		foreach($roles as $the_role) 
		{
		     $role = get_role($the_role);

			if (!is_object($role)) {
				continue;
			}

             $role->add_cap( 'read' );
             $role->add_cap( 'read_wpvqgr_quiz');
             $role->add_cap( 'read_private_wpvqgr_quizzes' );

             $role->add_cap( 'edit_wpvqgr_quiz' );
             $role->add_cap( 'edit_wpvqgr_quizzes' );

             $role->add_cap( 'edit_others_wpvqgr_quizzes' );
             $role->add_cap( 'edit_published_wpvqgr_quizzes' );
             $role->add_cap( 'edit_private_wpvqgr_quizzes' );

             $role->add_cap( 'publish_wpvqgr_quizzes' );

             $role->add_cap( 'delete_wpvqgr_quizzes' );
             $role->add_cap( 'delete_others_wpvqgr_quizzes' );
             $role->add_cap( 'delete_private_wpvqgr_quizzes' );
             $role->add_cap( 'delete_published_wpvqgr_quizzes' );
		}
	}

	// Create a new post with a quiz shortcode
	public function default_editor_content_shortcode( $content ) 
	{
		if (isset($_GET['wpvqgr_shortcode_id']) && is_numeric($_GET['wpvqgr_shortcode_id']))
		{
			$content = '[wpViralQuiz id=' . $_GET['wpvqgr_shortcode_id'] . ']';
			return $content;
		}
	}

	public function reset_session()
	{
		if( !session_id()) {
        	session_start();
        }

        // Reset user and random seed
        if (WPVQGR_Shortcode::getPage() == 1) {
        	unset($_SESSION['wpvqgr']);
        }
	}

	/**
	 * Param for update
	 */
	function addSecretKeyForUpdate($query) 
	{
		// TODO : key.
		$code = carbon_get_theme_option( 'wpvqgr_licencekey' );

		// >= v1.3
		$query['secret'] 	= $code;
		$query['url'] 		= get_site_url();

		return $query;
	}

	/**
	 * Create thumbnail for square answers
	 */
	public function create_thumbnail() {
		add_image_size( 'wpvqgr-square-answer', 300, 300, true);
	}
	
	/**
	 * Load stuff after setup theme
	 * @return [type] [description]
	 */
	public function init_controllers()
	{
		\Carbon_Fields\Carbon_Fields::boot();
		// define( 'Carbon_Fields\COMPACT_INPUT', true );

		// Settings
		new WPVQGR_Settings();
		// Quizzes
		new WPVQGR_type_global_meta();
		new WPVQGR_type_trivia_meta();
		new WPVQGR_type_perso_meta();
		// Users
		new WPVQGR_type_user_meta();
		// Draws
		new WPVQGR_type_draw_meta();
		// Ajax
		new WPVQGR_ajax_controller();
		// Hooks
		new WPVQGR_hook_results_API_services();
		// Add Gutenberg block
		new WPVQGR_Gutenberg();

		// Flush rules for permalink
		if (is_admin()) 
		{
		    // Flush permalink if needed
			$set = get_option( 'post_type_rules_flush_wpvqgr' );
			if ( $set !== true ){
			    flush_rewrite_rules( false );
			    update_option( 'post_type_rules_flush_wpvqgr', true );
			}
		}
	}

	/**
	 * Load plugin textdomain.
	 */
	function load_textdomain() 
	{
		$domain = 'wpvq';
    	$locale = apply_filters('plugin_locale', get_locale(), $domain);

    	load_textdomain($domain, WP_LANG_DIR.'/wp-viral-quiz-gr/'.$domain.'-'.$locale.'.mo');
    	load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}

	/**
	 * Enqueue script and CSS
	 */
	function load_scripts_bo() 
	{
		global $post_type;

    	if( strpos($post_type, 'wpvqgr') !== false)
    	{
			$screen = get_current_screen();
    		if ($screen->base == 'post')
    		{
				wp_register_script('wpvqgr-bo-script', WPVQGR_PLUGIN_URL . 'resources/js/bo-script.js', array('jquery'), WPVQGR_VERSION, true);
				wp_localize_script( 'wpvqgr-bo-script', 'wpvqgr', array('vars' => array(
					'bo_ajaxurl' 		=>  admin_url('admin-ajax.php' ),
					'bo_nounce' 		=>  wp_create_nonce('wpvqgr_bo_nounce'),
					'i18n_needSave' 	=>  __('You must save your quiz before changing tab, please.', 'wpvq'),
					'i18n_safeClose' 	=>  __('It looks like you have been editing something. If you leave before saving, your changes will be lost.', 'wpvq'),
				)));
				wp_enqueue_script('wpvqgr-bo-script');
				wp_enqueue_style( 'wpvqgr-bo-bootstrap', WPVQGR_PLUGIN_URL . 'resources/css/bootstrap-wrapper.css', false, WPVQGR_VERSION );
			}

			wp_enqueue_style( 'wpvqgr-bo-style', WPVQGR_PLUGIN_URL . 'resources/css/bo-style.css', false, WPVQGR_VERSION );
		}
	}

	/**
	 * Install the plugin DB.
	 */
	public static function install() { /* install things */ }

	/**
	 * Uninstall the plugin
	 * @return [type] [description]
	 */
	public static function uninstall() { /* uninstall things */ }

}

$WPViralQuizGR = new WPViralQuizGR();
