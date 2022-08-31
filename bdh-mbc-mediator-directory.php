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
	$table_prefix = wp_table_prefix();

	$query_data = run_sql_query("SELECT user_id, memberships FROM {$table_prefix}mepr_members WHERE active_txn_count > 0");

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

function run_sql_query($query_str)
{
	$table_prefix = wp_table_prefix();

	// echo "<br>";
	// echo $query_str;

	global $wpdb;

	$query_prepared = $wpdb->prepare($query_str);
	$query_data = $wpdb->get_results($query_prepared);

	// echo prettyPrintPHPArr($query_data);

	return $query_data;
}

// GLOBAL define row headings for DB query of {prefix}usermeta
$meta_keys_select_arr = array(
	"user_id",
	"meta_key",
	"meta_value"
);

// GLOBAL list fields wanted from {prefix}usermeta (called 'meta_key' in DB)
$meta_filters_arr = array(
	"first_name",
	"last_name",
	"description", // Not sure if using this
	"mepr_regions_serviced", // wpmi_options.mepr_options contains properly capitalized region names
	"mepr_phone",
	"mepr_year_began_mediating",
	"mepr_public_profile", // Short profile (listing)
	"mepr_profile_picture", // URL to user profile photo
	"mepr-address-city",
	"mepr-address-country",
	"mepr-address-postal-zip",
	"mepr_detailed_profile" // Long profile
);

function mp_get_usermeta_table_data($user_id_list)
{
	// {prefix}usermeta table query
	$table_prefix = wp_table_prefix();

	$ID_arr_str = implode(", ", $user_id_list);

	global $meta_filters_arr;

	$meta_filters_w_prefix = array();
	foreach ($meta_filters_arr as $val) {
		$val2 =  '"' . $val . '"';
		array_push($meta_filters_w_prefix, $val2);
	}
	$meta_filters_arr_str = implode(", ", $meta_filters_w_prefix);

	global $meta_keys_select_arr;

	$table_select_fields_arr_w_prefix = array();
	foreach ($meta_keys_select_arr as $val) {
		$val2 =  "`" . $table_prefix . "usermeta`.`" . $val . "`";
		array_push($table_select_fields_arr_w_prefix, $val2);
	}
	$table_select_fields_str = implode(", ", $table_select_fields_arr_w_prefix);



	$usermeta_query_str = "SELECT $table_select_fields_str FROM `{$table_prefix}usermeta` WHERE `wpmi_usermeta`.`user_id` IN($ID_arr_str) and `wpmi_usermeta`.`meta_key` IN($meta_filters_arr_str)";

	$usermeta_query_data = run_sql_query($usermeta_query_str);

	return $usermeta_query_data;
}

// GLOBAL list fields wanted from {prefix}users table
$user_keys_arr = array(
	"ID",
	"user_nicename",
	"user_email",
	"display_name",
	"user_url"
);

function mp_get_users_table_data($user_id_list)
{
	$table_prefix = wp_table_prefix();

	// NEED FOR THIS:
	// - address
	// - url slug
	// - options (fee waiver, willing to travel, fee reduction)
	// - Tags

	// Create array of IDs for queries
	$ID_arr_str = implode(",", $user_id_list);

	global $user_keys_arr;

	// Add table names and prefixes to fields wanted, and add to array for SELECT in SQL query
	$table_select_fields_arr_w_prefix = array();

	foreach ($user_keys_arr as $val) {
		$val2 =  "`" . $table_prefix . "users`.`" . $val . "`";
		array_push($table_select_fields_arr_w_prefix, $val2);
	}

	$table_select_fields_str = implode(", ", $table_select_fields_arr_w_prefix);

	$query_data = run_sql_query("SELECT $table_select_fields_str FROM `{$table_prefix}users` WHERE `wpmi_users`.`ID` IN($ID_arr_str)");

	return $query_data;
}

