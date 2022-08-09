<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             1.0.0
 * @package           BDH_MBC_Mediator_Directory
 *
 * @wordpress-plugin
 * Plugin Name:       BDH Mediate BC Roster Directory Plugin
 * Description:       A plugin for creating a shortcode [MBC_Roster_Directory] to show a mediator directory.
 * Version:           1.0.0
 * Author:            Brandon Hastings
 * Author URI:        www.bhastings.com
 * License:           Licensed for use by Mediate BC only. This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. PROVIDED WITH NO WARRANTY OR GUARANTEE WHATSOEVER.
 * Text Domain:       bdh-mbc-mediator-directory
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
define( 'BDH_MBC_MEDIATOR_DIRECTORY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bdh-mbc-mediator-directory-activator.php
 */
function activate_bdh_mbc_mediator_directory() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bdh-mbc-mediator-directory-activator.php';
	BDH_MBC_Mediator_Directory_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bdh-mbc-mediator-directory-deactivator.php
 */
function deactivate_bdh_mbc_mediator_directory() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bdh-mbc-mediator-directory-deactivator.php';
	BDH_MBC_Mediator_Directory_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bdh_mbc_mediator_directory' );
register_deactivation_hook( __FILE__, 'deactivate_bdh_mbc_mediator_directory' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bdh-mbc-mediator-directory.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bdh_mbc_mediator_directory() {

	$plugin = new BDH_MBC_Mediator_Directory();
	$plugin->run();

}
run_bdh_mbc_mediator_directory();

// Add Shortcode
function bdh_mbc_mediator_directory_shortcode_fn() {

	// API Keys
	$bdhmbc_mp_api_key = "9XpI7R0Wnw";

	wp_register_script( 'listjs', '//cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.js', array(), null, false);

	wp_enqueue_script( 'bdh_mbc_js', plugin_dir_url( __FILE__ ) . 'public/js/bdh-mbc-mediator-directory-list-js.js', array('listjs'), null, false);

	wp_enqueue_style( 'bdh_mbc_css', plugin_dir_url( __FILE__ ) . 'public/css/bdh-mbc-mediator-directory-list-css.css', array(), null, 'all');

	$list_html = <<<'EOD'
				<div id="mbcusers">
					<input class="fuzzy-search" placeholder="Search" />
					
					<button class="sort" data-sort="mbcname">
					Sort by name
					</button>
				
					<ul class="mbclist"></ul>

					<ul class="pagination"></ul>
			
				</div>
				EOD;

	return "$list_html";
}
add_shortcode( 'MBC_Roster_Directory', 'bdh_mbc_mediator_directory_shortcode_fn' );