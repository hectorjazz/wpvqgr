<?php global $wpvqgr_quiz, $wpvqgr_quiz_columns, $wpvqgr_resources_dir_url, $wpvqgr_skin_dir_url; ?>

<!-- Load CSS Skin Theme -->
<style> @import url('<?php echo $wpvqgr_resources_dir_url . 'css/bootstrap-wrapper.css'; ?>'); </style>
<style> @import url('<?php echo $wpvqgr_resources_dir_url . 'icons/fa/css/font-awesome.min.css'; ?>'); </style>
<style> @import url('<?php echo $wpvqgr_resources_dir_url . 'css/fo-style.css'; ?>'); </style>
<style> @import url('<?php echo $wpvqgr_skin_dir_url . 'style.css'; ?>'); </style>

<!-- Custom style -->
<style>
	<?php if ($wpvqgr_quiz->getSetting('progessbarcolor') != ''): ?>
		.wpvqgr-wrapper button.wpvqgr-button.wpvqgr-playagain,
		.wpvqgr-wrapper button.wpvqgr-button.wpvqgr-start-button,
		.wpvqgr-wrapper div.wpvqgr-continue button.wpvqgr-button,
		.wpvqgr-wrapper button.wpvqgr-button.wpvqgr-askinfo-submit,
		.wpvqgr-progress .progress-bar {
			background-color:<?php echo $wpvqgr_quiz->getSetting('progessbarcolor'); ?>;
		}
	<?php endif; ?>

	<?php echo $wpvqgr_quiz->getSetting('global_custom_css'); ?>
</style>

