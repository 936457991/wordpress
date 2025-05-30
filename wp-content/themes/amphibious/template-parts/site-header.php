<?php
/**
 * The template for displaying site header
 * @package Amphibious
 */
?>

<header id="masthead" class="site-header">
	<div class="container">
		<div class="row">
			<div class="col">

				<div class="site-header-inside-wrapper">
					<?php
					// Site Branding
					get_template_part( 'template-parts/site-branding' );

					// Site Navigation
					get_template_part( 'template-parts/site-navigation' );
					?>
				</div><!-- .site-header-inside-wrapper -->

			</div><!-- .col -->
		</div><!-- .row -->
	</div><!-- .container -->
</header><!-- #masthead -->
