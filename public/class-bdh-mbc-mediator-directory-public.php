<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BDH_MBC_Mediator_Directory
 * @subpackage BDH_MBC_Mediator_Directory/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    BDH_MBC_Mediator_Directory
 * @subpackage BDH_MBC_Mediator_Directory/public
 * @author     Your Name <email@example.com>
 */
class BDH_MBC_Mediator_Directory_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $bdh_mbc_mediator_directory    The ID of this plugin.
	 */
	private $bdh_mbc_mediator_directory;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $bdh_mbc_mediator_directory       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $bdh_mbc_mediator_directory, $version ) {

		$this->bdh_mbc_mediator_directory = $bdh_mbc_mediator_directory;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in BDH_MBC_Mediator_Directory_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The BDH_MBC_Mediator_Directory_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->bdh_mbc_mediator_directory, plugin_dir_url( __FILE__ ) . 'css/bdh-mbc-mediator-directory-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in BDH_MBC_Mediator_Directory_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The BDH_MBC_Mediator_Directory_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->bdh_mbc_mediator_directory, plugin_dir_url( __FILE__ ) . 'js/bdh-mbc-mediator-directory-public.js', array( 'jquery' ), $this->version, false );
	}

}
