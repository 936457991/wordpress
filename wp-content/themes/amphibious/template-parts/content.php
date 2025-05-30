<?php
/**
 * The default template for displaying content
 *
 * @package Amphibious
 */
?>

<div class="post-wrapper-hentry">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="post-content-wrapper post-content-wrapper-archive">

			<?php amphibious_post_thumbnail(); ?>

			<div class="entry-data-wrapper">
				<div class="entry-header-wrapper">
					<?php if ( 'post' === get_post_type() ) : // For Posts ?>
					<div class="entry-meta entry-meta-header-before">
						<?php
							amphibious_posted_on();
							amphibious_sticky_post();
							amphibious_post_category();
						?>
					</div><!-- .entry-meta -->
					<?php endif; ?>

					<header class="entry-header">
						<?php the_title( sprintf( '<h2 class="entry-title"><a href="%1$s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
					</header><!-- .entry-header -->

					<?php if ( 'post' === get_post_type() ) : // For Posts ?>
					<div class="entry-meta entry-meta-header-after">
						<?php
						amphibious_posted_by();
						amphibious_comment_count();
						?>
					</div><!-- .entry-meta -->
					<?php endif; ?>
				</div><!-- .entry-header-wrapper -->

				<?php if ( amphibious_has_excerpt() ) : ?>
				<div class="entry-summary">
					<?php the_excerpt(); ?>
				</div><!-- .entry-summary -->
				<?php endif; ?>

				<?php
				if ( amphibious_has_read_more_label() ) {
					amphibious_read_more_link();
				}
				?>
			</div><!-- .entry-data-wrapper -->

		</div><!-- .post-content-wrapper -->
	</article><!-- #post-## -->
</div><!-- .post-wrapper-hentry -->
