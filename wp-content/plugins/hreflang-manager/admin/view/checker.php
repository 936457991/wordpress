<?php
/**
 * The file used to display the "Checker" menu in the admin area.
 *
 * @package hreflang-manager
 */

$this->menu_elements->capability = get_option( $this->shared->get( 'slug' ) . '_checker_menu_capability' );
$this->menu_elements->context    = null;
$this->menu_elements->display_menu_content();
