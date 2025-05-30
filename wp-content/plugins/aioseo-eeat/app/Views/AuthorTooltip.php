<?php
/**
 * View for the author tooltip.
 *
 * @since 1.0.0
 */

// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$authorNameClasses = [
	'aioseo-author-name',
	$data['attributes']['showTooltip'] ? 'aioseo-tooltip-underline' : ''
];
?>
<style>
.aioseo-author {
	display: flex;
	align-items: center;
}

.aioseo-author-text {
	margin-right: 6px;
}

.aioseo-author-image {
	width: 20px;
	height: 20px;
	border-radius: 50%;
	margin-right: 6px;
}

.aioseo-author-name {
	position: relative;
}

.aioseo-author-name.aioseo-tooltip-underline {
	text-decoration: underline dotted;
	stroke-width: 1px;
}

.aioseo-author-name.aioseo-tooltip-underline:hover {
	cursor: pointer;
}

<?php
if ( $data['attributes']['showTooltip'] ) {
	?>
	.aioseo-author-tooltip {
		display: none;
	}

	.aioseo-author-name:hover .aioseo-author-tooltip {
		display: block;
		position: absolute;
		top: 35px;
		left: -10px;

		width: 400px;
		padding: 10px;

		text-align: left;
		border: 1px solid black;
		border-radius: 5px;
		background-color: white;

		cursor: default;
		z-index: 1;
	}

	.aioseo-author-tooltip > svg {
		position: absolute;
		top: -12px;
		left: 14px;
	}

	.aioseo-author-tooltip > svg * {
		fill: white;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-content .aioseo-author-tooltip-header {
		display: flex;
		align-items: center;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-content .aioseo-author-tooltip-header .aioseo-author-tooltip-image {
		margin-right: 8px;
		width: 32px;
		height: 32px;
		border-radius: 50%;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-content .aioseo-author-tooltip-header span {
		font-weight: bold;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-content .aioseo-author-tooltip-main {
		margin: 16px 0;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-footer .author-bio-link {
		display: flex;
		align-items: center;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-footer .author-bio-link a {
		text-decoration: underline;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-footer .author-bio-link a:hover {
		text-decoration: none;
	}

	.aioseo-author-tooltip .aioseo-author-tooltip-footer .author-bio-link a.author-bio-link-caret {
		display: flex;
	}

	@media screen and (max-width: 430px ) {
		.aioseo-author-name:hover .aioseo-author-tooltip {
			width: 50vw;
		}
	}
	<?php
}
?>
</style>

<span class="aioseo-author">
	<?php do_action( 'aioseo_eeat_author_tooltip_start', $data['authorId'] ); ?>

	<?php
	if ( $data['attributes']['showLabel'] ) {
		?>
		<span class="aioseo-author-text"><?php echo esc_html( $data['labels']['writtenBy'] ); ?></span>
		<?php
	}

	if ( $data['authorImageUrl'] && $data['attributes']['showImage'] ) {
		?>
		<img class="aioseo-author-image" src="<?php echo esc_attr( esc_url( $data['authorImageUrl'] ) ); ?>" alt="<?php echo esc_html( $data['labels']['authorImageAlt'] ); ?>"/>
		<?php
	}
	?>
	<span class="<?php esc_attr_e( implode( ' ', $authorNameClasses ) ); //phpcs:ignore AIOSEO.Wp.I18n ?>">
		<?php echo esc_html( $data['authorName'] ); ?>

		<?php
		if ( $data['attributes']['showTooltip'] ) {
			?>
			<span class="aioseo-author-tooltip">
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="24"
					height="14"
					viewBox="0 0 24 14"
					fill="none"
				>
					<path
						d="M1.20711 11.5L10.9393 1.76777C11.5251 1.18198 12.4749 1.18198 13.0607 1.76777L22.7929 11.5H1.20711Z"
						fill="white"
						stroke="black"
					/>
					<path
						d="M10.5858 2.91421L0 13.5H24L13.4142 2.91421C12.6332 2.13317 11.3668 2.13317 10.5858 2.91421Z"
						fill="white"
					/>
				</svg>

				<div class="aioseo-author-tooltip-content">
					<div class="aioseo-author-tooltip-header">
						<?php do_action( 'aioseo_eeat_author_tooltip_header', $data['authorId'] ); ?>

						<?php
						if ( $data['authorImageUrl'] ) {
							?>
							<img
								class="aioseo-author-tooltip-image"
								src="<?php echo esc_attr( esc_url( $data['authorImageUrl'] ) ); ?>"
								alt="<?php echo esc_html( $data['labels']['authorImageAlt'] ); ?>"
							/>
							<?php
						}
						?>

						<span><?php echo esc_html( $data['authorName'] ); ?></span>
					</div>
					
					<div class="aioseo-author-tooltip-main">
						<?php do_action( 'aioseo_eeat_author_tooltip_main_start', $data['authorId'] ); ?>

						<?php
						echo wp_kses_post(
							! empty( $data['authorMetaData']['authorExcerpt'] )
								? aioseo()->tags->replaceTags( $data['authorMetaData']['authorExcerpt'], get_the_ID() )
								: ''
						);
						?>

						<?php do_action( 'aioseo_eeat_author_tooltip_main_end', $data['authorId'] ); ?>
					</div>

					<div class="aioseo-author-tooltip-footer">
						<?php do_action( 'aioseo_eeat_author_tooltip_footer', $data['authorId'] ); ?>

						<?php
						if ( $data['authorUrl'] && $data['hasPublishedPost'] && ! empty( $data['attributes']['showBioLink'] ) ) {
							?>
							<div class="author-bio-link">
								<a href="<?php echo esc_attr( esc_url( $data['authorUrl'] ) ); ?>"><?php echo esc_html( $data['labels']['seeFullBio'] ); ?></a>

								<a
									class="author-bio-link-caret"
									href="<?php echo esc_attr( esc_url( $data['authorUrl'] ) ); ?>"
									aria-label="<?php echo esc_attr_e( 'See Full Bio', 'aioseo-eeat' ); ?>"
								>
									<svg
										xmlns="http://www.w3.org/2000/svg"
										width="16"
										height="17"
										viewBox="0 0 16 17"
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
					</div>
				</div>
			</span>
			<?php
		}
		?>
	</span>

	<?php do_action( 'aioseo_eeat_author_tooltip_end', $data['authorId'] ); ?>
</span>