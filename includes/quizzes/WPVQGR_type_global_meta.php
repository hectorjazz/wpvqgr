<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WPVQGR_type_global_meta
{
	function __construct()
	{
		// Global settings
		add_filter( 'wpvqgr_add_settings_general', array($this, 'add_settings_general'), 0, 1 );

		// Global settings
		add_filter( 'wpvqgr_add_settings_customlabels', array($this, 'add_settings_customlabels'), 0, 1 );

		// Virality settings
		add_filter( 'wpvqgr_add_settings_virality', array($this, 'add_settings_virality'), 0, 1 );

		// Marketing settings
		add_filter( 'wpvqgr_add_settings_marketing', array($this, 'add_settings_marketing'), 0, 1 );

		// Ads settings
		add_filter( 'wpvqgr_add_settings_ads', array($this, 'add_settings_ads'), 0, 1 );

		// Advanced settings
		add_filter( 'wpvqgr_add_settings_advanced', array($this, 'add_settings_advanced'), 0, 1 );

		// Global fields, like a new global container
		add_action( 'carbon_fields_register_fields', array($this, 'generate_fields') );
	}

	// Global container
	public function generate_fields()
	{
		// Side Informations
		$container = Container::make( 'post_meta', __("How to display the quiz?", 'wpvq') )
		    ->where( 'post_type', '=', 'wpvqgr_quiz_trivia' )
		    ->or_where( 'post_type', '=', 'wpvqgr_quiz_perso' )
		    ->set_context( 'side' )
		    ->set_priority( 'low' )
		    ->add_fields( array(
		        Field::make( 'html', 'wpvqgr_shortcodeinfo_main' )
    				->set_html( sprintf(__('<p>You can integrate the quiz wherever you want on your Wordpres site, using this simple shortcode :</p><br/><code>%s</code>', 'wpvq'), 
    					(isset($_GET['post'])) ? '[wpViralQuiz id='.intval($_GET['post']).']' : __('/!\ You must save your quiz to show its shortcode.', 'wpvq')) ),

    			Field::make( 'html', 'wpvq_shortcodeinfo_params' )
    				->set_html( __('<p>Parameters :</p>
    					 <strong>- columns</strong> : force to use X columns (between 1 and 4)', 'wpvq') ),

				Field::make( 'html', 'wpvq_shortcodeinfo_params_examples' )
    				->set_html( '<p>Example :</p> <code>[wpViralQuiz id=' . (isset($_GET['post']) ? $_GET['post']:'XX') . ' columns=3]</code>' ),
		    ));

		    // One-click post creation
		    if (isset($_GET['post']))
		    {
			    $container->add_fields(array(
			   		Field::make( 'html', 'wpvq_shortcodeinfo_quickpost' )
	    				->set_html( __('<p>Create a new quiz-post','wpvq') . '<a href="post-new.php?wpvqgr_shortcode_id='.$_GET['post'].'">' . __('within one-click', 'wpvq') .'</a>.' )
	    			));
			}
		
	}

	// Global settings in the settings panel
	public function add_settings_general($array)
	{
		$settings = array
		(
			Field::make( 'radio', 'wpvqgr_settings_skin', __('What skin do you want :', 'wpvq') )
			    ->add_options( array(
			        'buzzfeed' => __('The Buzzfeed Skin', 'wpvq'),
			        'modern' => __('The Modern Flat Skin', 'wpvq'),
			    )),

			Field::make( 'checkbox', 'wpvqgr_settings_saveanswers', __("Save every players' answers and score ?", 'wpvq') )
			->set_help_text( __('You can find all results saved in the <a href="edit.php?post_type=wpvqgr_user" target="_blank">Users submenu</a>.', 'wpvq') )
			->set_option_value( 'yes' ),

			Field::make( 'checkbox', 'wpvqgr_settings_randomquestions', __('Display Random Questions ?', 'wpvq') )
			->set_width( 50 )
			->set_option_value( 'yes' ),

    		Field::make( 'text', 'wpvqgr_settings_randomquestionscount', __('and show only XX questions (let blank for all)', 'wpvq') )
    		->set_width( 50 )
    		->set_help_text( __('For instance if you want to display 10 questions over your 100 questions', 'wpvq') )
    		->set_conditional_logic( array(
                array(
                    'field' 	=>  'wpvqgr_settings_randomquestions',
                    'value' 	=>  true,
                )
            ) ),

			Field::make( 'checkbox', 'wpvqgr_settings_autoscroll', __('Always auto-scroll to the next question', 'wpvq') )
			->set_width( 50 )
			->set_option_value( 'yes' ),

			Field::make( 'text', 'wpvqgr_settings_autoscroll_offset', __('Fix the auto-scroll with an offset (in px)', 'wpvq') )
			->set_width( 50 )
    		->set_help_text( __("Useful if the scroll doesn't match exactly the next question.", 'wpvq') )
    		->set_conditional_logic( array(
                array(
                    'field' 	=>  'wpvqgr_settings_autoscroll',
                    'value' 	=>  true,
                )
            ) ),


			Field::make( 'checkbox', 'wpvqgr_settings_randomanswers', __('Random order for answers ?', 'wpvq') )
			->set_option_value( 'yes' )
			->set_help_text( __('Shuffle the answers for each question', 'wpvq') ),
			
			Field::make( 'checkbox', 'wpvqgr_settings_startbutton', __('"Start quiz" button ?', 'wpvq') )
			->set_option_value( 'yes' )
			->set_width( 50 )
			->set_help_text( __('User need to click a button to start the quiz', 'wpvq') ),

			Field::make( 'rich_text', 'wpvqgr_settings_startbuttonintro', __('Text before the start button', 'wpvq') )
			->set_width( 50 )
			->set_conditional_logic( array(
                array(
                    'field' 	=>  'wpvqgr_settings_startbutton',
                    'value' 	=>  true,
                )
            ) ),

			Field::make( 'checkbox', 'wpvqgr_settings_playagain', __('"Play again" button ?', 'wpvq') )
			->set_option_value( 'yes' )
			->set_help_text( __('Display a "Play Again" button at the end of the quiz', 'wpvq') ),

			Field::make( 'checkbox', 'wpvqgr_settings_redirect', __('Redirect users at the end of the quiz ?', 'wpvq') )
			->set_width( 50 ),

			Field::make( 'text', 'wpvqgr_settings_redirecturl', __('Redirect to this URL :', 'wpvq') )
			->set_help_text( __("You can display quiz's results on a different page. <a href=\"https://www.ohmyquiz.io/knowledgebase/redirect-user-another-page-end-quiz/\" target=\"_blank\">Read more.</a>", 'wpvq') )
			->set_width( 50 )
			->set_conditional_logic( array(
                array(
                    'field' 	=>  'wpvqgr_settings_redirect',
                    'value' 	=>  true,
                )
            ) ),

			Field::make( 'checkbox', 'wpvqgr_settings_refresh', __('Refresh browser on page changes', 'wpvq') )
			->set_option_value( 'yes' )
			->set_help_text( __('Refresh browser when changing quiz page (cool for pageviews++)', 'wpvq') ),

			Field::make( 'set', 'wpvqgr_settings_progessbar', __('Display Progressbar', 'wpvq') )
		    ->add_options( array(
		        'top' 		=>  __('Display above the quiz', 'wpvq'),
		        'bottom' 	=>  __('Display below the quiz', 'wpvq'),
		    ) ),

		    Field::make( 'select', 'wpvqgr_settings_progessbarcontent', __('Text in the progressbar', 'wpvq') )
		    ->add_options( array(
		        'blank' 		=>  __('Leave blank', 'wpvq'),
		        'percentage' 	=>  __('Show progress percentage (ex: 70%)', 'wpvq'),
		        'counter' 		=>  __('Show progress page per page (ex: page 7/10)', 'wpvq'),
		    ) ),

		    Field::make( 'color', 'wpvqgr_settings_progessbarcolor', __('Select the color of secondary elements', 'wpvq') )
			->set_help_text( __('It means : progressbar, "start", "play again" and "continue" buttons.', 'wpvq') ),

		);

		return array_merge($array, $settings);
	}

	// Virality settings in the settings panel
	public function add_settings_customlabels($array)
	{
		$settings = array
		(
			Field::make( 'html', 'wpvqgr_settings_customlabel_intro')
			->set_width(100)
			->set_html( __('<strong>Let a field empty to use it default value, or to reset to it default value.</strong>', 'wpvq') ),

			Field::make( 'text', 'wpvqgr_settings_customlabel_startbutton', __('Text of the "Start" button :', 'wpvq'))
			->set_width(50)
			->set_help_text( __('You can enable the start button in the general tab.', 'wpvq') ),

			Field::make( 'text', 'wpvqgr_settings_customlabel_playagainbutton', __('Text of the "Play Again" button :', 'wpvq'))
			->set_width(50)
			->set_help_text( __('You can enable the play again button in the general tab.', 'wpvq') ),

			Field::make( 'text', 'wpvqgr_settings_customlabel_continuebutton', __('Text of the "Continue" button :', 'wpvq'))
			->set_width(50)
			->set_help_text( __('If your quiz uses multiple pages.', 'wpvq') ),

			Field::make( 'text', 'wpvqgr_settings_customlabel_askinfotitle', __('Title of the final form', 'wpvq'))
			->set_width(50)
			->set_help_text( __('If you ask people some info at the end, like an e-mail (in the marketing tab).', 'wpvq') ),

			Field::make( 'text', 'wpvqgr_settings_customlabel_askinfobutton', __('Text of the submit button of the final form', 'wpvq'))
			->set_width(50)
			->set_help_text( __('If you ask people some info at the end, like an e-mail.', 'wpvq') ),

			Field::make( 'text', 'wpvqgr_settings_customlabel_askinfoignore', __('Text to let people ignore the form if it\'s optional', 'wpvq'))
			->set_width(50)
			->set_help_text( __('Something like "Click here if you don\'t want to fill the form". Used only if you set up an optional form.', 'wpvq') ),
		);

		return array_merge($settings, $array);
	}

	// Virality settings in the settings panel
	public function add_settings_virality($array)
	{
		$settings = array
		(
			Field::make( 'checkbox', 'wpvqgr_settings_forcefacebook', __('People must share on Facebook to see results', 'wpvq') )
			->set_option_value( 'yes' ),

			Field::make( 'checkbox', 'wpvqgr_settings_displaysharing', __('Display share buttons at the end of the quiz', 'wpvq') )
			->set_default_value( 'yes' )
			->set_option_value( 'yes' ),

			Field::make( 'checkbox', 'wpvqgr_settings_promote', __('Show a very small label to help us to promote our plugin (thanks <3)', 'wpvq') )
			->set_option_value( 'yes' ),

			Field::make( 'text', 'wpvqgr_settings_twitterhashtag', __('Which Twitter hashtag do you want to use:', 'wpvq'))
			->set_width(50),

			Field::make( 'text', 'wpvqgr_settings_twittermention', __('Which Twitter account do you want to mention:', 'wpvq'))
			->set_width(50),
		);

		return array_merge($array, $settings);
	}

	// Marketing settings in the settings panel
	public function add_settings_marketing($array)
	{
		// Complicated and duplicated conditions array for UI
		$askinfo_conditions = array(
            array( 'field' 	=>  'wpvqgr_settings_askinfo', 'value' 	=>  true, )
        );

		$mailchimp_conditions = array(
			'relation' => 'AND',
            array( 'field' 	=>  'wpvqgr_settings_askinfo', 'value' =>  true, ), 
            array( 'field' 	=>  'wpvqgr_settings_syncuser', 'value' =>  'mailchimp', ),
        );

        $aweber_conditions = array(
			'relation' => 'AND',
            array( 'field' 	=>  'wpvqgr_settings_askinfo', 'value' =>  true, ), 
            array( 'field' 	=>  'wpvqgr_settings_syncuser', 'value' =>  'aweber', ),
        );

        $activecampaign_conditions = array(
			'relation' => 'AND',
            array( 'field' 	=>  'wpvqgr_settings_askinfo', 'value' =>  true, ), 
            array( 'field' 	=>  'wpvqgr_settings_syncuser', 'value' =>  'activecampaign', ),
        );

        $webhooks_conditions = array(
			'relation' => 'AND',
            array( 'field' 	=>  'wpvqgr_settings_askinfo', 'value' =>  true, ), 
            array( 'field' 	=>  'wpvqgr_settings_syncuser', 'value' =>  'webhooks', ),
        );

        $thirdparty_conditions = array(
        	'relation' => 'AND',
            array( 'field' 	=>  'wpvqgr_settings_askinfo', 'value' =>  true, ), 
            array( 'field' 	=>  'wpvqgr_settings_syncuser', 'value' =>  'nosync', 'compare' => '!=' ),
	    );
       

		// Generate Settings
		$settings = array
		(
			Field::make( 'checkbox', 'wpvqgr_settings_askinfo', __('Ask player informations at the end', 'wpvq') )
			->set_option_value( 'yes' )
			->set_help_text( 'Like name, e-mails, phone, ...'),

			// Field::make( 'checkbox', 'wpvqgr_settings_askinfo_localsave', __('Save form data in Wordpress database? (recommended)', 'wpvq') )
			// ->set_option_value( 'yes' )
			// ->set_default_value ( true )
			// ->set_help_text( __(__('You can find all results saved in the <a href="edit.php?post_type=wpvqgr_user" target="_blank">Users submenu</a>.', 'wpvq'), 'wpvq') )
			// ->set_conditional_logic( $askinfo_conditions ),

			Field::make( 'complex', 'wpvqgr_settings_askinfo_fields', __('What informations would you want to ask?', 'wpvq') )
			->set_layout('tabbed-horizontal')
		    ->add_fields( array(
		        Field::make( 'text', 'wpvqgr_settings_askinfo_fields_field_label', __("Field's name", 'wpvq') )
		        ->set_width( 50 ),
		        Field::make( 'select', 'wpvqgr_settings_askinfo_fields_field_type', __("Field's type", 'wpvq') )
		        ->set_width( 50 )
		        ->add_options( array(
			        'text' 		=>  __('Text', 'wpvq'),
			        'number' 	=>  __('Number', 'wpvq'),
			        'email' 	=>  __('E-mail', 'wpvq'),
			        'phone' 	=>  __('Phone number', 'wpvq'),
			    ) ),
		        
		        Field::make( 'text', 'wpvqgr_settings_askinfo_syncuser_mapfields', __("External Field's Name", 'wpvq') )
		        ->set_help_text(__('Use this only if you enable user sync below', 'wpvq'))
		        ->set_width( 50 ),

		        Field::make( 'checkbox', 'wpvqgr_settings_askinfo_fields_field_optional', __("Is this field optional?", 'wpvq') )
		        ->set_help_text(__('User can leave it empty', 'wpvq'))
		        ->set_width( 50 ),

		    ))
		    ->setup_labels( array(
		    	'plural_name' => __('Fields', 'wpvq'),
				'singular_name' => __('Field', 'wpvq'),
		    ))
		    ->set_conditional_logic( $askinfo_conditions  ),

			Field::make( 'checkbox', 'wpvqgr_settings_askinfo_ignore', __('User can ignore the form', 'wpvq') )
			->set_option_value( 'yes' )
			->set_help_text( __("They will be able to ignore the whole form, and just see their results", 'wpvq') )
			->set_conditional_logic( $askinfo_conditions ),

			// Autresponder settings
			Field::make( 'select', 'wpvqgr_settings_syncuser', __('Sync user with a 3rd party service', 'wpvq') )
			->add_options( array(
		        'nosync' 			=>  __('No', 'wpvq'),
		        'mailchimp' 		=>  'Mailchimp',
		        'aweber' 			=>  'Aweber',
		        'activecampaign' 	=>  'ActiveCampaign',
		        'webhooks' 			=>  __('Generic Webhooks / Zapier', 'wpvqgr'),
		    ))
		    ->set_conditional_logic( $askinfo_conditions ),

		    // MAILCHIMP
		    Field::make( 'html', 'wpvqgr_html_settings_syncuser_mailchimp_doc') 
            ->set_html('<div><a href="https://www.ohmyquiz.io/doc/mailchimp-sync" target="_blank">Learn to configure Mailchimp</a></div>')
			->set_conditional_logic($mailchimp_conditions),

            Field::make( 'text', 'wpvqgr_settings_syncuser_mailchimp_apikey', __('Mailchimp API Key', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($mailchimp_conditions),

            Field::make( 'text', 'wpvqgr_settings_syncuser_mailchimp_listid', __('List ID', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($mailchimp_conditions),

			Field::make( 'checkbox', 'wpvqgr_settings_syncuser_mailchimp_doubleoptin', __('Double Opt-In', 'wpvq'))
			->set_conditional_logic($mailchimp_conditions),


			// AWEBER
            Field::make( 'html', 'wpvqgr_html_settings_syncuser_aweber_doc') 
            ->set_html('
				<div id="wpvqgr-aweber-generate-auth">
					<p><strong>Step 1 :</strong> <a href="https://auth.aweber.com/1.0/oauth/authorize_app/1fc6298b" target="_blank">Click here to generate your Aweber API key</a>.</p>
					<p><strong>Step 2 :</strong> Paste the <i>Auth code</i>, and submit.</p>
					<input type="text" id="wpvqgr-aweber-generate-auth-field" name="wpvqgr-aweber-generate-auth-field" placeholder="Paste your auth code here" style="width:40%; float:left;" /> <button>Submit</button>
					<hr style="visibility:hidden; clear:both;">
					<p><strong>Step 3 :</strong> Your API key should be registred in the field below.</p>
					<p>If you need help, <a href="https://www.ohmyquiz.io/doc/aweber-sync" target="_blank">learn to configure Aweber</a></p>
				</div>')
			->set_conditional_logic($aweber_conditions),

			Field::make( 'text', 'wpvqgr_settings_syncuser_aweber_apikey', __('API Key', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($aweber_conditions),

			Field::make( 'text', 'wpvqgr_settings_syncuser_aweber_listid', __('List ID', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($aweber_conditions),

			// ACTIVECAMPAIGN
			Field::make( 'html', 'wpvqgr_html_settings_syncuser_activecampaign_doc') 
            ->set_html('<div><a href="https://www.ohmyquiz.io/doc/activecampaign-sync" target="_blank">Learn to configure ActiveCampaign</a></div>')
			->set_conditional_logic($activecampaign_conditions),

			Field::make( 'text', 'wpvqgr_settings_syncuser_activecampaign_urlendpoint', __('URL Endpoint', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($activecampaign_conditions),

			Field::make( 'text', 'wpvqgr_settings_syncuser_activecampaign_apikey', __('API Key', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($activecampaign_conditions),

			Field::make( 'text', 'wpvqgr_settings_syncuser_activecampaign_listid', __('List ID', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($activecampaign_conditions),

			Field::make( 'text', 'wpvqgr_settings_syncuser_activecampaign_tags', __('Contact tags', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($activecampaign_conditions),

			// WEBHOOKS
			Field::make( 'html', 'wpvqgr_html_settings_syncuser_webhooks_doc') 
            ->set_html('<div><a href="https://www.ohmyquiz.io/doc/zapier-sync" target="_blank">Learn how to configure Zapier (or any alternative) with webhooks.</a></div>')
			->set_conditional_logic($webhooks_conditions),

			Field::make( 'text', 'wpvqgr_settings_syncuser_webhooks_url', __('Webhook URL Endpoint', 'wpvq'))
			->set_width(50)
			->set_conditional_logic($webhooks_conditions),
		);

		return array_merge($array, $settings);
	}

	// Ads settings in the settings panel
	public function add_settings_ads($array)
	{
		$settings = array
		(
			Field::make( 'textarea', 'wpvqgr_settings_ads_before', __('Add HTML code just before the quiz', 'wpvq') ),
			Field::make( 'textarea', 'wpvqgr_settings_ads_after', __('Add HTML code just after the quiz', 'wpvq') ),
			Field::make( 'textarea', 'wpvqgr_settings_ads_aboveresults', __('Add HTML code above the result area (when a quiz is finished)', 'wpvq') ),
			Field::make( 'textarea', 'wpvqgr_settings_ads_afterresults', __('Add HTML code just after the text in the result area', 'wpvq') ),

			Field::make( 'textarea', 'wpvqgr_settings_ads_trigger_onanswer', __('Trigger this JS code each time user answers something', 'wpvq') )
			->set_help_text(__("Don't add any &lt;script>...&lt;/script> tags here, just put your JS code.", 'wpvq')),
			Field::make( 'textarea', 'wpvqgr_settings_ads_trigger_onend', __('Trigger this JS code when user ends the quiz', 'wpvq') )
			->set_help_text(__("Don't add any &lt;script>...&lt;/script> tags here, just put your JS code.", 'wpvq')),
			Field::make( 'textarea', 'wpvqgr_settings_ads_trigger_onstart', __('Trigger this JS code when user starts the quiz', 'wpvq') )
			->set_help_text(__("Don't add any &lt;script>...&lt;/script> tags here, just put your JS code.", 'wpvq')),
		);

		return array_merge($array, $settings);
	}

	// Ads settings in the settings panel
	public function add_settings_advanced($array)
	{
		$settings = array
		(
			
		);

		return array_merge($array, $settings);
	}
}