<?php
/**
 * Plugin Name: Delete Old Comments
 * Plugin URI:  https://cmoreira.net/delete-old-comments
 * Description: Delete from database old comments automatically. The plugin will add options to the discussion settings page to control this.
 * Version:     1.0.0
 * Author:      Carlos Moreira & Pedro Carvalho
 * Author URI:  https://cmoreira.net/delete-old-comments
 * License:     GPL3
 * Text Domain: delete-old-comments
 * Domain Path: /languages
 */
namespace Arte\WP\Plugin\DeleteComments;

// If this file is called directly, quit.
if ( ! defined( 'WPINC' ) ) {
	exit;
}

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * Begins execution of the plugin.
 *
 */
add_action( 'plugins_loaded', function () {
	$plugin = new Core(
		'delete-old-comments',
		'1.0.0',
		__FILE__
	);
	$plugin->init();
} );
