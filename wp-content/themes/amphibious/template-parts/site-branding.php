<?php
/**
 * The template for displaying site branding
 * @package Amphibious
 */
?>

<div class="site-branding-wrapper">
	<?php
	// Site Custom Logo
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
	?>

	<div class="site-branding">
		<?php if ( is_front_page() && is_home() ) : ?>
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
		<?php else : ?>
			<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
		<?php endif; ?>

		<?php
		$description = get_bloginfo( 'description', 'display' );
		if ( $description || is_customize_preview() ) :
		?>
		<p class="site-description">
			<?php echo esc_html( $description ); ?>
		</p>
		<?php endif; ?>
	</div>
</div><!-- .site-branding-wrapper -->
