<div class="wpvqgr-show-register" style="position:relative;">
	<button id="ajax-register-draw-btn" type="button" class="wpvqgr-button wpvqgr-register-draw" style="background:#f39406;"><i class="fa fa-trophy"></i>&nbsp;Register in Draw</button>

	<div id="ajax-register-login-modal" style="display:none; border: 1px solid; border-radius: 5px; padding: 10px; position: absolute; background: white; z-index: 10; width: calc(100% - 40px); top:-30px; left:10px;">
		<div>Please login to register result in draw.</div>
		<div style="display: flex; align-items:center; justify-content: space-around;padding: 10px;width: 100%;">
			<button onclick="wpvqrg_register_in_draw_login()">Login</button>
			<button onclick="wpvqrg_register_in_draw_cancel()">Cancel</button>
		</div>
	</div>

	<script>
		jQuery("#ajax-register-draw-btn").on("click", function(){
			console.log("draw click event:");
			if(document.body.classList.contains( 'logged-in' )){
				wpvqrg_register_in_draw();
			}else{
				jQuery("#ajax-register-login-modal").show();
			}
		});

		var register_draw_login_waiting = 0;

		function wpvqrg_register_in_draw_login(){
			if(jQuery('#ajax-login-plugin-buttons .login-with-ajax button').length > 0){
				jQuery('#ajax-login-plugin-buttons .login-with-ajax button').trigger('click');
			}else if(jQuery('#ajax-login-plugin-buttons .login-with-ajax a').length > 0){
				jQuery('#ajax-login-plugin-buttons .login-with-ajax a').trigger('click');
			}
			clearInterval(register_draw_login_waiting);
			register_draw_login_waiting = setInterval(function(){
				if(document.body.classList.contains( 'logged-in' )){
					console.log("login - success");
					clearInterval(register_draw_login_waiting);
					jQuery("#ajax-register-login-modal").hide();

					wpvqrg_register_in_draw();
				}else{
					console.log("login - wait");
				}
			}, 500);
		}

		function wpvqrg_register_in_draw_cancel(){
			jQuery("#ajax-register-login-modal").hide();
			clearInterval(register_draw_login_waiting);
		}

		function wpvqrg_register_in_draw(){

			console.log("wpvqrg_register_in_draw:");

			var data 		=  {};
			var callback1 	= function(){
				// jQuery("#ajax-register-draw-btn").html('<i class="fa fa-trophy"></i>&nbsp;Registered in Draw');
				// jQuery("#ajax-register-draw-btn").css('background', '#A9BBAD ');
				// var callback2 = function(){
				// 	if (wpvqgr.vars.quiz.settings.saveanswers != true) {
				// 		wpvqgr.ajaxSaveAnswers(function(){});
				// 	}
				// }
				// wpvqgr.ajaxSaveInfo(data, callback2);
			};

			// Trigger Custom Event
			// jQuery( document ).trigger( "wpvqgr-askInfo", [ wpvqgr.vars.quiz ] );

			// Save form data to WP database

			// if (wpvqgr.vars.quiz.settings.askinfo_localsave === true) {

			wpvqgr.ajaxRegisterInDraw(data, callback1);
			// } else {
			// 	callback();
			// }
			// jQuery("#ajax-register-draw-btn").attr("disabled","disabled");
			jQuery("#ajax-register-draw-btn").hide();
		}
	</script>
</div>