function mp_memberships_and_user_ids()
{
	// Get list of active user ids and their associated (active) membership ids.
	$users_arr = get_active_mp_members();

	// Create list of (1) active user IDs, and (2) active membership ids, with duplicates.
	$membership_id_list = array();
	$user_id_list = array();
	$user_memberships_list = array();
	foreach ($users_arr as $user) {
		$user_memberships = explode(',', $user->memberships);
		$membership_id_list = array_merge($membership_id_list, $user_memberships);
		$user_id = $user->user_id;
		array_push($user_id_list, $user_id);
		$user_memberships_list[$user_id] = $user_memberships;
	}

	// Remove duplicates, so $membership_id_list contains only unique values
	$membership_id_list = array_unique($membership_id_list);
	$user_id_list = array_unique($user_id_list);

	// Convert the values to int
	$membership_id_list = array_map('intVal', $membership_id_list);
	$user_id_list = array_map('intVal', $user_id_list);

	return array($user_id_list, $membership_id_list, $user_memberships_list);
}

function get_structured_active_membership_data($active_membership_id_list)
{
	$active_membership_table_data = mp_get_membership_table_data($active_membership_id_list);

	$structured_membership_table_data = array();
	foreach ($active_membership_table_data as $membership) {
		$id = $membership->ID;
		$title = $membership->post_title;
		$structured_membership_table_data[$id] = $title;
	}

	return $structured_membership_table_data;
}

