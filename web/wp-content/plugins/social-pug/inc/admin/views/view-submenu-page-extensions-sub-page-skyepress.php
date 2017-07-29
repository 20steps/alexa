<?php dpsp_admin_header(); ?>

<div class="dpsp-page-wrapper dpsp-page-extensions dpsp-sub-page-skyepress wrap">

	<div id="skyepress-promo-box">
		<div id="skyepress-promo-box-banner">
			<img src="<?php echo DPSP_PLUGIN_DIR_URL . '/assets/img/skyepress-banner.png'; ?>" />
			<img src="<?php echo DPSP_PLUGIN_DIR_URL . '/assets/img/skyepress-banner-clouds.png'; ?>" />
		</div>

		<div id="skyepress-promo-box-inner">

			<!-- Title and Sub-title -->
			<h1 class="skyepress-promo-box-title"><?php echo __( 'SkyePress', 'social-pug' ); ?> <span><?php echo __( 'WordPress Plugin', 'social-pug' ); ?></span></h1>
			<h2 class="skyepress-promo-box-sub-title"><?php echo __( 'Connect your Twitter, Facebook and LinkedIn accounts to automatically share your new and existing content to your social media profiles.', 'social-pug' ); ?></h2>

			<!-- Call to Action -->
			<div class="skyepress-promo-box-cta">
				<a class="button-primary" href="<?php echo admin_url( 'plugin-install.php?s=skyepress&tab=search&type=term' ); ?>"><?php echo __( 'Try it Now for Free', 'social-pug' ); ?></a>
			</div>

			<hr />

			<h1 class="skyepress-promo-box-title"><span><?php echo __( 'How it Works', 'social-pug' ); ?></span></h1>

			<!-- Row: Connect Accounts -->
			<div class="dpsp-row dpsp-big-padding">
				<div class="dpsp-col-1-2">
					<h2 class="skyepress-promo-box-sub-title" style="text-align: left;"><?php echo __( 'Connect your social media accounts', 'social-pug' ); ?></h2>
					<p>To share your posts to your social media profiles you will first need to connect your profiles to your WordPress website. Once done you will be able to automatically share your posts with the world, giving you extra time to publish more.</p>
				</div>
				<div class="dpsp-col-1-2">
					<img src="<?php echo DPSP_PLUGIN_DIR_URL . '/assets/img/skyepress-promo-1.png'; ?>" />
				</div>
			</div>

			<!-- Row: Share New Posts -->
			<div class="dpsp-row dpsp-big-padding">
				<div class="dpsp-col-1-2">
					<img src="<?php echo DPSP_PLUGIN_DIR_URL . '/assets/img/skyepress-promo-2.png'; ?>" />
				</div>
				<div class="dpsp-col-1-2">
					<h2 class="skyepress-promo-box-sub-title" style="text-align: left;"><?php echo __( 'Automatically share new posts', 'social-pug' ); ?></h2>
					<p>Pressing the Publish button will do much more now. Your fresh post will be shared automatically to the profiles you specify, with the option to add a custom message for each individual profile.</p>
				</div>
			</div>

			<!-- Row: Schedule Published Posts -->
			<div class="dpsp-row dpsp-last dpsp-big-padding">
				<div class="dpsp-col-1-2">
					<h2 class="skyepress-promo-box-sub-title" style="text-align: left;"><?php echo __( 'Schedule published posts sharing', 'social-pug' ); ?></h2>
					<p>Your old posts are also important. To share them to your social media profiles you can create custom schedules, where you can set the week's days and times you want the posts to be shared, and also set custom messages for each individual profile.</p>
				</div>
				<div class="dpsp-col-1-2">
					<img src="<?php echo DPSP_PLUGIN_DIR_URL . '/assets/img/skyepress-promo-3.png'; ?>" />
				</div>
			</div>

			<!-- Call to Action -->
			<div class="skyepress-promo-box-cta dpsp-last">
				<a class="button-primary" href="<?php echo admin_url( 'plugin-install.php?s=skyepress&tab=search&type=term' ); ?>"><?php echo __( 'Try it Now for Free', 'social-pug' ); ?></a>
			</div>

		</div>
	</div>

</div>