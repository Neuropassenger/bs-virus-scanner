<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/admin
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Virus_Scanner_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bs_Virus_Scanner_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Virus_Scanner_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bs-virus-scanner-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bs_Virus_Scanner_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Virus_Scanner_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bs-virus-scanner-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_plugin_settings_page() {
		add_options_page( 'Virus Scanner', 'Virus Scanner', 'manage_options', $this->plugin_name, array( $this, 'render_settings_page' ) );
	}

	function render_settings_page() {
		?>
		<h1 class="wp-heading-inline">
			<?php echo get_admin_page_title() ?>
		</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'bs_virus_scanner_general' );
			do_settings_sections( $this->plugin_name );
			submit_button();
			?>
		</form>
		<?
	}

	public function add_plugin_settings() {
		register_setting(
			'bs_virus_scanner_general',
			'bs_virus_scanner_api_key'
		);

		add_settings_section(
			'bs_virus_scanner_general',
			'General Settings',
			array( $this, 'show_general_settings_section' ),
			$this->plugin_name
		);

		add_settings_field(
			'bs_virus_scanner_api_key',
			'Cloudmersive API key',
			array( $this, 'show_api_key_field' ),
			$this->plugin_name,
			'bs_virus_scanner_general'
		);
	}

	function show_general_settings_section() {
		// Block before fields
	}

	function show_api_key_field() {
		$api_key = get_option( 'bs_virus_scanner_api_key' );
		echo "<input type='text' class='regular-text bs_spam-protector-secret-key' name='bs_virus_scanner_api_key' value='" . (esc_attr( $api_key ) ?? '') . "'>";
	}

}
