<?php
/**
 * Conditional Tags
 *
 * @package Amphibious
 */

 /**
 * A helper conditional function.
 * Whether there is a post thumbnail and post is not password protected.
 *
 * @return bool
 */
function amphibious_has_post_thumbnail() {

	/**
	 * Post Thumbnail Filter
	 * @return bool
	 */
	return apply_filters( 'amphibious_has_post_thumbnail', (bool) ( ! post_password_required() && has_post_thumbnail() ) );

}

/**
 * A helper conditional function.
 * Post is Sticky or Not
 *
 * @return bool
 */
function amphibious_has_sticky_post() {

	/**
	 * Sticky Post Filter
	 * @return bool
	 */
	return apply_filters( 'amphibious_has_sticky_post', (bool) ( is_sticky() && is_home() && ! is_paged() ) );

}

/**
 * A helper conditional function.
 * Post has Edit link or Not
 *
 * @return bool
 */
function amphibious_has_post_edit_link() {

	/**
	 * Post Edit Link Filter
	 * @return bool
	 */
	$post_edit_link = get_edit_post_link();
	return apply_filters( 'amphibious_has_post_edit_link', (bool) ( ! empty( $post_edit_link ) ) );

}

/**
 * A helper conditional function.
 * Theme has Excerpt or Not
 *
 * @return bool
 */
function amphibious_has_excerpt() {

	// Post Excerpt
	$post_excerpt = get_the_excerpt();

	// Custom Excerpt Length
	$excerpt_length = amphibious_mod( 'amphibious_excerpt_length' );

	/**
	 * Excerpt Filter
	 * @return bool
	 */
	return apply_filters( 'amphibious_has_excerpt', (bool) $excerpt_length >= 1 && ! empty ( $post_excerpt ) );

}

/**
 * A helper conditional function.
 * Theme has Read More Label or Not
 *
 * @return bool
 */
function amphibious_has_read_more_label() {

	// Read More Label
	$read_more_label = amphibious_mod( 'amphibious_read_more_label' );

	/**
	 * Read More Label Filter
	 * @return bool
	 */
	return apply_filters( 'amphibious_has_read_more_label', (bool) ! empty ( $read_more_label ) );

}

/**
 * A helper conditional function.
 * Theme has Sidebar or Not
 *
 * @return bool
 */
function amphibious_has_sidebar() {

	/**
	 * Sidebar Filter
	 * @return bool
	 */
	return apply_filters( 'amphibious_has_sidebar', (bool) is_active_sidebar( 'sidebar-1' ) );

}
