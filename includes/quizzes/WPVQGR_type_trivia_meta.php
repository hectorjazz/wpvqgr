<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WPVQGR_type_trivia_meta
{
	function __construct()
	{		
		add_action( 'carbon_fields_register_fields', array($this, 'generate_fields') );
	}

	public function generate_fields()
	{
	    Container::make( 'post_meta', 'wpvq_quiz_trivia_builder' , __('Build a new Trivia Quiz', 'wpvq') )
	    	->set_priority('high')
		    ->where( 'post_type', '=', 'wpvqgr_quiz_trivia' )
		    ->add_tab( __('Appreciations on results', 'wpvq'), array(
		        Field::make( 'complex', 'wpvqgr_quiz_trivia_appreciations', __('Write something to people depending on their score.', 'wpvq') )
		        	->set_layout('tabbed-vertical')
				    ->add_fields( 'wpvqgr_quiz_appreciation', __('Configure this appreciation', 'wpvq'), array(
				        Field::make( 'text', 'wpvqgr_quiz_appreciation_score', __('Display this appreciation if user gets less than ... points', 'wpvq') )
				        ->set_help_text(__('Ex: if you put 4, this appreciation will be displayed if user gets 0, 1, 2, 3 or 4 points.', 'wpvq')),
				        Field::make( 'image', 'wpvqgr_quiz_appreciation_picture', __('Choose a picture for the Facebook share box', 'wpvq') )
				        ->set_value_type( 'url' )
				        ->set_width(30)
				        ->set_help_text(__('Let empty to let Facebook choose a picture for you automatically', 'wpvq')),
				        Field::make( 'rich_text', 'wpvqgr_quiz_appreciation_content', __("Appreciation's description :", 'wpvq') )
				        ->set_help_text(__("Displayed in the result's box and, if you want, in the Facebook share box", 'wpvq'))
				        ->set_width(70),
				        Field::make( 'text', 'wpvqgr_quiz_appreciation_redirect', __('Redirect people to a specific URL at the end', 'wpvq') )
				        ->set_help_text(__("If you don't want to show people their scores, and just redirect them somewhere.", 'wpvq')),
				    ))
				    ->setup_labels( array(
				    	'plural_name' => __('Appreciations', 'wpvq'),
    					'singular_name' => __('Appreciation', 'wpvq'),
				    ))
				    ->set_header_template('<%- (wpvqgr_quiz_appreciation_score) ? "Score ≤ " + wpvqgr_quiz_appreciation_score  : "' . __('Score : ?', 'wpvq') . '" %>'),
		    ) )
		    ->add_tab( __('Questions/answers', 'wpvq'), array(
		        Field::make( 'complex', 'wpvqgr_quiz_trivia_questions', __("Create questions and answers for each question, to build a cool and viral quiz.", 'wpvq') )
		        	->set_layout('tabbed-vertical')
				    ->add_fields( 'wpvqgr_quiz_htmlblocks', __('Raw HTML Content', 'wpvq'), array
				    (
				    	Field::make( 'rich_text', 'wpvqgr_quiz_htmlblocks_content', __("Content", 'wpvq') )
				        ->set_width(100),
				         Field::make( 'checkbox', 'wpvqgr_quiz_questions_addpage', __("Do you want to add a page after this ?", 'wpvq') )
			            ->set_option_value( 'yes' ),
				    ))
				    ->add_fields( 'wpvqgr_quiz_questions', __('A new question', 'wpvq'), array
				    (
				        Field::make( 'image', 'wpvqgr_quiz_questions_picture', __("Picture", 'wpvq') )
				        ->set_width(30),
				        Field::make( 'rich_text', 'wpvqgr_quiz_questions_content', __("Content", 'wpvq') )
				        ->set_width(70),

				        Field::make( 'complex', 'wpvqgr_quiz_questions_answers', __("Answers", 'wpvq') )
			                ->add_fields( 'wpvqgr_quiz_answers', __('Configure this answer', 'wpvq'), array(
			                    Field::make( 'image', 'wpvqgr_quiz_questions_answers_picture', __('Image', 'wpvq') )
			                    ->set_width(30),
			                    Field::make( 'text', 'wpvqgr_quiz_questions_answers_answer', __('Answer', 'wpvq') )
			                    ->set_width(70),
			                    Field::make( 'checkbox', 'wpvqgr_quiz_questions_answers_right', __("This answer is the right answer", 'wpvq') )
			                    	->set_option_value( 'yes' ),
			                ))
			                ->set_classes('wpvqgr-handler-answer')
			                ->setup_labels( array(
						    	'plural_name' => __('Answers', 'wpvq'),
		    					'singular_name' => __('Answer', 'wpvq'),
						    )),
				        Field::make( 'checkbox', 'wpvqgr_quiz_questions_addpage', __("Do you want to add a page after this question ?", 'wpvq') )
			            	->set_option_value( 'yes' ),

			            Field::make( 'checkbox', 'wpvqgr_quiz_questions_explanation_status', __("Give an explaination when people answer?", 'wpvq') )
			            	->set_option_value( 'yes' ),

			            Field::make( 'rich_text', 'wpvqgr_quiz_questions_explanation', __("Write the explanation here", 'wpvq') )
			            ->set_conditional_logic( array(
			                array(
			                    'field' 	=>  'wpvqgr_quiz_questions_explanation_status',
			                    'value' 	=>  true,
			                )
			            ) ),
				    ))
				    ->setup_labels( array(
				    	'plural_name' => __('Questions', 'wpvq'),
    					'singular_name' => __('Question', 'wpvq'),
				    ))
				    ->set_header_template("<%- (wpvqgr_quiz_questions_content) ? wpvqgr_strip_tags(wpvqgr_quiz_questions_content, 40) : \"" . __('Question', 'wpvq') . "\" %>"),
		    ) );

		Container::make( 'post_meta', __('Settings') )
			->set_priority('low')
			->where( 'post_type', '=', 'wpvqgr_quiz_trivia' )
			->add_tab( __('General', 'wpvq'), apply_filters('wpvqgr_add_settings_general', array
				(
					
				)))
			->add_tab( __('Custom Labels', 'wpvq'), apply_filters('wpvqgr_add_settings_customlabels', array
				(
					Field::make( 'text', 'wpvqgr_settings_customlabel_right', __('Replace "Correct!" with something else', 'wpvq'))
					->set_width(50)
					->set_help_text( __('Leave blank to reset to default value.', 'wpvq') ),

					Field::make( 'text', 'wpvqgr_settings_customlabel_wrong', __('Replace "Wrong!" with something else', 'wpvq'))
					->set_width(50)
					->set_help_text( __('Leave blank to reset to default value.', 'wpvq') ),
				)))
			->add_tab( __('Specific to Trivia', 'wpvq'), apply_filters('wpvqgr_add_settings_trivia', array
				(
					// Field::make( 'checkbox', 'wpvqgr_settings_trivia_hiderightwrong', __('Hide right/wrong answers ?', 'wpvq') )
					// ->set_help_text( 'Hide right/wrong answsers until the end of the quiz' ),
					Field::make( 'html', 'wpvqgr_settings_trivia_specific_settinghtml')
					->set_html( __('No specific setting for now. Probably in the next update :))', 'wpvq') ),
				)))
			->add_tab( __('Virality', 'wpvq'), apply_filters('wpvqgr_add_settings_virality', array
				(

				)))
			->add_tab( __('Marketing', 'wpvq'), apply_filters('wpvqgr_add_settings_marketing', array
				(

				)))
			->add_tab( __('Ads & code', 'wpvq'), apply_filters('wpvqgr_add_settings_ads', array
				(

				)))
			->add_tab( __('Advanced', 'wpvq'), apply_filters('wpvqgr_add_settings_advanced', array
				(

				)));
	}
}