// Generate HTML for jurisdictions checkboxes
function bdh_mbc_jurisdiction_html($jurisdictions)
{

	// Just in case want to add others to end of array, will still be in alpha order
	ksort($jurisdictions);

	// Determine number of jurisdictions
	$jurisdictions_array_len = count($jurisdictions);

	// Determine number of jurisdictions for first two columns (of 4 columns)
	$jurisdictions_per_column = floor($jurisdictions_array_len / 4);

	// Initialize jurisdictions checkbox HTML (for use in $html_ret) with top part of $jurisdictions_html
	$jurisdictions_html = <<<EOD
							
							<div class="et_pb_row et_pb_row_4 et_pb_equal_columns">
								
								<div class="et_pb_column et_pb_column_1_3 et_pb_css_mix_blend_mode_passthrough" style="margin:0px; padding: 0px;">
									<div class="et_pb_module et_pb_text et_pb_text_9  et_pb_text_align_left et_pb_bg_layout_light">
										<div class="et_pb_text_inner">
											<p>
												
					EOD;

	// Initialize counter
	$jurdctn_count = 0;

	// Loop through jurisdictions, creating checkboxes for each
	foreach ($jurisdictions as $jrsdctn_id => $jrsdctn_val) {

		// If $jurisdictions_per_column is reached
		if ($jurdctn_count == $jurisdictions_per_column) {

			// Close current column
			$jurisdictions_html .= <<<EOD
												</p>
											</div>
										</div>
									</div>
									EOD;

			// Start new column
			$jurisdictions_html .= <<<EOD
									<div class="et_pb_column et_pb_column_1_3 et_pb_css_mix_blend_mode_passthrough et-last-child" style="margin:0px; padding: 0px;">
										<div class="et_pb_module et_pb_text et_pb_text_9  et_pb_text_align_left et_pb_bg_layout_light">
											<div class="et_pb_text_inner">
												<p>
									EOD;

			// Reset counter to 0
			$jurdctn_count = 0;

			$jurisdictions_html .= "<input type='checkbox' id='{$jrsdctn_id}' value='{$jrsdctn_id}'>$jrsdctn_val</option><br>";
		} else {

			$jurisdictions_html .= "<input type='checkbox' id='{$jrsdctn_id}' value='{$jrsdctn_id}'>$jrsdctn_val</option><br>";
		}

		// Increment counter. Do this after the check, so that don't double-add column close.
		$jurdctn_count++;
	}

	// Close column and row after foreach loop
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

function prettyPrintPHPArr($array)
{
	return '<pre>' . print_r($array, true) . '</pre>';
}

function get_structured_active_user_data($user_id_list, $user_memberships_arr)
{
	$user_table_data = mp_get_users_table_data($user_id_list);
	$usermeta_table_data = mp_get_usermeta_table_data($user_id_list);

	$structured_user_data = structure_user_query_data($user_table_data);
	$structured_user_data = add_usermeta_query_data_to_structured_user_data($usermeta_table_data, $structured_user_data);

	return $structured_user_data;
}

$tag_trigger_str = "Tag: ";
$tag_trigger_end_idx = strlen($tag_trigger_str) - 1;
function add_membership_ids_and_tags_to_structured_user_data($user_data, $user_memberships_arr, $active_membership_structured_data)
{

	global $tag_trigger_str;
	global $tag_trigger_end_idx;

	$tags_list = array();
	$tags_dict = array();

	// Determine memberships that are tags
	foreach ($active_membership_structured_data as $membership_id => $membership_text) {
		if (substr($membership_text, 0, 5) == "Tag: ") {
			$text_arr = explode(" ", $membership_text);
			$tag = $text_arr[1];
			array_push($tags_list, $membership_id);
			$tags_dict[$membership_id] = $tag;
		}
	}

	// Search each user's member IDs, and add string of tags to each user record
	foreach ($user_memberships_arr as $user_id => $membership_list) {

		// $user_data[$user_id]["memberships_text_testing"] = array();
		$user_data[$user_id]["tags"] = array();
		foreach ($membership_list as $membership_id) {

			// array_push($user_data[$user_id]["memberships_text_testing"], $active_membership_structured_data[$membership_id]);

			if (in_array($membership_id, $tags_list)) {
				array_push($user_data[$user_id]["tags"], $tags_dict[$membership_id]);
			}
		}
	}

	return array($user_data, $tags_dict);
}

function add_usermeta_query_data_to_structured_user_data($table_data, $user_data)
{
	global $meta_keys_select_arr;
	global $meta_filters_arr;

	foreach ($table_data as $entry) {
		$entry_user_id = $entry->user_id;
		$user_data[$entry_user_id][$entry->meta_key] = $entry->meta_value;
	}

	return $user_data;
}

function structure_user_query_data($table_data)
{

	global $user_keys_arr;

	$user_data = array();
	foreach ($table_data as $user) {
		$id = $user->ID;

		$temp_arr = array();

		foreach ($user_keys_arr as $key) {
			if (!($key == "ID")) {
				$temp_arr[$key] = $user->$key;
			}
		}

		$user_data[$id] = $temp_arr;
	}

	return $user_data;
}

function parse_regions_served($user_data)
{
	$list_of_regions = array();


	foreach ($user_data as $user_k => $user_v) {
		$user_region_data = unserialize($user_v['mepr_regions_serviced']);

		$temp_region_list = array_keys($user_region_data);

		$list_of_regions = array_merge($list_of_regions, $temp_region_list);

		$user_data[$user_k]["regions_serviced_str"] = $temp_region_list;
	}

	$list_of_regions = array_unique($list_of_regions);

	return array($user_data, $list_of_regions);
}

function create_structured_jurisdictions_array($regions_list)
{
	$structured_regions_list = array();

	$table_prefix = wp_table_prefix();

	$query_data = run_sql_query("SELECT `option_value` FROM `{$table_prefix}options` WHERE `option_name` = 'mepr_options'");

	$query_data = $query_data[0]->option_value;

	$query_data = unserialize($query_data)["custom_fields"];

	foreach ($query_data as $field) {
		if ($field["field_key"] == "mepr_regions_serviced") {
			foreach ($field["options"] as $region_pair) {
				$upper_case = $region_pair["option_name"];
				$lower_case = $region_pair["option_value"];

				$structured_regions_list[$lower_case] = $upper_case;
			}
		}
	}

	return $structured_regions_list;
}

function bdh_mbc_membership_filter_html($tags_dict)
{
	$ret_html = "";


	foreach (array_values($tags_dict) as $tag) {
		$tag_lower = strtolower($tag);

		$ret_html .= "<input type='checkbox' id='$tag_lower' name='$tag_lower' value='$tag_lower'>$tag<br>";
	}

	return $ret_html;
};

function bdh_mbc_mediator_list_html($user_data)
{

	$ret_html = "";

	shuffle($user_data);

	foreach ($user_data as $user) {

		if (isset($user["mepr_profile_picture"])) {
			$photo_url = $user["mepr_profile_picture"];
			$photo_url_2 = substr($photo_url, -4) . "-200x300" . ".jpg";
		} else {
			$photo_url = "";
			$photo_url_2 = "";
		}

		$display_name = $user['display_name'];

		if (isset($user['mepr_public_profile'])) {
			$mepr_public_profile = $user['mepr_public_profile'];
		} else {
			$mepr_public_profile = "No profile found.";
		}

		$ret_html .= <<<EOD
						<div class="et_pb_row et_pb_row_6 et_clickable">
							<div class="et_pb_column et_pb_column_1_5 et_pb_column_11  et_pb_css_mix_blend_mode_passthrough">
								<div class="et_pb_module et_pb_image et_pb_image_0 et_pb_image_sticky">
									<span class="et_pb_image_wrap ">
										<img src="$photo_url" alt="" title="portrait-12" srcset="$photo_url 400w, $photo_url_2 200w" sizes="(max-width: 400px) 100vw, 400px" class="wp-image-211240" width="400" height="600">
									</span>
								</div>
							</div>
							<div class="et_pb_column et_pb_column_3_5 et_pb_column_12  et_pb_css_mix_blend_mode_passthrough">
										<div class="et_pb_module et_pb_text et_pb_text_11 et_clickable  et_pb_text_align_left et_pb_bg_layout_light">
											<div class="et_pb_text_inner">
												<p>
													<strong>$display_name</strong>
		EOD;

		foreach ($user["tags"] as $tag) {
			$ret_html .= <<<EOD
							<span class="mbctag $tag">$tag</span>;
						EOD;
		}

		$ret_html .= <<<EOD
						</p>
						<p>$mepr_public_profile</p>
						</div>
						</div>
						</div>
						<div class="et_pb_column et_pb_column_1_5 et_pb_column_13  et_pb_css_mix_blend_mode_passthrough et-last-child et_pb_column_empty">
						</div>
						</div>
					EOD;
	}

	return $ret_html;
}

// Add Shortcode
function bdh_mbc_mediator_directory_shortcode_fn()
{

	// Initialize scripts and css for the appropriate page
	bdh_mbc_ready_scripts_styles();

	[$active_users_list, $active_membership_id_list, $user_memberships_arr] = mp_memberships_and_user_ids();

	$active_membership_structured_data = get_structured_active_membership_data($active_membership_id_list);

	$user_structured_data = get_structured_active_user_data($active_users_list, $user_memberships_arr);

	[$user_structured_data, $tags_dict] = add_membership_ids_and_tags_to_structured_user_data($user_structured_data, $user_memberships_arr, $active_membership_structured_data);

	// echo prettyPrintPHPArr($tags_dict);

	[$user_structured_data, $region_list] = parse_regions_served($user_structured_data);

	$structured_jurisdictions_array = create_structured_jurisdictions_array($region_list);

	// Construct jurisdictions html for use in page
	$jurisdictions_html = bdh_mbc_jurisdiction_html($structured_jurisdictions_array);

	// Construct HTML for tag / roster filter checkboxes
	$membership_filter_html = bdh_mbc_membership_filter_html($tags_dict);

	// Construct HTML for mediator list
	$mediator_list_html = bdh_mbc_mediator_list_html($user_structured_data);

	$debug_data1 = prettyPrintPHPArr($user_structured_data);
	// $debug_data2 = prettyPrintPHPArr($structured_jurisdictions_array);
	// $debug_data1 = "";
	// $debug_data2 = "";
	// $debug_data3 = "";

	// debug_data1: $debug_data1
	// <br>
	// debug_data2: $debug_data2
	// <br>
	// debug_data3: $debug_data3

	$html_ret = <<<EOD
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
											$membership_filter_html
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
										<p>Placeholder</p>
									</div>
								</div>
							</div>
							<div class="et_pb_column et_pb_column_1_3 et_pb_column_5  et_pb_css_mix_blend_mode_passthrough et-last-child">
								<div class="et_pb_module et_pb_text et_pb_text_5  et_pb_text_align_left et_pb_bg_layout_light">
									<div class="et_pb_text_inner">
										<p>Placeholder</p>
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
							$mediator_list_html
						</div>
					</div>
EOD;

	return "$html_ret";
}
add_shortcode('MBC_Roster_Directory', 'bdh_mbc_mediator_directory_shortcode_fn');