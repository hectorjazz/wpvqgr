<div class="wpvqgr-show-register" style="position:relative; <?php echo $show_register?"":"display:none;"; ?>">
	<button id="ajax-register-draw-btn" type="button" class="wpvqgr-button wpvqgr-register-draw" style="background:#f39406;"><i class="fa fa-trophy"></i>&nbsp;Register in Draw</button>

	<div id="ajax-register-login-modal" style="display:none; border: 1px solid; border-radius: 5px; padding: 10px; position: absolute; background: white; z-index: 10; width: calc(100% - 40px); top:-30px; left:10px;">
		<div>Please login to register result in draw.</div>
		<div style="display: flex; align-items:center; justify-content: space-around;padding: 10px;width: 100%;">
			<button onclick="wpvqrg_register_in_draw_login()">Login</button>
			<button onclick="wpvqrg_register_in_draw_cancel()">Cancel</button>
		</div>
	</div>
</div>
