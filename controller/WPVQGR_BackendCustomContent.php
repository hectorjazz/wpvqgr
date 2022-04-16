<?php

class WPVQGR_BackendCustomContent
{
	/**
	 * Main page of the plugin
	 * @return [type] [description]
	 */
	public static function printWelcomePage()
	{
		?>
	
		<div class="wpvqgr-wrapper">
			<div class="wpvqgr-welcome">

				<div class="container-fluid">
					<div class="header clearfix">
						<h3 class="text-muted">WP Viral Quiz</h3>
					</div>

					<div class="jumbotron">
						<h1 class="display-4">The Great Reboot.</h1>
						<p class="lead">
							To build a quiz is probably the best way to collect e-mail, engage users and grow your ads revenues. That's why we build WP Viral Quiz : an great quiz builder, focused on business.
						</p>
						<p><a class="btn btn-lg btn-success" href="https://codecanyon.net/item/wordpress-viral-quiz-buzzfeed-quiz-builder/11178623" target="_blank" role="button">Read more about WP Viral Quiz</a></p>
					</div>

					<div class="row">
						<div class="col-md-8">
							<h4>Why "Great Reboot" ?</h4>
							<p>Because I rewrite all the code from scratch, with new and more modern technologies.</p>

							<h4>Where is the documentation, and the support ?</h4>
							<p>You an find everything you need <a href="https://www.ohmyquiz.io/support/" target="_blank">right here</a>! :-)</p>

							<h4>How to create quizzes ?</h4>
							<p>Easy peasy! Find the "WP Viral Quiz" menu in the left bar of your Wordpress backoffice, and choose between "Trivia" or "Perso" quiz.</p>
						</div>
					</div>
				</div> 
			</div>
		</div>
		<?php
	}

	/**
	 * Print an information before quiz listing
	 * @param  [type] $views [description]
	 * @return [type]        [description]
	 */
	public static function printBannerAds($views)
	{
		return;

		$screen = get_current_screen();
		if ($screen->base == 'edit' && isset($_GET['post_type']) && in_array($_GET['post_type'], array('wpvqgr_quiz_trivia', 'wpvqgr_quiz_perso'))):
			?>
			<div class="notice wpvqgr-backend-iframe" style="border:0; background:none; box-shadow: none; margin-bottom:0px; padding-left:0px">
		        <iframe src="https://www.ohmyquiz.io/iframe" style="border:0; width:100%; max-width:800px; max-height:120px; overflow: hidden;" scrolling="no"></iframe>
		    </div>
			<?php
		endif;
	}
}