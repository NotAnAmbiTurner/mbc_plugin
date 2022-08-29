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
 * Description:       A plugin for creating a shortcode [MBC_Roster_Directory] to show a mediator directory. Please note this plugin relies on "Tag:_TEXT_ANYTHING" (underscores are spaces, so, Tag:<space>TEXT<space>ANYTEXT to list TEXT as tags for the mediators in the mediator directory.
 * Version:           1.0.0
 * Author:            Brandon Hastings
 * Author URI:        www.bhastings.com
 * License:           Licensed for use by Mediate BC only. This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. PROVIDED WITH NO WARRANTY OR GUARANTEE WHATSOEVER.
 * Text Domain:       bdh-mbc-mediator-directory
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('BDH_MBC_MEDIATOR_DIRECTORY_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bdh-mbc-mediator-directory-activator.php
 */
function activate_bdh_mbc_mediator_directory()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-bdh-mbc-mediator-directory-activator.php';
	BDH_MBC_Mediator_Directory_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bdh-mbc-mediator-directory-deactivator.php
 */
function deactivate_bdh_mbc_mediator_directory()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-bdh-mbc-mediator-directory-deactivator.php';
	BDH_MBC_Mediator_Directory_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_bdh_mbc_mediator_directory');
register_deactivation_hook(__FILE__, 'deactivate_bdh_mbc_mediator_directory');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-bdh-mbc-mediator-directory.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bdh_mbc_mediator_directory()
{

	$plugin = new BDH_MBC_Mediator_Directory();
	$plugin->run();
}
run_bdh_mbc_mediator_directory();

// Enqueue and register scripts and styles for shortcode
function bdh_mbc_ready_scripts_styles($data = array())
{
	wp_register_script('bootstrap', '//cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.js', array(), null, false);
	wp_enqueue_script('bdh_mbc_js', plugin_dir_url(__FILE__) . 'public/js/bdh-mbc-mediator-directory-list-js.js', array('listjs'), null, false);
	wp_enqueue_style('bdh_mbc_css', plugin_dir_url(__FILE__) . 'public/css/bdh-mbc-mediator-directory-list-css.css', array(), null, 'all');
}

// Get and return wordpress table prefix
function wp_table_prefix()
{
	global $wpdb;
	$table_prefix = $wpdb->prefix;
	return $table_prefix;
}

// Generate list of MemberPress members with active subscriptions
function get_active_mp_members()
{
	// Run SQL Query
	global $wpdb;
	$table_prefix = wp_table_prefix();
	$query_str = "SELECT user_id, memberships FROM {$table_prefix}mepr_members WHERE active_txn_count > 0";
	$query_prepared = $wpdb->prepare($query_str);
	$query_data = $wpdb->get_results($query_prepared);

	return $query_data;
}

function mp_get_membership_table_data($ID_arr)
{
	// Convert $ID_arr to string
	$ID_arr = implode(",", $ID_arr);

	// Run SQL Query
	global $wpdb;
	$table_prefix = wp_table_prefix();
	$query_str = "SELECT ID, post_title FROM {$table_prefix}posts WHERE ID IN($ID_arr)";
	$query_prepared = $wpdb->prepare($query_str);
	$query_data = $wpdb->get_results($query_prepared);

	return $query_data;
}

function mp_get_usermeta_table_data($user_id_list)
{
	$table_prefix = wp_table_prefix();

	$ID_arr_str = implode(", ", $user_id_list);

	// List fields wanted from {prefix}usermeta (called 'meta_key' in DB)
	$meta_filters_arr = array(
		"first_name",
		"last_name",
		"description",
		"mepr_regions_serviced", // wpmi_options.mepr_options contains properly capitalized region names
	);
	$meta_filters_w_prefix = array();
	foreach ($meta_filters_arr as $val) {
		$val2 =  '"' . $val . '"';
		array_push($meta_filters_w_prefix, $val2);
	}
	$meta_filters_arr_str = implode(", ", $meta_filters_w_prefix);
	// echo $meta_filters_arr_str;

	$meta_keys_select_arr = array(
		"user_id",
		"meta_key",
		"meta_value",
		"mper_phone",
		"mepr_year_began_mediating",

	);

	$table_select_fields_arr_w_prefix = array();
	foreach ($meta_keys_select_arr as $val) {
		$val2 =  "`" . $table_prefix . "usermeta`.`" . $val . "`";
		array_push($table_select_fields_arr_w_prefix, $val2);
	}
	$table_select_fields_str = implode(", ", $table_select_fields_arr_w_prefix);

	// Set up SQL query
	global $wpdb;

	// {prefix}usermeta table query
	$query_str = "SELECT $table_select_fields_str FROM `{$table_prefix}usermeta` WHERE `wpmi_usermeta`.`user_id` IN($ID_arr_str) and `wpmi_usermeta`.`meta_key` IN($meta_filters_arr_str)";

	echo $query_str;

	$query_prepared = $wpdb->prepare($query_str);
	$query_data = $wpdb->get_results($query_prepared);

	return $query_data;
}

function mp_get_users_table_data($user_id_list)
{
	$table_prefix = wp_table_prefix();

	// NEED FOR THIS:
	// - address
	// - url slug
	// - options (fee waiver, willing to travel, fee reduction)
	// - Tags
	// - Long description
	// - Short description
	// - Photo URL

	// Appears to work as a start
	// SELECT wpmi_usermeta.user_id, wpmi_usermeta.meta_key, wpmi_usermeta.meta_value, wpmi_users.user_nicename, wpmi_users.user_email FROM wpmi_users INNER JOIN wpmi_usermeta ON wpmi_users.ID = wpmi_usermeta.user_id WHERE wpmi_users.ID IN(22); 


	// Create array of IDs for queries
	$ID_arr_str = implode(",", $user_id_list);

	// List fields wanted from {prefix}users table
	$user_keys_arr = array(
		"user_nicename",
		"user_email",
		"display_name",
		"user_url"
	);

	// Add table names and prefixes to fields wanted, and add to array for SELECT in SQL query

	$table_select_fields_arr_w_prefix = array();

	foreach ($user_keys_arr as $val) {
		$val2 =  "`" . $table_prefix . "users`.`" . $val . "`";
		array_push($table_select_fields_arr_w_prefix, $val2);
	}

	$table_select_fields_str = implode(", ", $table_select_fields_arr_w_prefix);

	// Set up SQL query
	global $wpdb;

	// {prefix}usermeta table query
	$query_str = "SELECT $table_select_fields_str FROM `{$table_prefix}users` WHERE `wpmi_users`.`ID` IN($ID_arr_str)";

	$query_prepared = $wpdb->prepare($query_str);
	$query_data = $wpdb->get_results($query_prepared);




	return $query_data;
}

function mp_memberships_and_user_ids()
{

	// Get list of active user ids and their associated (active) membership ids.
	$users_arr = get_active_mp_members();

	// Create list of (1) active user IDs, and (2) active membership ids, with duplicates.
	$membership_id_list = array();
	$user_id_list = array();
	foreach ($users_arr as $user) {
		$membership_id_list = array_merge($membership_id_list, explode(',', $user->memberships));
		$user_id_list = array_merge($user_id_list, explode(',', $user->user_id));
	}

	// Remove duplicates, so $membership_id_list contains only unique values
	$membership_id_list = array_unique($membership_id_list);
	$user_id_list = array_unique($user_id_list);

	// Convert the values in $membership_id_list to int
	$membership_id_list = array_map('intVal', $membership_id_list);
	$user_id_list = array_map('intVal', $user_id_list);

	// Get table data for active memberships
	$membership_table_data = mp_get_membership_table_data($membership_id_list);
	$users_table_data = mp_get_users_table_data($user_id_list);
	$usermeta_table_data = mp_get_usermeta_table_data($user_id_list);

	// Create membership data structure
	$membership_structured_data = structure_membership_table_data($membership_table_data);

	// Get table data for active members
	// $member_table_data = get_member_table_data($users_arr);

	return array($users_arr, $membership_table_data);
}



function structure_membership_table_data($data)
{
	return false;
}

// Generate HTML for jurisdictions checkboxes
function bdh_mbc_jurisdiction_html($member_data)
{

	// Initialize jurisdictions; get one user and collect jurisdictions




	$jurisdictions = array('Alberni–Clayoquot', 'Bulkley–Nechako', 'Capital', 'Cariboo', 'Central Coast', 'Central Kootenay', 'Central Okanagan', 'Columbia-Shuswap', 'Comox Valley', 'Cowichan Valley', 'East Kootenay', 'Fraser Valley', 'Fraser–Fort George', 'Kitimat–Stikine', 'Kootenay Boundary', 'Metro Vancouver', 'Mount Waddington', 'Nanaimo', 'North Coast', 'North Okanagan', 'Northern Rockies', 'Okanagan–Similkameen', 'Peace River', 'qathet', 'Squamish–Lillooet', 'Stikine Region', 'Strathcona', 'Sunshine Coast', 'Thompson–Nicola');

	// Just in case want to add others to end of array, will still be in alpha order
	sort($jurisdictions);

	// Determine number of jurisdictions
	$jurisdictions_array_len = count($jurisdictions);

	// Determine number of jurisdictions for first two columns (of 3 columns)
	$jurisdictions_per_column = ceil($jurisdictions_array_len / 3);

	// Initialize jurisdictions checkbox HTML (for use in $html_ret) with top part of $jurisdictions_html
	$jurisdictions_html = <<<EOD
							
							<div class="et_pb_row et_pb_row_4 et_pb_equal_columns">
								<div class="et_pb_column et_pb_column_1_3 et_pb_css_mix_blend_mode_passthrough" style="margin:0px; padding: 0px;">
									<div class="et_pb_module et_pb_text et_pb_text_7  et_pb_text_align_left et_pb_bg_layout_light">
										<div class="et_pb_text_inner">
											<p>
												
					EOD;

	// Initialize counter
	$jurdctn_count = 0;

	// Loop through jurisdictions, creating checkboxes for each
	foreach ($jurisdictions as $jrsdctn) {

		$jurisdictions_html .= "<input type='checkbox' id='strtolower($jrsdctn)' value='strtolower($jrsdctn)'>$jrsdctn</option>";

		// If $jurisdictions_per_column is reached
		if ($jurdctn_count == $jurisdictions_per_column) {

			// Close current column and create new column
			$jurisdictions_html .= <<<EOD
													</p>
												</div>
											</div>
										</div>
										<div class="et_pb_column et_pb_column_1_3 et_pb_css_mix_blend_mode_passthrough et-last-child" style="margin:0px; padding: 0px;">
											<div class="et_pb_module et_pb_text et_pb_text_9  et_pb_text_align_left et_pb_bg_layout_light">
												<div class="et_pb_text_inner">
													<p>
										EOD;

			// Reset counter to 0
			$jurdctn_count = 0;
		} else {
			// Add a line break if another checkbox is to be added
			$jurisdictions_html .= "<br>";
		}

		// Increment counter. Do this after the check, so that don't double-add column close.
		$jurdctn_count++;
	}

	$jurisdictions_html .= <<<EOD
													</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							EOD;

	return $jurisdictions_html;
}

// Get structured data for mediator list
function bdh_mbc_mediators_html($data = array())
{
	return bdh_mbc_mp_api_call();
}

function prettyPrintPHPArr($array)
{
	return '<pre>' . print_r($array, true) . '</pre>';
}

function create_user_data_arr()
{
	return mp_memberships_and_user_ids();
}

// Add Shortcode
function bdh_mbc_mediator_directory_shortcode_fn()
{

	// Initialize scripts and css for the appropriate page
	bdh_mbc_ready_scripts_styles();

	// Generate an associative array, containing relevant user and member data
	$member_data = create_user_data_arr();

	$debug_data1 = prettyPrintPHPArr($member_data[0]);
	$debug_data2 = prettyPrintPHPArr($member_data[1]);

	// Construct jurisdictions html for use in page
	$jurisdictions_html = bdh_mbc_jurisdiction_html($member_data);

	$html_ret = <<<EOD
					debug_data1: $debug_data1
					<br>
					debug_data2: $debug_data2
					<div class="et_pb_column et_pb_column_4_4 et_pb_column_0  et_pb_css_mix_blend_mode_passthrough et-last-child">
						<div class="et_pb_module et_pb_text et_pb_text_0  et_pb_text_align_left et_pb_bg_layout_light">
							<div class="et_pb_text_inner">
								<h1>Mediator search</h1>
								<p>Select relevant options, and type in the search bar, to filter mediators and show a curated list below.</p>
							</div>
						</div>
						<div class="et_pb_row et_pb_row_1">
							<div class="et_pb_column et_pb_column_1_3 et_pb_column_1  et_pb_css_mix_blend_mode_passthrough">
								<div class="et_pb_module et_pb_text et_pb_text_1  et_pb_text_align_left et_pb_bg_layout_light">
									<div class="et_pb_text_inner"><h5>Roster membership</h5>
										<p>
											<input type="checkbox" id="familyMediator" name="familyMediator" value="familyMediator">Family Mediator<br>
											<input type="checkbox" id="civilMediator" name="civilMediator" value="civilMediator">Civil Mediator<br>
											<input type="checkbox" id="elderMediator" name="elderMediator" value="elderMediator">Elder Mediator<br><input type="checkbox" id="excludeAssociate" name="excludeAssociate" value="exclude Associate">Exclude associate mediators
										</p>
									</div>
								</div>
							</div>
							<div class="et_pb_column et_pb_column_2_3 et_pb_column_2  et_pb_css_mix_blend_mode_passthrough et-last-child">
								<div class="et_pb_module et_pb_text et_pb_text_2  et_pb_text_align_left et_pb_bg_layout_light">
									<div class="et_pb_text_inner">
										<h5>Filter mediator list based on text</h5>
									</div>
									</div>
									<div class="et_pb_module et_pb_search et_pb_search_0  et_pb_text_align_left et_pb_bg_layout_light">
									<div>
										<label class="screen-reader-text" for="s">Search</label>
										<input type="text" name="s" placeholder="Enter text here to search mediators" class="et_pb_s">
									</div>
								</div>
							</div>
						</div>
						<div class="et_pb_row et_pb_row_2">
							<div class="et_pb_column et_pb_column_1_3 et_pb_column_3  et_pb_css_mix_blend_mode_passthrough">
								<div class="et_pb_module et_pb_text et_pb_text_3  et_pb_text_align_left et_pb_bg_layout_light">
									<div class="et_pb_text_inner">
										<p>
											<input type="checkbox" id="willingToTravel" name="willingToTravel" value="willingToTravel">Willing to travel (additional fees may apply)<br>
											<input type="checkbox" id="legalAidTarriff" name="legalAidTarriff" value="legalAidTarriff">Legal aid tariff applies (family mediation only)<br>
											<input type="checkbox" id="feeReductionPossible" name="feeReductionPossible" value="feeReductionPossible">  Fee reduction possible (contact mediator for conditions)
										</p>
									</div>
								</div>
							</div>
							<div class="et_pb_column et_pb_column_1_3 et_pb_column_4  et_pb_css_mix_blend_mode_passthrough">
								<div class="et_pb_module et_pb_text et_pb_text_4  et_pb_text_align_left et_pb_bg_layout_light">
									<div class="et_pb_text_inner">
										<p>Professional background checkboxes/dropdown (criteria TBD)</p>
									</div>
								</div>
							</div>
							<div class="et_pb_column et_pb_column_1_3 et_pb_column_5  et_pb_css_mix_blend_mode_passthrough et-last-child">
								<div class="et_pb_module et_pb_text et_pb_text_5  et_pb_text_align_left et_pb_bg_layout_light">
									<div class="et_pb_text_inner">
										<p>“tags” criteria TBD (eg. online mediator)</p>
									</div>
								</div>
							</div>
						</div>
						<div class="et_pb_row et_pb_row_3">
							<div class="et_pb_column et_pb_column_4_4 et_pb_column_6  et_pb_css_mix_blend_mode_passthrough et-last-child">
								<div class="et_pb_module et_pb_text et_pb_text_6  et_pb_text_align_left et_pb_bg_layout_light">
									<div class="et_pb_text_inner">
										<h5>Regions served</h5>
										<p>Leaving all boxes unchecked, will list mediators for all regions.</p>
									</div>
								</div>
							</div>  
						</div>
						$jurisdictions_html
						<div class="et_pb_section et_pb_section_1 et_section_regular et_pb_section_sticky">
							<div class="et_pb_row et_pb_row_5">
								<div class="et_pb_column et_pb_column_4_4 et_pb_column_10  et_pb_css_mix_blend_mode_passthrough et-last-child">
									<div class="et_pb_module et_pb_text et_pb_text_10  et_pb_text_align_left et_pb_bg_layout_light">
										<div class="et_pb_text_inner">
											<h2>Mediators</h2>
										</div>
									</div>
								</div>
							</div>
							<div class="et_pb_row et_pb_row_6 et_clickable">
								<div class="et_pb_column et_pb_column_1_5 et_pb_column_11  et_pb_css_mix_blend_mode_passthrough">
									<div class="et_pb_module et_pb_image et_pb_image_0 et_pb_image_sticky">
										<span class="et_pb_image_wrap ">
											<img src="http://mbcsandbox.com/wp-content/uploads/2022/07/portrait-12.jpg" alt="" title="portrait-12" srcset="https://mbcsandbox.com/wp-content/uploads/2022/07/portrait-12.jpg 400w, https://mbcsandbox.com/wp-content/uploads/2022/07/portrait-12-200x300.jpg 200w" sizes="(max-width: 400px) 100vw, 400px" class="wp-image-211240" width="400" height="600">
										</span>
									</div>
								</div>
								<div class="et_pb_column et_pb_column_3_5 et_pb_column_12  et_pb_css_mix_blend_mode_passthrough">
									<div class="et_pb_module et_pb_text et_pb_text_11 et_clickable  et_pb_text_align_left et_pb_bg_layout_light">
										<div class="et_pb_text_inner">
											<p>
												<strong>Mediator Name</strong>
												<span class="mbctag mbctag1"></span><span class="mbctag mbctag3"></span>
											</p>
											<p>Mediator intro blurb.</p>
										</div>
									</div>
								</div>
								<div class="et_pb_column et_pb_column_1_5 et_pb_column_13  et_pb_css_mix_blend_mode_passthrough et-last-child et_pb_column_empty">
								</div>
							</div>
						</div>
					</div>
EOD;

	return "$html_ret";
}
add_shortcode('MBC_Roster_Directory', 'bdh_mbc_mediator_directory_shortcode_fn');