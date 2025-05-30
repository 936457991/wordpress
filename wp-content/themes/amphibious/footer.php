<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Amphibious
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<?php
		// Site Info
		get_template_part( 'template-parts/site-info' );
		?>
	</footer><!-- #colophon -->

</div><!-- #page .site-wrapper -->

<div class="overlay-effect"></div><!-- .overlay-effect -->

<?php wp_footer(); ?>
</body>
</html>
