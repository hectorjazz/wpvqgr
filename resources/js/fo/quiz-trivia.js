var wpvqgr = wpvqgr || {};

(function($) 
{
	$(document).ready(function() 
	{
		wpvqgr.selectAnswer = function($question, question_id, $answer, answer_id)
		{
			// Don't let the user play twice the same question!
			if ($answer.hasClass('wpvqgr-disabled-answer') && !wpvqgr.vars.quiz.settings.trivia_hiderightwrong) {
				return;
			}

			// Right or wrong?
			var rightAnswers 	=  wpvqgr.getRightAnswers(question_id);
			var isRight 		=  (rightAnswers.indexOf(answer_id) > -1);

			// Store data
			var _answers = wpvqgr.getStore('answers') || { 'questions' : [] };
			_answers.questions[question_id] = { 
				'answer_id' 	: answer_id,
				'isRight' 		: isRight,
				'rightAnswers' 	: rightAnswers,
			};
			wpvqgr.setStore('answers', _answers);

			// Display Right or Wrong answers
			if (wpvqgr.vars.quiz.settings.trivia_hiderightwrong != 'yes')
			{
				// First, disable every answers
				$question.find('.wpvqgr-answer').removeClass('wpvqgr-selected-answer').addClass('wpvqgr-disabled-answer');

				// Add explanation
				var $explanation = $question.find('div.wpvqgr-explanation');
				$explanation.find('div.wpvqgr-explanation-content').html(wpvqgr.vars.quiz.questions[question_id].wpvqgr_quiz_questions_explanation);

				if (isRight) 
				{
					$explanation.find('h3.wpvqgr-thats-right').show();
					$answer.addClass('wpvqgr-right-answer');
				} 
				else 
				{
					$explanation.find('h3.wpvqgr-thats-wrong').show();
					$answer.addClass('wpvqgr-wrong-answer');
					$.each(rightAnswers, function(index, answer_id){
						$question.find('.wpvqgr-answer[data-id=' + answer_id + ']').addClass('wpvqgr-right-answer');
					});
				}

				$explanation.show();
			}
			else
			{
				$question.find('.wpvqgr-answer').removeClass('wpvqgr-selected-answer');
				$answer.addClass('wpvqgr-selected-answer');
			}

			// Update visual checkbox
			$question.find('div.wpvqgr-checkbox-picture').removeClass('wpvqgr-checkbox-checked-picture');
			$answer.find('div.wpvqgr-checkbox-picture').addClass('wpvqgr-checkbox-checked-picture');

			// Not a visual class, just marked as
			$answer.addClass('wpvqgr-is-selected-answer');
		};

		wpvqgr.computeResults = function()
		{
			var answers 	=  wpvqgr.getStore('answers') || { 'questions' : [] };
			var finalScore 	=  0;

			// Score
			$.each(answers.questions, function (q_id, answer_data) {
				if (answer_data.isRight) {
					finalScore++;
				}
			});

			// Appreciation
			var appreciation = wpvqgr.getAppreciation(finalScore);

			// Store global data for Facebook, page refresh and other stuff
			wpvqgr.setStore('finalScore', finalScore);
			wpvqgr.setStore('appreciation', appreciation);
		};

		/**
		 * Integrate results in view
		 * @return {[type]} [description]
		 */
		wpvqgr.integrateResults = function ()
		{
			wpvqgr.parseResults('quizname', wpvqgr.vars.quiz.general.name);
			wpvqgr.parseResults('score', wpvqgr.getStore('finalScore'));
			wpvqgr.parseResults('total', wpvqgr.getStore('answers').questions.length);
			wpvqgr.parseResults('description', wpvqgr.getStore('appreciation').content);
		}

		wpvqgr.getAppreciation = function(score)
		{
			var finalAppreciationId = -1;
			var finalAppreciationScoreStep = 9999;

			$.each(wpvqgr.vars.quiz.appreciations, function (ap_id, data) 
			{
				var app_score = parseInt(data.score);
				if (score <= app_score && finalAppreciationScoreStep > app_score) 
				{
					finalAppreciationScoreStep 	=  app_score;
					finalAppreciationId 		=  ap_id;
				}
			});

			// No appreciation found!
			if (finalAppreciationId == -1) {
				return { 'content':'', 'redirect':'', 'picture':'', 'score':-1 }
			} else {
				return wpvqgr.vars.quiz.appreciations[finalAppreciationId];
			}
		};

		wpvqgr.getRightAnswers = function(question_id)
		{
			var rightAnswers = [];

			var answers = wpvqgr.vars.quiz.questions[question_id]['wpvqgr_quiz_questions_answers'];
			$.each(answers, function(id, data) {
				if (data['wpvqgr_quiz_questions_answers_right']) {
					rightAnswers.push(id);
				}
			});

			return rightAnswers;
		};

		wpvqgr.getFinalScore = function()
		{
			return wpvqgr.getStore('finalScore');
		};
	});

})(jQuery);

/**
 * Store
 *
 * questions[$id] = $answer_id
 *
 *
 * 
 */