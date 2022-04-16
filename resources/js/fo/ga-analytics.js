var wpvqgr = wpvqgr || {};

(function($) 
{
	$(document).ready(function() 
	{
		wpvqgrLog('-- GANALYTICS API LOADER --');
		wpvqgr.initGAnalytics();
	});

	wpvqgr.initGAnalytics = function ()
	{
		$( document ).on( "wpvqgr-startQuiz", function(e, _wpvqgr_quiz) {
			wpvqgr.sendGaData('WPVQGR - Game', _wpvqgr_quiz.general.name, 'Started quizzes');
		});

		$( document ).on( "wpvqgr-endQuiz", function(e, _wpvqgr_quiz) {
			wpvqgr.sendGaData('WPVQGR - Game', _wpvqgr_quiz.general.name, 'Ended quizzes');
		});

		$( document ).on( "wpvqgr-facebookShare", function(e, _wpvqgr_quiz) {
			wpvqgr.sendGaData('WPVQGR - Share', _wpvqgr_quiz.general.name, 'Shared on Facebook');
		});

		$( document ).on( "wpvqgr-twitterShare", function(e, _wpvqgr_quiz) {
			wpvqgr.sendGaData('WPVQGR - Share', _wpvqgr_quiz.general.name, 'Shared on Twitter');
		});

		$( document ).on( "wpvqgr-vkShare", function(e, _wpvqgr_quiz) {
			wpvqgr.sendGaData('WPVQGR - Share', _wpvqgr_quiz.general.name, 'Shared on VK');
		});

		$( document ).on( "wpvqgr-askInfo", function(e, _wpvqgr_quiz) {
			wpvqgr.sendGaData('WPVQGR - Submit Info', _wpvqgr_quiz.general.name, 'Info submitted');
		});
	};

	/**
	 * Send data to GAnalytics, using the right way
	 * @param  {[type]} actionName [description]
	 * @param  {[type]} objectName [description]
	 * @param  {[type]} label      [description]
	 */
	wpvqgr.sendGaData = function(actionName, objectName, label)
	{
		if (typeof ga !== 'undefined') {
			ga('send', 'event', actionName, objectName, label);
			wpvqgrLog('(GAnalytics) Send GA.');
		}
		else if (typeof _gaq !== 'undefined') {
			_gaq.push(['_trackEvent', actionName, objectName, label]);
			wpvqgrLog('(GAnalytics) Send _GAQ.');
		}
		else if (typeof gtag !== 'undefined') {
			gtag('event', actionName, { objectName : label });
			wpvqgrLog('(GAnalytics) Send GTAG.');
		} else {
			wpvqgrLog('(Error) Analytics : no ga, neither _gaq found.');
		}
	};

})(jQuery);