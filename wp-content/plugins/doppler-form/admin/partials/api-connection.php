
<section class="dplr_settings">

<div class="wrap dplr_connect text-center">

		<h2></h2>

		<a href="<?php _e('https://www.fromdoppler.com/en/?utm_source=landing&utm_medium=integracion&utm_campaign=wordpress', 'doppler-form')?>" target="_blank" id="dplr_logo" class="d-inline-block"><img src="<?= plugins_url( '/../img/logo-doppler.svg', __FILE__ ); ?>" alt="Doppler"></a>

		<?php

		if ($connected) {

			?>

			<h1 class="size-huge margin-auto mb-1">
				<?php _e("Successful connection!", "doppler-form" ); ?>
			</h1>

			<p class="subtitle margin-auto mb-1"><?php _e("Your account is now officially connected","doppler-form") ;?> :)</p>

			<div class="updated-message">

				<img class="ok-message mb-2" src="<?= plugins_url( '/../img/ok-message.svg', __FILE__ ); ?>" alt="">

				<div class="disconnect-box">
					<form method="POST" action="options.php" id="dplr-disconnect-form">
						<div class="dplr-row-section d-flex" style="justify-content: center;align-items: center;">
							<span><?php echo __('Username', 'doppler-form')?>: <strong><?php echo $options['dplr_option_useraccount']?></strong></span>
							<span>Api Key: <strong><?php echo $options['dplr_option_apikey']?></strong></span>
							<?php settings_fields('dplr_plugin_options'); ?>
							<button type="submit" class="dp-button button-medium primary-green"><?php _e("Disconnect", "doppler-form"); ?></button>
						</div>
					</form>
				</div>

				<hr class="mb-1 mt-1">

				<div class="dplr-pasos">

					<!--<h2><?php _e('You are almost done! Follow this steps', 'doppler-form')?></h2>-->

					<div><!-- 3 boxes -->

						<div>
							<figure>
								<img src="<?= plugins_url( '/../img/'.__('screenshot-1.png', 'doppler-form'), __FILE__ ); ?>" alt="step 1"/>
							</figure>
							<span>
								1.
							</span>
							<p>
								<?php
									_e('Go to Doppler Forms > Create Form.', 'doppler-form');
								?>
							</p>
						</div>

						<div>
							<figure>
								<img src="<?= plugins_url( '/../img/'.__('screenshot-2.png', 'doppler-form'), __FILE__ ); ?>" alt="step 2"/>
							</figure>
							<span>
								2.
							</span>
							<p>
								<?php
									_e('Go to Appearance > Widgets > Doppler Form and select where do you want the Forms to be displayed.', 'doppler-form');
								?>
							</p>
						</div>

						<div>
							<figure>
								<img src="<?= plugins_url( '/../img/'.__('screenshot-3.png', 'doppler-form'), __FILE__ ); ?>" alt="step 3"/>
							</figure>
							<span>
								3.
							</span>
							<p>
								<?php
									_e('Done! You should now see your Form published on your Website.', 'doppler-form');
								?>
							</p>
						</div>

					</div> <!-- fin 3 boxes -->

				</div> <!-- fin dplr_pasos -->

			</div> <!-- fin updated message -->

			<?php

		} else {

		?>

		<h1 class="size-huge margin-auto mb-1"><?php _e("Connect your WordPress Forms with Doppler", "doppler-form" ); ?></h1>

		<p class="subtitle size-big margin-auto mb-2">
				<?php _e("Create Subscription Forms that respect your Website styles and automatically send your new Subscribers from WordPress to Doppler Lists.","doppler-form") ;?>
		</p>

		<div class="dplr_form_wrapper margin-auto">

			<form method="POST" action="options.php" id="dplr-connect-form" class="form-horizontal <?= $error?'error':''; ?>">

				<?php settings_fields('dplr_plugin_options'); ?>

				<div class="dplr-row-section d-flex">

					<div class="dplr-input-section input-text tooltip tooltip-warning <?= (isset($errorMessages['user_account']) || $error)  ? 'tooltip-initial input-error' : 'tooltip-hide'; echo !empty($options['dplr_option_useraccount']) ? ' notempty' : ''; ?>">

						<label><?php _e('Username', 'doppler-form');?></label>

						<input class="validation visible"  id="user-account" data-validation-email="<?php _e("Ouch! Enter a valid Email.", "doppler-form"); ?>"
							<?= isset($errorMessages['user_account']) ? "data-validation-fixed='".$errorMessages['user_account']."'" : "";?>
							type="text"
							placeholder=""
							name="dplr_settings[dplr_option_useraccount];"
							autocomplete="off"
							value="<?php echo isset($options['dplr_option_useraccount'])? $options['dplr_option_useraccount']:'';?>" />

						<div class="tooltip-container">
							<span></span>
						</div>

					</div>

					<div class="dplr-input-section input-text input-icon tooltip tooltip-warning <?= isset($errorMessages['api_key']) ? 'input-error' : 'tooltip-hide'; echo !empty($options['dplr_option_apikey']) ? ' notempty' : ''; ?>">

						<label>API Key
							<div class="icon ml-1">
								<span class="tooltip tooltip-info tooltip-top tooltip-hover">
									<div class="tooltip-container">
										<p>
											<?php _e("How do you find your API Key? Press", "doppler-form"); ?> <a href="<?php _e('https://help.fromdoppler.com/en/where-do-i-find-my-api-key/?utm_source=landing&utm_medium=integracion&utm_campaign=wordpress', 'doppler-form')?>"><?php _e("HELP", "doppler-form"); ?></a>.<br>
										</p>
									</div>
								</span>
							</div>
						</label>

						<input id="api-key"
							data-validation-required="<?php _e("Ouch! The field is empty.", "doppler-form"); ?>" <?= isset($errorMessages['api_key']) ? "data-validation-fixed='".$errorMessages['api_key']."'" : "";?>
							data-validation="noempty"
							class="visible"
							type="text"
							placeholder=""
							name="dplr_settings[dplr_option_apikey];"
							autocomplete="off"
							value="<?php echo (isset($options['dplr_option_apikey']))? $options['dplr_option_apikey']:'' ?>" />

						<div class="tooltip-container pl-1">
							<span></span>
						</div>
					</div>

					<button class="dp-button button-medium primary-green visible">
						<?php _e("Connect", "doppler-form"); ?>
					</button>

				</div>

			</form>

			<?php if($error): ?>

				<div class="tooltip tooltip-warning tooltip--user_api_error">
					<div class="text-red text-left">
							<span><?php echo $errorMessage  ?></span>
					</div>
				</div>

			<?php endif;?>

		</div>

		<p class="margin-auto visible">
				<?php _e("Do you have any doubts about how to connect your Forms with Doppler? Press", "doppler-form")?>
				<?php echo  '<a href="'.__('https://help.fromdoppler.com/en/how-to-integrate-wordpress-forms-with-doppler?utm_source=landing&utm_medium=integracion&utm_campaign=wordpress','doppler-form').'" target="blank">'.__('HELP','doppler-form').'</a>'?>.
		</p>

	<?php
	}
	?>

	</div>
</section>