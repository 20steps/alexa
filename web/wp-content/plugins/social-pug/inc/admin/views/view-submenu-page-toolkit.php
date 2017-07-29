<?php dpsp_admin_header(); ?>

<div class="dpsp-page-wrapper dpsp-page-toolkit wrap">

	<?php wp_nonce_field( 'dpsptkn', 'dpsptkn' ); ?>

	<!-- Share Tools -->
	<h1 class="dpsp-page-title"><?php echo __( 'Share Tools', 'social-pug' ); ?></h1>

	<div class="dpsp-row dpsp-m-padding">
	<?php 
		$tools = dpsp_get_tools('share_tool'); 

		foreach( $tools as $tool_slug => $tool )
			dpsp_output_tool_box( $tool_slug, $tool );
	?>
	</div><!-- End of Share Tools -->

	<br />

	<!-- Marketing Tools -->
	<h1 class="dpsp-page-title"><?php echo __( 'Marketing Tools', 'social-pug' ); ?></h1>

	<div class="dpsp-row dpsp-m-padding">

		<?php
			$tools = array();

			$tools['opt_in_pop_up'] = array(
				'name' 		 		 => __( 'Email Opt-in Pop-up', 'social-pug' ),
				'img'		 		 => 'assets/img/opt-in-hound-promo-pop-up.png',
				'url'				 => admin_url( 'admin.php?page=dpsp-extensions&sub-page=opt-in-hound' )
			);

			$tools['opt_in_widget'] = array(
				'name' 		 		 => __( 'Email Opt-in Widget', 'social-pug' ),
				'img'		 		 => 'assets/img/opt-in-hound-promo-widget.png',
				'url'				 => admin_url( 'admin.php?page=dpsp-extensions&sub-page=opt-in-hound' )
			);

			foreach( $tools as $tool_slug => $tool )
				dpsp_output_tool_box( $tool_slug, $tool );
		?>

	</div><!-- End of Marketing Tools -->

</div>