<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://leira.dev
 * @since             1.0.0
 * @package           Woobi
 *
 * @wordpress-plugin
 * Plugin Name:       Woobi
 * Plugin URI:        https://wordpress.org/plugins/woobi/
 * Description:       Bring analytics to your Woocommerce installations with powerful reporter tools.
 * Version:           1.0.0
 * Author:            Ariel
 * Author URI:        https://leira.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woobi
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOBI_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woobi-activator.php
 */
function activate_woobi() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woobi-activator.php';
	Woobi_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woobi-deactivator.php
 */
function deactivate_woobi() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woobi-deactivator.php';
	Woobi_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woobi' );
register_deactivation_hook( __FILE__, 'deactivate_woobi' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woobi.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @return Woobi
 * @since    1.0.0
 * @access   global
 */
function woobi() {
	return Woobi::instance();
}

woobi()->run();
