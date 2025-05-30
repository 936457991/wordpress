<?php
/**
 * View for the author bio block.
 *
 * @since 1.0.0
 */

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
.aioseo-author-bio-compact {
	display: flex;
	gap: 40px;
	padding: 12px;

	text-align: left;
	border: 1px solid black;
	border-radius: 5px;

	color: #111111;
	background-color: #FFFFFF;
}

.aioseo-author-bio-compact-left {
	flex: 0 0 120px;
}

.aioseo-author-bio-compact-right {
	flex: 1 1 auto;
}

.aioseo-author-bio-compact-left .aioseo-author-bio-compact-image {
	width: 120px;
	height: 120px;
	border-radius: 5px;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-header {
	display: flex;
	align-items: center;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-header .author-name {
	font-size: 22px;
	font-weight: 600;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-header .author-job-title {
	margin-left: 12px;
	padding-left: 12px;
	font-size: 18px;
	border-left: 1px solid gray;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-main {
	margin: 12px 0;
	font-size: 18px;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-main > p:last-of-type {
	display: inline;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-main .author-bio-link {
	display: inline-flex;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-main .author-bio-link a {
	display: flex;
	align-items: center;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-main .author-bio-link a svg {
	fill: black;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-footer .author-expertises {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-footer .author-expertises .author-expertise {
	padding: 4px 8px;

	font-size: 14px;

	border-radius: 4px;
	background-color: #DCDDE1;
	color: inherit;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-footer .author-socials {
	margin-top: 12px;

	display: flex;
	gap: 6px;
}

.aioseo-author-bio-compact-right .aioseo-author-bio-compact-footer .author-socials .aioseo-social-icon-tumblrUrl {
	margin-left: -2px;
}

.aioseo-author-bio-compact-site-editor-disclaimer {
	color: black;
	margin-bottom: 12px;
	font-style: italic;
}

@media screen and (max-width: 430px ) {
	.aioseo-author-bio-compact {
		flex-direction: column;
		gap: 20px;
	}

	.aioseo-author-bio-compact-left .aioseo-author-bio-compact-image {
		display: block;
		margin: 0 auto;
		width: 160px;
		height: 160px;
	}

	.aioseo-author-bio-compact-right {
		text-align: center;
	}

	.aioseo-author-bio-compact-right .aioseo-author-bio-compact-header {
		justify-content: center;
	}

	.aioseo-author-bio-compact-right .aioseo-author-bio-compact-footer .author-socials {
		justify-content: center;
	}
}
</style>

<?php
if ( $showSampleDescription ) {
	?>
	<p class="aioseo-author-bio-compact-site-editor-disclaimer"><?php esc_html_e( 'Below is a sample of how this block will look in posts & author archives:', 'aioseo-eeat' ); ?></p>
	<?php
}
?>

<div class="aioseo-author-bio-compact">
	<?php
	if ( $data['authorImageUrl'] ) {
		?>
		<div class="aioseo-author-bio-compact-left">
			<img class="aioseo-author-bio-compact-image" src="<?php echo esc_attr( esc_url( $data['authorImageUrl'] ) ); ?>" alt="<?php echo esc_attr( $data['labels']['authorImageAlt'] ); ?>"/>
		</div>
		<?php
	}
	?>
	<div class="aioseo-author-bio-compact-right">
		<div class="aioseo-author-bio-compact-header">
			<span class="author-name"><?php echo esc_html( $data['authorName'] ); ?></span>
			<?php
			if ( ! empty( $data['authorMetaData']['jobTitle'] ) ) {
				?>
				<span class="author-job-title"><?php echo esc_html( $data['authorMetaData']['jobTitle'] ); ?></span>
				<?php
			}
			?>
		</div>

		<div class="aioseo-author-bio-compact-main">
			<?php do_action( 'aioseo_eeat_author_bio_main_start', $data['authorId'] ); ?>

			<?php
			echo wp_kses_post(
				! empty( $data['authorMetaData']['authorExcerpt'] )
					? aioseo()->tags->replaceTags( $data['authorMetaData']['authorExcerpt'], get_the_ID() )
					: ''
			);
			?>

			<?php
			if ( $data['authorUrl'] && ! empty( $data['attributes']['showBioLink'] ) ) {
				?>
				<div class="author-bio-link">
					<a href="<?php echo esc_attr( esc_url( $data['authorUrl'] ) ); ?>"><?php echo esc_html( $data['labels']['seeFullBio'] ); ?></a>

					<a href="<?php echo esc_attr( esc_url( $data['authorUrl'] ) ); ?>" aria-label="<?php esc_attr_e( 'See Full Bio', 'aioseo-eeat' ); ?>">
						<svg
							xmlns="http://www.w3.org/2000/svg"
							width="16"
							height="17"
							viewBox="0 0 16 17"
							fill="none"
						>
							<path
								d="M5.52978 5.44L8.58312 8.5L5.52979 11.56L6.46979 12.5L10.4698 8.5L6.46978 4.5L5.52978 5.44Z"
							/>
						</svg>
					</a>
				</div>
				<?php
			}
			?>

			<?php do_action( 'aioseo_eeat_author_bio_main_end', $data['authorId'] ); ?>
		</div>

		<div class="aioseo-author-bio-compact-footer">
			<?php do_action( 'aioseo_eeat_author_bio_footer_start', $data['authorId'] ); ?>

			<?php
			if ( ! empty( $data['authorMetaData']['knowsAbout'] ) ) {
				?>
				<div class="author-expertises">
				<?php
				foreach ( $data['authorMetaData']['knowsAbout'] as $expertise ) {
					?>
					<span class="author-expertise"><?php echo esc_html( $expertise['label'] ); ?></span>
					<?php
				}
				?>
				</div>
				<?php
			}
			?>

			<?php
			if ( ! empty( $data['socialUrls'] ) ) {
				?>
				<div class="author-socials">
				<?php
				foreach ( $data['socialUrls'] as $network => $url ) {
					if (
						! $url ||
						! in_array( $network, array_keys( $data['socialIcons'] ), true ) ||
						empty( $data['socialIcons'][ $network ] )
					) {
						continue;
					}
					?>
					<a
						class="aioseo-social-icon-<?php echo esc_attr( $network ); ?>"
						href="<?php echo esc_attr( esc_url( $url ) ); ?>"
						rel="noopener" target="_blank"
						aria-label="<?php echo esc_attr( $network ); ?>"
					>
						<img src="<?php echo esc_attr( $data['socialIcons'][ $network ] ); ?>" alt="<?php echo esc_attr( $data['labels']['socialsIconAlt'] ); ?>"/>
					</a>
					<?php
				}
				?>
				</div>
				<?php
			}
			?>

			<?php do_action( 'aioseo_eeat_author_bio_footer_end', $data['authorId'] ); ?>
		</div>
	</div>
</div>