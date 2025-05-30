<?php
/**
 * The file used to display the "Connections" menu in the admin area.
 *
 * @package hreflang-manager
 */

$this->menu_elements->capability = get_option( $this->shared->get( 'slug' ) . '_connections_menu_capability' );
$this->menu_elements->context = 'crud';
$this->menu_elements->display_menu_content();