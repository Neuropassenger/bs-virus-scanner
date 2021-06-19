<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/includes
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Virus_Scanner {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bs_Virus_Scanner_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BS_VIRUS_SCANNER_VERSION' ) ) {
			$this->version = BS_VIRUS_SCANNER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'bs-virus-scanner';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bs_Virus_Scanner_Loader. Orchestrates the hooks of the plugin.
	 * - Bs_Virus_Scanner_i18n. Defines internationalization functionality.
	 * - Bs_Virus_Scanner_Admin. Defines all hooks for the admin area.
	 * - Bs_Virus_Scanner_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bs-virus-scanner-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bs-virus-scanner-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bs-virus-scanner-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bs-virus-scanner-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-bs-virus-scanner-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		$this->loader = new Bs_Virus_Scanner_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Bs_Virus_Scanner_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Bs_Virus_Scanner_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Bs_Virus_Scanner_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'add_plugin_settings' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Bs_Virus_Scanner_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//$this->loader->add_filter( 'wp_handle_upload', $plugin_public, 'check_file_for_viruses' );

		$this->loader->add_filter( 'wp_handle_upload_prefilter', $plugin_public, 'check_file_for_viruses' );
		$this->loader->add_filter( 'wp_handle_sideload_prefilter', $plugin_public, 'check_file_for_viruses' );

		//$this->loader->add_filter( 'wp_handle_upload', $plugin_public, 'process_file_upload' );

		//$this->loader->add_filter( 'wp_handle_upload_overrides', $plugin_public, 'add_overrides', 10, 2 );
		//$this->loader->add_filter( 'wp_handle_sideload_overrides', $plugin_public, 'add_overrides', 10, 2 );

        // Generate a file hash
        $this->loader->add_action( 'attachment_updated', $plugin_public, 'generate_file_hash', 15, 1 );
        $this->loader->add_action( 'add_attachment', $plugin_public, 'generate_file_hash', 15, 1 );

        // Move a file to the quarantine directory if needed
        $this->loader->add_action( 'attachment_updated', $plugin_public, 'quarantine_file_upload', 20, 1 );
        $this->loader->add_action( 'add_attachment', $plugin_public, 'quarantine_file_upload', 20, 1 );

        // Finish work with a quarantined file
        $this->loader->add_action( 'attachment_updated', $plugin_public, 'finish_quarantine_upload', 25, 1 );
        $this->loader->add_action( 'add_attachment', $plugin_public, 'finish_quarantine_upload', 25, 1 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Bs_Virus_Scanner_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
