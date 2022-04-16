<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class WPVQGR_type_perso_meta
{
	function __construct()
	{
		add_action( 'carbon_fields_register_fields', array($this, 'generate_fields') );
	}

	public function generate_fields()
	{
	    $container = Container::make( 'post_meta', 'wpvq_quiz_perso_builder', __('Build a new Perso Quiz', 'wpvq') )
	    	->set_priority('high')
		    ->where( 'post_type', '=', 'wpvqgr_quiz_perso' )
		    ->add_tab( __('Personalities', 'wpvq'), array(
		        Field::make( 'complex', 'wpvqgr_quiz_perso_appreciations', __('Create some personalities for your quiz', 'wpvq') )
		        	->set_layout('tabbed-vertical')
				    ->add_fields( 'wpvqgr_configure_personality', __('Configure this personality', 'wpvq'), array(
				        Field::make( 'text', 'wpvqgr_quiz_appreciation_name', __('Personality name', 'wpvq') ),
				        Field::make( 'image', 'wpvqgr_quiz_appreciation_picture', __('Choose a picture for the Facebook share box', 'wpvq') )
				        ->set_value_type( 'url' )
				        ->set_width(30)
				        ->set_help_text(__('Let empty to let Facebook choose a picture for you automatically', 'wpvq')),
				        Field::make( 'rich_text', 'wpvqgr_quiz_appreciation_content', __("Personality's description :", 'wpvq') )
				        ->set_width(70),
				        Field::make( 'text', 'wpvqgr_quiz_appreciation_redirect', __('Redirect people to a specific URL at the end', 'wpvq') )
				        ->set_help_text(__("If you don't want to show people their scores, and just redirect them somewhere.", 'wpvq')),
				    ))
				    ->setup_labels( array(
				    	'plural_name' 		=>  __('Personalities', 'wpvq'),
    					'singular_name' 	=>  __('Personality', 'wpvq'),
				    ))
		        	->set_header_template("<%- (wpvqgr_quiz_appreciation_name) ? wpvqgr_quiz_appreciation_name : \"" . __('Personality', 'wpvq') . "\" %>"),
		    ) );

		/**
		 * Personalities
		 */
		
		$container->add_tab( __('Questions/answers', 'wpvq'), array(
	        Field::make( 'complex', 'wpvqgr_quiz_perso_questions', __('Create questions and answers for each question, to build a cool and viral quiz.', 'wpvq') ) 
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
			        	->set_classes('wpvqgr-handler-answer')
		                ->add_fields( $this->generateAnswersFields() )
		                ->setup_labels( array(
				    	'plural_name' => __('Answers', 'wpvq'),
    					'singular_name' => __('Answer', 'wpvq'),
				    )),

			        Field::make( 'checkbox', 'wpvqgr_quiz_questions_addpage', __("Do you want to add a page after this question ?", 'wpvq') )
		            	->set_option_value( 'yes' ),
			    ))
			    ->setup_labels( array(
			    	'plural_name' => __('Questions', 'wpvq'),
					'singular_name' => __('Question', 'wpvq'),
			    ))
			    ->set_header_template("<%- (wpvqgr_quiz_questions_content) ? wpvqgr_strip_tags(wpvqgr_quiz_questions_content, 40) : \"" . __('Question', 'wpvq') . "\" %>"),
	    ));

		Container::make( 'post_meta', __('Quiz Settings') )
			->set_priority('low')
			->where( 'post_type', '=', 'wpvqgr_quiz_perso' )
			->add_tab( __('General', 'wpvq'), apply_filters('wpvqgr_add_settings_general', array
				(
					
				)))
			->add_tab( __('Custom Labels', 'wpvq'), apply_filters('wpvqgr_add_settings_customlabels', array
				(
					
				)))
			->add_tab( __('Specific to Personality Quiz', 'wpvq'), apply_filters('wpvqgr_add_settings_trivia', array
				(
					Field::make( 'text', 'wpvqgr_settings_perso_showpersonalities', __('How many personalities displayed at the end ?', 'wpvq') )
					->set_attribute('placeholder', 1)
					->set_attribute('type', 'number')
					->set_help_text( __('(order by score descending, default is 1)', 'wpvq') ),

					Field::make( 'checkbox', 'wpvqgr_settings_forcecontinue', __('"Continue" button between pages', 'wpvq') )
					->set_option_value( 'yes' )
					->set_help_text( __('Force user to click on "Continue" between each page', 'wpvq') ),
	
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

	/**
	 * Fetch personalities (TODO : Ajax call on tab change?)
	 * @return [type] [description]
	 */
	private function getPersonalities()
	{
		/**
		 * Fill a $personalities array with 30 empty personality
		 * Why? Because CarbonFields needs to generate every fields before theme_setup.
		 * But at this moment, we don't have any information about a quiz we must load.
		 * So, we created 100 empty fields, and we hope user won't use 101 fields. -_-
		 * Crappy code, but looking for a better solution.
		 */
		
		$maxPersonalitiesFix = apply_filters('wpvqgr_increase_personalities_counter', 30);

		$personalities = [];
		for ($i=0;$i<$maxPersonalitiesFix;$i++) {
			$personalities[] = ['name' => '_empty_', 'content' => '_empty_'];
		}

		$post_id = 0;
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		    $post_id = isset( $_POST['post_ID'] ) ? intval( $_POST['post_ID'] ) : 0;
		} else {
		    $post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
		} 

		// Generate fields for core, not when loading a quiz
		if ($post_id == 0) {
			return $personalities;
		} else {
			$personalities = [];
		}

		if (is_numeric($post_id))
		{
			$quiz = new WPVQGR_Quiz();
			$quiz->load($post_id);

			$personalities = []; // not sure
			foreach($quiz->getAppreciations() as $key => $data)
			{	
				$personalities[] = [
					'name' 		=>  isset($data['name']) ? $data['name'] : '',
					'content' 	=>  isset($data['content']) ? $data['content'] : '',
				];
			}
		}
		
		return $personalities;
	}

	private function getPointsAttribution()
	{
		$pointsAttribution = array(
			'0' => '0',
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
			'7' => '7',
			'8' => '8',
			'9' => '9',
			'10' => '10',
			'-1' => '-1',
			'-2' => '-2',
			'-3' => '-3',
			'-4' => '-4',
			'-5' => '-5',
			'-6' => '-6',
			'-7' => '-7',
			'-8' => '-8',
			'-9' => '-9',
			'-10' => '-10',
		);

		return $pointsAttribution;
	}

	private function generateAnswersFields()
	{
		$personalities = $this->getPersonalities();
		$personalitiesAnswers 	= array();
		$personalitiesAnswers[] = Field::make( 'html', 'wpvqgr_quiz_questions_answers_answer_intro' )->set_html( '<span class="wpvqgr-tiny-notice">'.__('If you don\'t see all your personalities below, please <strong>save the quiz</strong> to refresh the page.', 'wpvq').'</span>' );
		$personalitiesAnswers[] = Field::make( 'image', 'wpvqgr_quiz_questions_answers_picture', 'Image' )->set_width(30);
		$personalitiesAnswers[] = Field::make( 'text', 'wpvqgr_quiz_questions_answers_answer', 'Answer' )->set_width(70);
		
		foreach ($personalities as $id => $perso)
		{
			$persoAnswer = Field::make( 'select', "wpvqgr_quiz_questions_answers_perso_$id", $perso['name'] )
				->set_help_text(substr(strip_tags($perso['content']), 0, 40) . '...')
				->set_width(33)
			    ->add_options( $this->getPointsAttribution() );

			if ($perso['name'] == '_empty_') {
				$persoAnswer->set_classes('wpvqgr-hidden-field');				
			}
			
			$personalitiesAnswers[] = $persoAnswer;

		}

		return $personalitiesAnswers;
	}

}