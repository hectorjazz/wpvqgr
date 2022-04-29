<?php 

class WPVQGR_Shortcode {

	public static $isShortcodeLoaded = false;
	public static $quiz = NULL;

	/** 
	 * Shortcode display Quiz in front 
	 *
	 * @var 
	 * (int) id : quiz's id
	 *
	 * (int) columns : number of columns
	 *  
	*/
	public static function print_shortcode($param) 
	{
		// Our Global Variable for the view
		global $wpvqgr_quiz;
		global $wpvqgr_quiz_columns;
		global $wpvqgr_resources_dir_url;
		global $wpvqgr_skin_dir_url;
		global $wpvqgr_resultsOnly;

		// Is result-only-mode?
		if (isset($param['resultsonly']) && isset($_GET['wpvqgr_id']) && is_numeric($_GET['wpvqgr_id'])) {
			$wpvqgr_resultsOnly = true;
		}

		// [Classic Shortcode] Bad ID | [ResultShortocde] Bad configuration
		if ( !( (isset($param['id']) && is_numeric($param['id'])) || $wpvqgr_resultsOnly ) ) {
			return;
		}

		// Show wpvqgr_quiz only when on page
		if (!is_page() && !is_single()) {
			return;
		}

		// Load wpvqgr_quizz
		$id = intval( $wpvqgr_resultsOnly ? $_GET['wpvqgr_id'] : $param['id'] );
		try {
			$wpvqgr_quiz = new WPVQGR_Quiz();
			$wpvqgr_quiz->load($id);
			self::$quiz = $wpvqgr_quiz;
		} catch (Exception $e) {
			echo "ERROR : Quiz #{$id} doesn't exist.";
			die();
		}

		// Useful to load JS script
		self::$isShortcodeLoaded = true;

		// Columns
		$wpvqgr_quiz_columns = NULL;
		if(isset($param['columns']) && is_numeric($param['columns'])) {
			$wpvqgr_quiz_columns = $param['columns'];
		}

		// Resources URL
		$wpvqgr_resources_dir_url	=  WPVQGR_PLUGIN_URL . 'resources/';
		$wpvqgr_skin_dir_url 		=  $wpvqgr_resources_dir_url . 'css/skins/' . $wpvqgr_quiz->getSetting('skin') . '/' ;

		// View
		$shortCode = ob_start();
		include dirname(__FILE__) . '/../views/WPVQGR_Shortcode.php';
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	public static function print_shortcode_register_draw(){

		$show_register = 1;

		$user = wp_get_current_user();
		if($user && $user->ID > 0){
			$ret = WPVQGR_User::check_in_draw($user->data->ID);
			if($ret == 1){
				$show_register = 0;
			}
		}

		$output = "";
		// View
		$shortCode = ob_start();
		include dirname(__FILE__) . '/../views/WPVQGR_Shortcode_Draw_Register.php';
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/** 
	 * Shortcode display a players counter
	 *
	 * @var 
	 * (int) id : quiz's id
	 *  
	*/
	public static function print_shortcode_countusers($param) 
	{
		$count = 0;
		$addfake = 0;

		// [Classic Shortcode] Bad ID | [ResultShortocde] Bad configuration
		if ( !( (isset($param['id']) && is_numeric($param['id'])) ) ) {
			return __('You need to specify a quiz ID to display a players counter.', 'wpvqgr');
		}

		// Fetch tag
		$tag = get_term_by('slug', 'quiz-' . $param['id'], 'wpvqgr_tag1');
		if ($tag) {
			$count = $tag->count;
		}

		// Add fake parameter
		if ( isset($param['addfake']) && is_numeric($param['addfake']) ) {
			$count += intval($param['addfake']);
		}

		return $count;
	}

	/**
	 * Generate a blank page with right Facebook ogtags value
	 * @return [type] [description]
	 */
	public static function generate_ogtags_page()
	{
		$data = array_map( 'urldecode', $_GET );

		// Fetch nice data
		$url 			=  ( is_ssl() ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$quiz_url 		=  stripslashes(trim((isset($data['url'])) ? $data['url'] : ''));
		$title 			=  stripslashes(trim((isset($data['title'])) ? $data['title'] : ''));
		$description 	=  stripslashes(trim((isset($data['description'])) ? $data['description'] : ''));
		$image 			=  stripslashes(trim((isset($data['image'])) ? $data['image'] : ''));
	?>
		<html>
			<head>
				<title><?php echo $title; ?></title>

				<meta property="og:url"             content="<?php echo esc_attr( $url ); ?>" />
				<meta property="og:type"            content="article" />
				<meta property="og:title"           content="<?php echo esc_attr( $title ); ?>" />
				<meta property="og:description"     content="<?php echo esc_attr( $description ); ?>" name="description" />
				<meta property="og:image"           content="<?php echo esc_url( $image ); ?>" />
				<meta property="fb:app_id" 			content="<?php echo WPVQGR_Quiz::getThemeOption('facebook_appid') ?>">

				<?php if ($image != ''): ?>
					<?php list( $img_width, $img_height ) = getimagesize( $image ); ?>
					<?php if ( isset( $img_width ) && $img_width ) : ?>
						<meta property="og:image:width" content="<?php echo $img_width ?>">
					<?php else: ?>
						<meta property="og:image:width" content="600">
					<?php endif; ?>
					<?php if ( isset( $img_height ) && $img_height ) : ?>
						<meta property="og:image:height" content="<?php echo $img_height ?>">
					<?php else: ?>
						<meta property="og:image:height" content="400">
					<?php endif; ?>
				<?php endif; ?>

				<?php if (!WPVQGR_Snippets::isFacebookBot() && !WPVQGR_Snippets::isVKBot()): ?>
					<meta http-equiv="refresh" content="0;url=<?php echo esc_url( $quiz_url ); ?>">
				<?php endif; ?>
			</head>
		<body>
			Redirecting.
		</body>
		</html>
		<?php
		die();
	}

	/**
	 * Quiz main scripts
	 */
	public static function register_scripts() 
	{
		// Libs
		wp_register_script( 'wpvqgr-fo-ga-analytics', WPVQGR_PLUGIN_URL . 'resources/js/fo/ga-analytics.js', array('jquery'), '1.0', true );
		wp_register_script( 'wpvqgr-fo-social-media', WPVQGR_PLUGIN_URL . 'resources/js/fo/social-media.js', array('jquery'), '1.0', true );
		wp_register_script( 'wpvqgr-store2', WPVQGR_PLUGIN_URL . 'resources/components/store2/dist/store2.min.js', array('jquery'), '1.0', true );
		wp_register_script( 'wpvqgr-lodash', WPVQGR_PLUGIN_URL . 'resources/components/lodash/dist/lodash.min.js', array('jquery'), '1.0', true );
		
		// Quiz types
		wp_register_script( 'wpvqgr_quiz_trivia-script', WPVQGR_PLUGIN_URL . 'resources/js/fo/quiz-trivia.js', array('jquery'), '1.0', true );
		wp_register_script( 'wpvqgr_quiz_perso-script', WPVQGR_PLUGIN_URL . 'resources/js/fo/quiz-perso.js', array('jquery'), '1.0', true );
		
		// The same global.js file, waiting for each quiz type
		wp_register_script( 'wpvqgr_quiz_trivia-script-global', WPVQGR_PLUGIN_URL . 'resources/js/fo/global.js', array('jquery', 'wpvqgr-store2', 'wpvqgr_quiz_trivia-script', 'wpvqgr-fo-social-media'), '1.0', true );
		wp_register_script( 'wpvqgr_quiz_perso-script-global', WPVQGR_PLUGIN_URL . 'resources/js/fo/global.js', array('jquery', 'wpvqgr-store2', 'wpvqgr_quiz_perso-script', 'wpvqgr-fo-social-media'), '1.0', true );
	}

	/**
	 * Print script into the footer (if needed)
	 * @return [type] [description]
	 */
	public static function print_scripts()
	{
		if (self::$isShortcodeLoaded) 
		{	
			global $wpvqgr_resultsOnly;
			$type = self::$quiz->getType();

			// JS Storage API
			wp_enqueue_script( 'wpvqgr-store2');
			wp_enqueue_script( 'wpvqgr-lodash');

			// Social Media
			wp_enqueue_script( 'wpvqgr-fo-social-media' );

			// Global JS var
			wp_localize_script( $type . '-script-global', 'wpvqgr', array('vars' => array(
				'log' 				=>  isset($_GET['wpvqgr_debug']) ? 'on':'off',
				'ajaxurl' 			=>  admin_url('admin-ajax.php'),
				'quiz' 				=>  self::$quiz->getAllParameters(),
				'nounce' 			=>  wp_create_nonce('wpvqgr_nounce'),
				'page' 				=>  self::getPage(),
				'quiz_url' 			=>  get_permalink(),
				'next_page_url' 	=>  self::getNextPageURL(),
				'fbshare_page_url'  =>  self::getFbSharePageURL(),
				'results_only' 		=>  $wpvqgr_resultsOnly ? 'true' : 'false',
				'results_url' 		=>  self::getResultsPageURL(),
			)));

			// Enqueue main scripts
			wp_enqueue_script( $type . '-script-global' );
			wp_enqueue_script( $type . '-script' );

			// GAnalytics Tracking
			if (self::$quiz->getSetting('global_ganalytics')) {
				wp_enqueue_script( 'wpvqgr-fo-ga-analytics' );
			}
		}
	}

	/**
	 * Get the page number using Wordpress /content/page-X mechanism
	 * @return [type] [description]
	 */
	public static function getPage()
	{
		$nextPage = (int) get_query_var('page', 1);

		if ($nextPage == 0) {
			return 1;
		} else {
			return $nextPage;
		}
	}

	/**
	 * Return the next page URl (for refresh feature)
	 * @return [type] [description]
	 */
	private static function getNextPageURL()
	{
		$autoScrollAnchor = (self::$quiz->getSetting('autoscroll')) ? '#wpvqgrquestion' : '';
		return add_query_arg( array('page' => (self::getPage() + 1)) ) . $autoScrollAnchor;
	}

	/**
	 * Return the next page URl (for refresh feature)
	 * @return [type] [description]
	 */
	private static function getFbSharePageURL()
	{
		return add_query_arg( array('wpvqgrogtags' => '1'), get_permalink());
	}

	/**
	 * Return the URL of the result page (with right parameters)
	 * @return [type] [description]
	 */
	private static function getResultsPageURL()
	{
		if (self::$quiz->getSetting('redirect')) {
			$url = add_query_arg( array('wpvqgr_id' => self::$quiz->getId()), self::$quiz->getSetting('redirecturl') );	
		} else {
			$url = '';
		}		

		return $url;
	}

}