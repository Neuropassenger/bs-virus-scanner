<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/public
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Virus_Scanner_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
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
		 * defined in Bs_Virus_Scanner_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Virus_Scanner_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bs-virus-scanner-public.css', array(), $this->version, 'all' );

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
		 * defined in Bs_Virus_Scanner_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Virus_Scanner_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bs-virus-scanner-public.js', array( 'jquery' ), $this->version, false );

	}

	public function check_file_for_viruses( $res ) {
		$api_key = get_option( 'bs_virus_scanner_api_key' );

		// Configure API key authorization: Apikey
		$config = Swagger\Client\Configuration::getDefaultConfiguration()->setApiKey( 'Apikey', $api_key );

		$apiInstance = new Swagger\Client\Api\ScanApi(
			new GuzzleHttp\Client(),
			$config
		);

		$input_file = $res['file']; // \SplFileObject | Input file to perform the operation on.
		$allow_executables = false; // bool | Set to false to block executable files (program code) from being allowed in the input file.  Default is false (recommended).
		$allow_invalid_files = true; // bool | Set to false to block invalid files, such as a PDF file that is not really a valid PDF file, or a Word Document that is not a valid Word Document.  Default is false (recommended).
		$allow_scripts = false; // bool | Set to false to block script files, such as a PHP files, Python scripts, and other malicious content or security threats that can be embedded in the file.  Set to true to allow these file types.  Default is false (recommended).
		$allow_password_protected_files = true; // bool | Set to false to block password protected and encrypted files, such as encrypted zip and rar files, and other files that seek to circumvent scanning through passwords.  Set to true to allow these file types.  Default is false (recommended).
		$allow_macros = true; // bool | Set to false to block macros and other threats embedded in document files, such as Word, Excel and PowerPoint embedded Macros, and other files that contain embedded content threats.  Set to true to allow these file types.  Default is false (recommended).
		$restrict_file_types = ""; // string | Specify a restricted set of file formats to allow as clean as a comma-separated list of file formats, such as .pdf,.docx,.png would allow only PDF, PNG and Word document files.  All files must pass content verification against this list of file formats, if they do not, then the result will be returned as CleanResult=false.  Set restrictFileTypes parameter to null or empty string to disable; default is disabled.

		try {
			$result = $apiInstance->scanFileAdvanced( $input_file, $allow_executables, $allow_invalid_files, $allow_scripts, $allow_password_protected_files, $allow_macros, $restrict_file_types );
			Bs_Virus_Scanner_Functions::logit( $result );
		} catch ( Exception $e ) {
			echo 'Exception when calling ScanApi->scanFileAdvanced: ', $e->getMessage(), PHP_EOL;
		}

		return $res;
	}

}
