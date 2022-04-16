<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WPVQGR_Settings
{
	function __construct()
	{
	
		Container::make( 'theme_options', __('Settings', 'wpvq'))
		->set_page_parent( 'wpvqgr-main' )
		->add_tab( __('General', 'wpvq'), array
		(
			Field::make( 'text', 'wpvqgr_licencekey', __('Envato Licence Key', 'wpvq') )
			->set_help_text( __('To enable auto-update (very recommended), you have to put your Envato Purchase Code here. <a href="https://www.ohmyquiz.io/knowledgebase/enable-auto-update/" target="_blank">Click here to understand how to get your purchase code.</a>', 'wpchatbot'))
		    ->set_attribute('placeholder', 'xxxxxxx-xxxx-xxx-xxxx-xxxxxxxxxxx'),

		    Field::make( 'checkbox', 'wpvqgr_ganalytics', __('Enable Google Analytics tracking ?', 'wpvq') )
			->set_option_value( 'yes' )
			->set_help_text(__('Track players using your Google Analytics account. <a href="https://www.ohmyquiz.io/knowledgebase/google-analytics-tracking/" target="_blank">Read more.</a>', 'wpvq')),
		    
		    Field::make( 'checkbox', 'wpvqgr_gdpr_enabled', __('Enable GDPR compliance ?', 'wpvq') )
			->set_option_value( 'yes' )
			->set_help_text(__('Add a GDPR checkbox next to the form you can use to capture leads.', 'wpvq')),
			
			Field::make( 'textarea', 'wpvqgr_gdpr_message', __('Display this message next to the GDPR checkbox', 'wpvq') )
			->set_help_text(__('You can use HTML and shortcode here.', 'wpvq'))
			->set_conditional_logic( array(
                array(
                    'field' 	=>  'wpvqgr_gdpr_enabled',
                    'value' 	=>  true,
                )
            ) ),
		))
		->add_tab( __('Sharing', 'wpvq'), array
		(
			Field::make( 'set', 'wpvqgr_socialmedia_hide', __('Hide social button :', 'wpvq') )
		    ->add_options( array(
		        'facebook'  =>  'Facebook',
		        'twitter' 	=>  'Twitter',
		        'vk' 		=>  'VK',
		    )),
		    
			Field::make( 'text', 'wpvqgr_facebook_appid', __('Your Facebook App ID', 'wpvq') )
			->set_help_text(__("To enable the Facebook share button, you have to create a Facebook App with your Facebook account. Don't panic, it's VERY easy. <a href=\"https://www.ohmyquiz.io/knowledgebase/configure-facebook-share-button/\" target=\"_blank\">Click here to understand how to create a Facebook App.</a>", 'wpvq')),
		))
		->add_tab( __('Ads Manager', 'wpvq'), array
		(
			Field::make( 'html', 'wpvqgr_ads_html')
			->set_html('
				<p>'.__('You can <strong>put some ads above and below each of your quizzes</strong>. Just copy and paste the HTML code of your ads in the field below.', 'wpvq').'</p>
				<p>'.__('<strong>You can also configure specific content quiz by quiz</strong>, when building your quiz. Look for the “ads and content” settings section on the building page.', 'wpvq').'</p>
			'),

			Field::make( 'textarea', 'wpvqgr_ads_before', __('Just before each quiz', 'wpvq')),
			Field::make( 'textarea', 'wpvqgr_ads_after', __('Add ads just after the quiz', 'wpvq') ),
			Field::make( 'textarea', 'wpvqgr_ads_aboveresults', __('Above the result area (when a quiz is finished)', 'wpvq') ),
			Field::make( 'textarea', 'wpvqgr_ads_afterresults', __('Just after the text in the result area', 'wpvq') ),
			Field::make( 'text', 'wpvqgr_ads_between_count', __('Between each XXX questions', 'wpvq') )
				->set_help_text(__('Ex: if you want to display an ads every 4 questions, set 4.', 'wpvq'))
				->set_width(20),
			Field::make( 'textarea', 'wpvqgr_ads_between_content', __('Put the code of your ad.', 'wpvq') )
				->set_width(80),
		))
		->add_tab( __('Trivia Quiz', 'wpvq'), array
		(
			Field::make( 'html', 'wpvqgr_quiz_trivia_settings_html')
			->set_html('
				<h3 style="font-weight:bold;">' . __('Customize Social Media Sharebox', 'wpvq') . '</h3>
				<p style="text-align:center;padding:15px 0;"><a href="https://www.ohmyquiz.io/demo/official/wp-content/plugins/wp-viral-quiz/views/img/share-content-big.jpg" target="_blank"><img src="https://www.ohmyquiz.io/demo/official/wp-content/plugins/wp-viral-quiz/views/img/share-content-small.jpg" class="wpvq-clicktozoom"></a></p>
				<p>'.__('Configure share box content for Facebook and Twitter, when people share your quizzes. Unfortunately, <strong>Google+ does not let us customize the text</strong> when sharing.', 'wpvq') . '</p>
				<ul class="wpvq-tags-list"><li>– <strong>%%score%%</strong> :'. __('will be replaced by the final score', 'wpvq').'</li><li>– <strong>%%description%%</strong> : '.__('will be replaced by the personality description', 'wpvq').'</li><li>– <strong>%%total%%</strong> : '.__('will be replaced by the number of questions', 'wpvq').'</li><li>– <strong>%%quizname%%</strong> : '.__('will be replaced by the name of your quiz', 'wpvq').'</li></ul>
			'),

			Field::make( 'text', 'wpvqgr_quiz_trivia_template_result', __('Text on your page (below the quiz) :', 'wpvq') )
			->set_default_value(__("I got %%score%% of %%total%% right", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_trivia_template_twitter', __('Content for Twitter :', 'wpvq') )
			->set_default_value(__("I got %%score%% of %%total%% right, and you ?", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_trivia_template_facebook_title', __('Content for Facebook Title :', 'wpvq') )
			->set_default_value(__("I got %%score%% of %%total%% right, and you ?", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_trivia_template_facebook_description', __('Content for Facebook Description :', 'wpvq') )
			->set_default_value("%%description%%"),

			Field::make( 'text', 'wpvqgr_quiz_trivia_template_vk_title', __('Content for VK Title :', 'wpvq') )
			->set_default_value(__("I got %%score%% of %%total%% right, and you ?", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_trivia_template_vk_description', __('Content for VK Description :', 'wpvq') )
			->set_default_value("%%description%%"),
		))
		->add_tab( __('Personality Quiz', 'wpvq'), array
		(
			Field::make( 'html', 'wpvqgr_quiz_perso_settings_html')
			->set_html('
				<h3 style="font-weight:bold;">'.__('Customize Social Media Sharebox', 'wpvq').'</h3>
				<p style="text-align:center;padding:15px 0;"><a href="https://www.ohmyquiz.io/demo/official/wp-content/plugins/wp-viral-quiz/views/img/share-content-big.jpg" target="_blank"><img src="https://www.ohmyquiz.io/demo/official/wp-content/plugins/wp-viral-quiz/views/img/share-content-small.jpg" class="wpvq-clicktozoom"></a></p>
				<p>'.__('Configure share box content for Facebook and Twitter, when people share your quizzes. Unfortunately, <strong>Google+ does not let us customize the text</strong> when sharing.', 'wpvq').'</p>
				<ul class="wpvq-tags-list"><li>– <strong>%%personality%%</strong> : '.__('will be replaced by the final result', 'wpvq').'</li><li>– <strong>%%description%%</strong> : '.__('will be replaced by the personality description', 'wpvq').'</li><li>– <strong>%%total%%</strong> : '.__('will be replaced by the number of questions', 'wpvq').'</li><li>– <strong>%%quizname%%</strong> : '.__('will be replaced by the name of your quiz', 'wpvq').'</li><li>– <strong>%%percentage%%</strong> : '.__('will be replaced by the score percentage', 'wpvq').'</li></ul>
			'),

			Field::make( 'text', 'wpvqgr_quiz_perso_template_result', __('Text on your page (below the quiz) :', 'wpvq') )
			->set_default_value(__("I'm %%personality%%", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_perso_template_additional_results', __('Text on your page for additionals results :', 'wpvq') )
			->set_default_value(__("But I'm also %%personality%%", 'wpvq'))
			->set_help_text(__("Used when you dislay several personalities at the end of a quiz", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_perso_template_twitter', __('Content for Twitter :', 'wpvq') )
			->set_default_value(__("I'm %%personality%%, and you ?", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_perso_template_facebook_title', __('Content for Facebook Title :', 'wpvq') )
			->set_default_value(__("I'm %%personality%%, and you ?", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_perso_template_facebook_description', __('Content for Facebook Description :', 'wpvq') )
			->set_default_value("%%description%%"),

			Field::make( 'text', 'wpvqgr_quiz_perso_template_vk_title', __('Content for VK Title :', 'wpvq') )
			->set_default_value(__("I'm %%personality%%, and you ?", 'wpvq')),

			Field::make( 'text', 'wpvqgr_quiz_perso_template_vk_description', __('Content for VK Description :', 'wpvq') )
			->set_default_value("%%description%%"),
		))
		->add_tab( __('Under the hood', 'wpvq'), array
		(
			Field::make( 'textarea', 'wpvqgr_custom_css', __('Custom CSS code', 'wpvq')),
		));
		
	}
}