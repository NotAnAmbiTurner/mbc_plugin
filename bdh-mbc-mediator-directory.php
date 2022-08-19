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

// Make API call(s) to MemberPress, and return / append to structured data
function bdh_mbc_mp_api_call()
{
	$bdhmbc_mp_api_key = "rmo2UyLCaP";
	$mp_api_header = "MEMBERPRESS-API-KEY: $bdhmbc_mp_api_key";

	$page = strval(1);
	$per_page = strval(10);

	$target_url = "http://mbcsandbox.com/wp-json/mp/v1/members?page=$page&per_page=$per_page";

	// Initialize API call (cURL)
	$ch = curl_init($target_url);

	// Set header to include API key
	curl_setopt($ch, CURLOPT_HEADER, $mp_api_header);


	// Set return data
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

	$header = array();
	$header[] = $mp_api_header;
	$header[] = 'Content-Type: application/json';

	$ret_val = curl_exec($ch);

	curl_close($ch);

	return $ret_val;
}

// Generate HTML for jurisdictions checkboxes
function bdh_mbc_jurisdiction_html()
{

	// Initialize jurisdictions
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

// Add Shortcode
function bdh_mbc_mediator_directory_shortcode_fn()
{

	bdh_mbc_ready_scripts_styles();

	$jurisdictions_html = bdh_mbc_jurisdiction_html();

	$debug_data = bdh_mbc_mediators_html();

	$html_ret = <<<EOD
					$debug_data
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