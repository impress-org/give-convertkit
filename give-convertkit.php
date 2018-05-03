<?php
/**
 * Plugin Name: Give - ConvertKit
 * Plugin URI: https://givewp.com/addons/convertkit/
 * Description: Easily integrate ConvertKit opt-ins within your Give donation forms.
 * Version: 1.0.2
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Text Domain: give-convertkit
 */

//Define constants.
if ( ! defined( 'GIVE_CONVERTKIT_VERSION' ) ) {
	define( 'GIVE_CONVERTKIT_VERSION', '1.0.2' );
}

if ( ! defined( 'GIVE_CONVERTKIT_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_CONVERTKIT_MIN_GIVE_VERSION', '2.1.1' );
}

if ( ! defined( 'GIVE_CONVERTKIT_FILE' ) ) {
	define( 'GIVE_CONVERTKIT_FILE', __FILE__ );
}

if ( ! defined( 'GIVE_CONVERTKIT_PATH' ) ) {
	define( 'GIVE_CONVERTKIT_PATH', dirname( GIVE_CONVERTKIT_FILE ) );
}

if ( ! defined( 'GIVE_CONVERTKIT_URL' ) ) {
	define( 'GIVE_CONVERTKIT_URL', plugin_dir_url( GIVE_CONVERTKIT_FILE ) );
}

if ( ! defined( 'GIVE_CONVERTKIT_DIR' ) ) {
	define( 'GIVE_CONVERTKIT_DIR', plugin_dir_path( GIVE_CONVERTKIT_FILE ) );
}

if ( ! defined( 'GIVE_CONVERTKIT_BASENAME' ) ) {
	define( 'GIVE_CONVERTKIT_BASENAME', plugin_basename( GIVE_CONVERTKIT_FILE ) );
}

/**
 * Give - ConvertKit Add-on licensing.
 */
function give_add_convertkit_licensing() {

	if ( class_exists( 'Give_License' ) ) {
		new Give_License( GIVE_CONVERTKIT_FILE, 'ConvertKit', GIVE_CONVERTKIT_VERSION, 'WordImpress' );
	}

}

add_action( 'plugins_loaded', 'give_add_convertkit_licensing' );


/**
 * Give ConvertKit Includes.
 */
function give_convertkit_includes() {

	include( GIVE_CONVERTKIT_PATH . '/includes/give-convertkit-activation.php' );

	if ( ! class_exists( 'Give' ) ) {
		return false;
	}

	include( GIVE_CONVERTKIT_PATH . '/includes/class-give-convertkit.php' );

	new Give_ConvertKit( 'convertkit', 'ConvertKit' );

}

add_action( 'plugins_loaded', 'give_convertkit_includes' );