<!-- Facebook SDK -->
<script type="text/javascript">
	(function(d, s, id){
		 var js, fjs = d.getElementsByTagName(s)[0];
		 if (d.getElementById(id)) {return;}
		 js = d.createElement(s); js.id = id;
		 js.src = "//connect.facebook.net/en_US/sdk.js";
		 fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
</script>
<!-- / Prepare sharing options -->
	
<?php echo apply_filters('wpvqgr_public_version', "<!--  Quiz Created with WP Viral Quiz (v".WPVQGR_VERSION.") - https://www.ohmyquiz.io/discover -->"); ?>

<a name="wpvqgr"></a>

<div class="wpvqgr-wrapper">
	<div class="container-fluid">

		<?php if ($wpvqgr_quiz->getSetting('startbutton') && !$wpvqgr_resultsOnly): ?>
			<div class="wpvqgr-intro">
				<?php if ($wpvqgr_quiz->getSetting('startbuttonintro')): ?>
					<p><?php echo $wpvqgr_quiz->getSetting('startbuttonintro'); ?></p>
				<?php endif ?>
				<button class="wpvqgr-start-button wpvqgr-button"><?php echo $wpvqgr_quiz->getSetting('customlabel_startbutton'); ?></button>
			</div>
		<?php endif ?>

		<div id="wpvqgr-<?php echo $wpvqgr_quiz->getId(); ?>" class="wpvqgr <?php echo $wpvqgr_quiz->getType(); ?>">

			<div style="display:none;" id="ajax-login-plugin-buttons">
				<div class="login-modal-ajax">
					<button class="lrm-login lrm-hide-if-logged-in" id="wpvqgr-login-user-button">Login</button>	
				</div>
				<div class="login-with-ajax">
					<?php
					echo do_shortcode('[lwa template="modal"]');	
					?>
				</div>
			</div>
			<div class="wpvqgr-a-d-s">
				<?php echo do_shortcode($wpvqgr_quiz->getSetting('global_ads_before')); ?>
				<?php echo do_shortcode($wpvqgr_quiz->getSetting('ads_before')); ?>
			</div>

			<?php if ($wpvqgr_quiz->getPageCounter() > 1 && in_array('top', $wpvqgr_quiz->getSetting('progessbar'))): ?>
				<!-- Progress bar -->
				<div class="wpvqgr-progress wpvqgr-progress-top progress">
					<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
				</div>
			<?php endif ?>

			<?php if (!$wpvqgr_resultsOnly): ?>
				<a href="" id="wpvqgrquestion"></a>
				<div class="wpvqgr-page-0 wpvqgr-page" data-id="0">

					<?php 	
						$wpvqgr_questions = $wpvqgr_quiz->getQuestionsAndBlocks();
						$q_real_id = -1;
						foreach($wpvqgr_questions as $q_id => $question):

							// Type and content
							$q_type 			=  $question['_type'];
							$q_content 			=  ($q_type == 'wpvqgr_quiz_htmlblocks') ? $question['wpvqgr_quiz_htmlblocks_content'] : $question['wpvqgr_quiz_questions_content'];

							// Real ID (ignore HTML blocks)
							if ($q_type == 'wpvqgr_quiz_questions') {
								$q_real_id++;
							}

							// Pagination
							$q_isTherePage 		=  ($question['wpvqgr_quiz_questions_addpage'] && isset($wpvqgr_questions[$q_id+1]));
							$currentPage_id 	=  (!isset($currentPage_id)) ? 0 : $currentPage_id;

							// Picture
							if ($q_type == 'wpvqgr_quiz_questions') {
								$q_picture_id		=  $question['wpvqgr_quiz_questions_picture'];
								$q_picture_info 	=  WPVQGR_Snippets::wpGetAttachment($q_picture_id);
								$q_picture_url 		=  wp_get_attachment_url($q_picture_id);
							}
					?>

						<div class="row">
							<div class="col-sm-12">

								<!-- Global ads between questions -->
								<?php if ( $q_real_id > 0 && $wpvqgr_quiz->getSetting('global_ads_between_count') > 0 && ($q_real_id % $wpvqgr_quiz->getSetting('global_ads_between_count') == 0) ): ?>
									<?php echo $wpvqgr_quiz->getSetting('global_ads_between_content'); ?>
								<?php endif; ?>
								
								<!-- HTML Blocks -->
								<?php if ($q_type == 'wpvqgr_quiz_htmlblocks'): ?>
									<div class="wpvqgr-htmlblock">
										<?php echo do_shortcode($q_content); ?>
									</div>
								<?php else: ?>
								<!-- Regular question -->
									<div class="wpvqgr-question" data-id="<?php echo $q_real_id; ?>">
										<div class="wpvqgr-question-label"><?php echo do_shortcode(nl2br($q_content)); ?></div>
										
										<?php if (is_numeric($q_picture_id)): ?>
											<div class="wpvqgr-question-picture">
												<figure class="figure">
													<img src="<?php echo $q_picture_url; ?>" class="figure-img img-fluid" alt="<?php echo htmlentities($q_picture_info['alt']); ?>" />
													<?php if ($q_picture_info['caption'] != ''): ?>
														<figcaption class="figure-caption"><?php echo $q_picture_info['caption']; ?></figcaption>
													<?php endif; ?>
												</figure>
											</div>
										<?php endif ?>
								
										<div class="row">
											<?php 
												$smartColumns = WPVQGR_Snippets::getSmartColumnsSize($question, $wpvqgr_quiz_columns);
												foreach ($question['wpvqgr_quiz_questions_answers'] as $a_id => $answer):
													// Answer
													$a_label 			=  $answer['wpvqgr_quiz_questions_answers_answer'];

													// Picture
													if ($smartColumns['displayPicture'])
													{
														$a_picture_id 	 =  $answer['wpvqgr_quiz_questions_answers_picture'];
														if ($a_picture_id == 0) {
															$a_picture_info  =  array('alt' => '', 'caption' => '');
															$a_picture_url 	 =  WPVQGR_PLUGIN_URL . '/resources/images/picture-placeholder.jpg';
														} else {
															$a_picture_info  =  WPVQGR_Snippets::wpGetAttachment($a_picture_id);
															$a_picture_url 	 =  wp_get_attachment_image_src($a_picture_id, 'wpvqgr-square-answer');
															$a_picture_url   =  $a_picture_url[0];
														}
													}
											?>
													<div class="wpvqgr-answer-col col-xs-<?php echo $smartColumns['xs-size']; ?> col-md-<?php echo $smartColumns['md-size']; ?>">
														<div class="wpvqgr-answer" data-id="<?php echo $a_id; ?>">

															<?php if ($wpvqgr_quiz->getType() == 'wpvqgr_quiz_perso'): ?>
																<!-- Multipliers -->
																<?php foreach ($answer['wpvqgr_quiz_questions_answers_multipliers'] as $p_id => $value): ?>
																	<input type="hidden" name="wpvqgr_answer_multipliers[]" data-pid="<?php echo (int)$p_id; ?>" value="<?php echo (int)$value; ?>" />
																<?php endforeach ?>
															<?php endif; ?>

															<?php if ($smartColumns['displayPicture']): ?>
																<div class="wpvqgr-answer-picture">
																	<figure class="figure">
																		<img src="<?php echo $a_picture_url; ?>" class="figure-img img-fluid" alt="<?php echo htmlentities($a_picture_info['alt']); ?>" />
																		<?php if ($a_picture_info['caption'] != ''): ?>
																			<figcaption class="figure-caption"><?php echo $a_picture_info['caption']; ?></figcaption>
																		<?php endif; ?>
																	</figure>
																</div>
															<?php endif ?>
															
															<?php if ($a_label != ''): ?>
																<div class="wpvqgr-checkbox">
																	<div class="wpvqgr-checkbox-picture wpvqgr-checkbox-unchecked-picture"></div>
																	<span class="wpvqgr-answer-label"><?php echo do_shortcode(stripslashes($a_label)); ?></span>
																	<hr class="wpvqgr-clear" />
																</div>
															<?php endif ?>
														</div>
													</div>
											<?php endforeach; ?>
										</div>

										<div class="row">
											<div class="col-sm-12">
												<div class="wpvqgr-explanation">
													<h3 class="wpvqgr-thats-right"><?php echo $wpvqgr_quiz->getSetting('customlabel_right'); ?></h3>
													<h3 class="wpvqgr-thats-wrong"><?php echo $wpvqgr_quiz->getSetting('customlabel_wrong'); ?></h3>
													<div class="wpvqgr-explanation-content"></div>
												</div>
											</div>
										</div>
									</div> <!-- .question -->
								<?php endif; ?>

							</div> <!-- / col -->
						</div> <!-- / row -->

						<div class="wpvqgr-continue">
							<button class="wpvqgr-button" style="background:<?php echo $wpvqgr_quiz->getSetting('progessbarcolor'); ?>;">
								<?php echo $wpvqgr_quiz->getSetting('customlabel_continuebutton'); ?>
							</button>
						</div>
					
						<?php 
							if ($q_isTherePage): $currentPage_id++; 
						?>
							</div> <!-- close previous page -->
							<div class="wpvqgr-page-<?php echo $currentPage_id; ?> wpvqgr-page" data-id="<?php echo $currentPage_id; ?>">
						<?php endif ?>
					<?php endforeach; ?>
				</div> <!-- Final page close -->
			<?php endif; ?>

			<a id="wpvqgr-resultarea"></a>

			<!-- Force to share -->
			<div class="row">
				<div class="col-xs-12 col-md-10 offset-md-1">
					<div class="wpvqgr-forcetoshare">
						<h3><?php echo __("Share the quiz to show your results !", 'wpvq'); ?></h3>
						<button class="wpvqgr-button wpvqgr-social-facebook wpvqgr-force-share" data-title="<?php echo $wpvqgr_quiz->getSetting('global_template_facebook_title'); ?>" data-description="<?php echo $wpvqgr_quiz->getSetting('global_template_facebook_description'); ?>"><i class="fa fa-facebook-square" aria-hidden="true"></i>&nbsp; <?php echo __('Share on Facebook', 'wpvq'); ?></button>
					</div>
				</div>
			</div>

			<!-- Force to give some informations -->
			<div class="row">
				<div class="col-xs-12 col-md-10 offset-md-1">
					<div class="wpvqgr-askinfo">
						<h3><?php echo $wpvqgr_quiz->getSetting('customlabel_askinfotitle'); ?></h3>
						<form action="" method="GET">
							<?php foreach($wpvqgr_quiz->getSetting('askinfo_fields') as $field): ?>
								<?php 
									$field_slug 	 	=  WPVQGR_Snippets::slugify($field['wpvqgr_settings_askinfo_fields_field_label']); 
									$is_required_field 	=  ($field['wpvqgr_settings_askinfo_fields_field_optional'] != 'yes');
								?>
								<div class="form-group">
									<label for="wpvqgr-<?php echo $field_slug; ?>"><?php echo $field['wpvqgr_settings_askinfo_fields_field_label']; ?></label>
									<input type="<?php echo $field['wpvqgr_settings_askinfo_fields_field_type']; ?>" class="form-control" name="<?php echo $field_slug; ?>" id="wpvqgr-<?php echo $field_slug; ?>" <?php if($is_required_field): ?>required="true"<?php endif; ?>/>
								</div>
							<?php endforeach; ?>

							<?php if ($wpvqgr_quiz->getSetting('global_gdpr_enabled') == 1): ?>
								<div class="form-check gdpr-area">
  									<label class="form-check-label">
										<input type="checkbox" class="form-check-input" name="wpvq_gpdr" id="wpvq_gpdr_checkbox" required="true" />
										<?php echo $wpvqgr_quiz->getSetting('global_gdpr_message'); ?>
									</label>
								</div>
							<?php endif ?>

							<div class="form-group" style="text-align:center;">
								<button type="submit" class="wpvqgr-button wpvqgr-askinfo-submit"><?php echo $wpvqgr_quiz->getSetting('customlabel_askinfobutton'); ?></button>
							</div>
						</form>
						
						<?php if ($wpvqgr_quiz->getSetting('askinfo_ignore')): ?>
							<p class="wpvqgr-askinfo-ignore"><?php echo $wpvqgr_quiz->getSetting('customlabel_askinfoignore'); ?></p>
						<?php endif ?>
					</div>
				</div>
			</div>

			<!-- Show results -->
			<div class="row">
				<div class="col-sm-12">
					<div class="wpvqgr-results">

						<div class="wpvqgr-a-d-s">
							<?php echo do_shortcode($wpvqgr_quiz->getSetting('global_ads_aboveresults')); ?>
							<?php echo do_shortcode($wpvqgr_quiz->getSetting('ads_aboveresults')); ?>
						</div>

						<div class="wpvqgr-results-box <?php echo $wpvqgr_quiz->getType(); ?>">
						
							<div class="wpvqgr-top-result">
								<div class="wpvqgr-quiz-name"><?php echo stripslashes($wpvqgr_quiz->getName()); ?></div>
								<h3><?php echo $wpvqgr_quiz->getSetting('global_template_result'); ?></h3>
								<div class="wpvqgr-result-description">%%description%%</div>
							</div>

							<div class="wpvqgr-top-result" style="position:relative;">
								<button id="ajax-register-draw-btn" type="button" class="wpvqgr-button wpvqgr-register-draw" style="background:#f39406;"><i class="fa fa-trophy"></i>&nbsp;Register in Draw</button>

								<div id="ajax-register-login-modal" style="display:none; border: 1px solid; border-radius: 5px; padding: 10px; position: absolute; background: white; z-index: 10; width: calc(100% - 40px); top:-30px; left:10px;">
									<div>Please login to register result in draw.</div>
									<div style="display: flex; align-items:center; justify-content: space-around;padding: 10px;width: 100%;">
										<button onclick="wpvqrg_register_draw_login()">Login</button>
										<button onclick="wpvqrg_register_draw_cancel()">Cancel</button>
									</div>
								</div>

								<script>
									jQuery("#ajax-register-draw-btn").on("click", function(){
										console.log("draw click event:");
										if(document.body.classList.contains( 'logged-in' )){
											wpvqrg_draw_result_submit();
										}else{
											jQuery("#ajax-register-login-modal").show();
										}
									});

									var register_draw_login_waiting = 0;

									function wpvqrg_register_draw_login(){
										jQuery('#ajax-login-plugin-buttons .login-with-ajax button').trigger('click');
										clearInterval(register_draw_login_waiting);
										register_draw_login_waiting = setInterval(function(){
											if(document.body.classList.contains( 'logged-in' )){
												console.log("login - success");
												clearInterval(register_draw_login_waiting);
												jQuery("#ajax-register-login-modal").hide();

												wpvqrg_draw_result_submit();
											}else{
												console.log("login - wait");
											}
										}, 500);
									}

									function wpvqrg_register_draw_cancel(){
										jQuery("#ajax-register-login-modal").hide();
										clearInterval(register_draw_login_waiting);
									}

									function wpvqrg_draw_result_submit(){

										console.log("wpvqrg_draw_result_submit:");

										var data 		=  {};
										var callback 	= function(){
											jQuery("#ajax-register-draw-btn").html('<i class="fa fa-trophy"></i>&nbsp;Registered in Draw');
											jQuery("#ajax-register-draw-btn").css('background', '#A9BBAD ');
										};

										// Trigger Custom Event
										// jQuery( document ).trigger( "wpvqgr-askInfo", [ wpvqgr.vars.quiz ] );

										// Save form data to WP database

										// if (wpvqgr.vars.quiz.settings.askinfo_localsave === true) {

											if (wpvqgr.vars.quiz.settings.saveanswers != true) {
												wpvqgr.ajaxSaveAnswers(function(){});
											}

											wpvqgr.ajaxSaveInfo(data, callback);
										// } else {
										// 	callback();
										// }
										jQuery("#ajax-register-draw-btn").attr("disabled","disabled");
									}
								</script>
							</div>

							<div class="wpvqgr-additional-results">
								<div class="wpvqgr-additional-results-template">
									<h3><?php echo $wpvqgr_quiz->getSetting('global_template_additional_results'); ?></h3>
									<div class="wpvqgr-result-description">%%description%%</div>
								</div>
							</div>

							<div class="wpvqgr-a-d-s">
								<?php echo do_shortcode($wpvqgr_quiz->getSetting('global_ads_afterresults')); ?>
								<?php echo do_shortcode($wpvqgr_quiz->getSetting('ads_afterresults')); ?>
							</div>

							<?php if ($wpvqgr_quiz->getSetting('displaysharing')): ?>
								<div class="wpvqgr-sharing">
									<?php if (!in_array('facebook', $wpvqgr_quiz->getSetting('global_socialmedia_hide'))): ?>
										<button class="wpvqgr-button wpvqgr-social-facebook" data-title="<?php echo $wpvqgr_quiz->getSetting('global_template_facebook_title'); ?>" data-description="<?php echo $wpvqgr_quiz->getSetting('global_template_facebook_description'); ?>"><i class="fa fa-facebook-square" aria-hidden="true"></i>&nbsp; <?php echo __('Share on Facebook', 'wpvq'); ?></button>
									<?php endif; ?>
									 
									<!-- Twitter -->
									<?php if (!in_array('twitter', $wpvqgr_quiz->getSetting('global_socialmedia_hide'))): ?>
										<button class="wpvqgr-button wpvqgr-social-twitter" data-tweet="<?php echo $wpvqgr_quiz->getSetting('global_template_twitter'); ?>" data-mention="<?php echo $wpvqgr_quiz->getSetting('twittermention'); ?>" data-hashtag="<?php echo $wpvqgr_quiz->getSetting('twitterhashtag'); ?>"><i class="fa fa-twitter-square" aria-hidden="true"></i>&nbsp; <?php echo __('Share on Twitter', 'wpvq'); ?></button>
									<?php endif ?>

									<!-- VK -->
									<?php if (!in_array('vk', $wpvqgr_quiz->getSetting('global_socialmedia_hide'))): ?>
										<button class="wpvqgr-button wpvqgr-social-vk" data-title="<?php echo $wpvqgr_quiz->getSetting('global_template_vk_title'); ?>" data-description="<?php echo $wpvqgr_quiz->getSetting('global_template_vk_description'); ?>"><i class="fa fa-vk" aria-hidden="true"></i>&nbsp; <?php echo __('Share on VK', 'wpvq'); ?></button>
									<?php endif ?>
								</div>
							<?php endif; ?>
						</div>

						<?php if ($wpvqgr_quiz->getSetting('playagain')): ?>
							<button class="wpvqgr-button wpvqgr-playagain"><?php echo $wpvqgr_quiz->getSetting('customlabel_playagainbutton'); ?></button>
						<?php endif; ?>

					</div>
				</div>
			</div>

			<?php if ($wpvqgr_quiz->getPageCounter() > 1 && in_array('bottom', $wpvqgr_quiz->getSetting('progessbar'))): ?>
				<!-- Progress bar -->
				<div class="wpvqgr-progress wpvqgr-progress-bottom progress">
					<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
				</div>
			<?php endif ?>

			<div class="wpvqgr-a-d-s">
				<?php echo do_shortcode($wpvqgr_quiz->getSetting('global_ads_after')); ?>
				<?php echo do_shortcode($wpvqgr_quiz->getSetting('ads_after')); ?>
			</div>

			<?php if ($wpvqgr_quiz->getSetting('promote')): ?>
				<div class="wpvqgr-promote">
					<p>
						<?php _e("This quiz has been created with", 'wpvq'); ?> <a href="https://www.ohmyquiz.io/discover" target="_blank">WordPress Viral Quiz</a>
					</p>
				</div>
			<?php endif; ?>
		</div> <!-- / #wpvqgr -->
	</div> <!-- / container -->

	<!-- Loading -->
	<div class="row">
		<div class="wpvqgr-loader">
			<p>
				<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
				<span class="sr-only"><?php _e("Loading...", 'wpvq'); ?></span>
			</p>
		</div>
	</div>
</div> <!-- / Bootstrap wrapper -->