<?php
/**
 * View for the author archive bio block.
 *
 * @since 1.0.0
 */

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable Generic.Files.LineLength.MaxExceeded

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alumniOf = [];
if ( ! empty( $data['authorMetaData']['alumniOf'] ) ) {
	foreach ( $data['authorMetaData']['alumniOf'] as $alumniOfData ) {
		if ( empty( $alumniOfData['name'] ) ) {
			continue;
		}

		$alumniOf[] = $alumniOfData['name'];
	}
}
?>
<style>
.aioseo-author-bio {
	text-align: left;
	background-color: white;
	border: 1px solid black;
	border-radius: 5px;

	color: #111111;
	background-color: #FFFFFF;
}

.aioseo-author-bio-header {
	padding: 24px;
	display: flex;
	gap: 40px;
}

.aioseo-author-bio-header-left {
	flex: 0 0 200px;
}

.aioseo-author-bio-header-right {
	flex: 1 1 auto;
}

.aioseo-author-bio-header-left .aioseo-author-bio-image {
	width: 200px;
	height: 200px;
	border-radius: 5px;
}

.aioseo-author-bio-header-right .author-name {
	font-size: 22px;
	font-weight: 600;
}

.aioseo-author-bio-header-right .author-job-title {
	margin-left: 12px;
	padding-left: 12px;
	font-size: 18px;
	border-left: 1px solid black;
}

.aioseo-author-bio-header-right .author-expertises {
	margin: 20px 0;

	display: flex;
	flex-wrap: wrap;
	gap: 10px;
}

.aioseo-author-bio-header-right .author-expertises .author-expertise {
	padding: 4px 8px;

	font-size: 14px;

	border-radius: 4px;
	background-color: #DCDDE1;
	color: inherit;
}

.aioseo-author-bio-header-right .author-alumni-of {
	margin: 20px 0;
	font-size: 18px;
}

.aioseo-author-bio-header-right .author-socials {
	margin: 20px 0;

	display: flex;
	gap: 6px;
}

.aioseo-author-bio-header-right .author-socials .aioseo-social-icon-tumblrUrl {
	margin-left: -2px;
}

.aioseo-author-bio-main {
	padding: 24px;
	border-top: 1px solid black;
	font-size: 18px;
}

.aioseo-author-bio-main *:last-child {
	margin-bottom: 0;
}

.aioseo-author-bio-site-editor-disclaimer {
	color: black;
	margin-bottom: 12px;
	font-style: italic;
}

.block-editor .aioseo-author-bio-main .aligncenter {
	margin-left: auto;
	margin-right: auto;
}

@media screen and (max-width: 530px ) {
	.aioseo-author-bio-header {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

	.aioseo-author-bio-header-left .aioseo-author-bio-image {
		display: block;
		margin: 0 auto;
	}

	.aioseo-author-bio-header-right {
		text-align: center;
	}

	.aioseo-author-bio-header-right .author-socials {
		justify-content: center;
	}
}
</style>

<?php
if ( $showSampleDescription ) {
	?>
	<p class="aioseo-author-bio-site-editor-disclaimer"><?php esc_html_e( 'Below is a sample of how this block will look in posts & author archives:', 'aioseo-eeat' ); ?></p>
	<?php
}
?>

<div class="aioseo-author-bio">
	<div class="aioseo-author-bio-header">
		<?php do_action( 'aioseo_eeat_author_archive_bio_header_start', $data['authorId'] ); ?>

		<?php
		if ( $data['authorImageUrl'] ) {
			?>
			<div class="aioseo-author-bio-header-left">
				<img class="aioseo-author-bio-image" src="<?php echo esc_attr( esc_url( $data['authorImageUrl'] ) ); ?>" alt="<?php echo esc_attr( $data['labels']['authorImageAlt'] ); ?>"/>
			</div>
			<?php
		}
		?>
		<div class="aioseo-author-bio-header-right">
			<div>
				<span class="author-name"><?php echo esc_html( $data['authorName'] ); ?></span>
				<?php
				if ( ! empty( $data['authorMetaData']['jobTitle'] ) ) {
					?>
					<span class="author-job-title"><?php echo esc_html( $data['authorMetaData']['jobTitle'] ); ?></span>
					<?php
				}
				?>
			</div>

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
			if ( ! empty( $alumniOf ) ) {
				?>
				<span><?php echo esc_html( $data['labels']['alumniOf'] ); ?></span>
				<?php
				for ( $index = 0; $index < count( $alumniOf ); $index++ ) {
					$name = $alumniOf[ $index ];
					if ( isset( $alumniOf[ $index + 1 ] ) ) {
						$name .= ',';
					}

					if ( ! empty( $data['authorMetaData']['alumniOf'][ $index ]['url'] ) ) {
						?>
							<a href="<?php echo esc_attr( esc_url( $data['authorMetaData']['alumniOf'][ $index ]['url'] ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $name ); ?></a>
						<?php
					} else {
						?>
							<span><?php echo esc_html( $name ); ?></span>
						<?php
					}
				}
			}

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

			<?php do_action( 'aioseo_eeat_author_archive_bio_header_end', $data['authorId'] ); ?>
		</div>
	</div>

	<?php
	if ( ! empty( $data['authorMetaData']['authorBio'] ) ) {
		?>
		<div class="aioseo-author-bio-main">
			<?php do_action( 'aioseo_eeat_author_bio_main_start', $data['authorId'] ); ?>

			<?php
			echo wp_kses_post(
				! empty( $data['authorMetaData']['authorBio'] )
					? do_shortcode( aioseo()->tags->replaceTags( $data['authorMetaData']['authorBio'], get_the_ID() ) )
					: ''
			);
			?>

			<?php do_action( 'aioseo_eeat_author_bio_main_end', $data['authorId'] ); ?>
		</div>
		<?php
	}
	?>
</div>