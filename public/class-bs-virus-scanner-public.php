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

	public function check_file_for_viruses( $file ) {
		$api_key = get_option( 'bs_virus_scanner_api_key' );

		// Configure API key authorization: Apikey
		$config = Swagger\Client\Configuration::getDefaultConfiguration()->setApiKey( 'Apikey', $api_key );

		$apiInstance = new Swagger\Client\Api\ScanApi(
			new GuzzleHttp\Client(),
			$config
		);

		$input_file = $file['tmp_name']; // \SplFileObject | Input file to perform the operation on.
		$allow_executables = false; // bool | Set to false to block executable files (program code) from being allowed in the input file.  Default is false (recommended).
		$allow_invalid_files = true; // bool | Set to false to block invalid files, such as a PDF file that is not really a valid PDF file, or a Word Document that is not a valid Word Document.  Default is false (recommended).
		$allow_scripts = false; // bool | Set to false to block script files, such as a PHP files, Python scripts, and other malicious content or security threats that can be embedded in the file.  Set to true to allow these file types.  Default is false (recommended).
		$allow_password_protected_files = true; // bool | Set to false to block password protected and encrypted files, such as encrypted zip and rar files, and other files that seek to circumvent scanning through passwords.  Set to true to allow these file types.  Default is false (recommended).
		$allow_macros = true; // bool | Set to false to block macros and other threats embedded in document files, such as Word, Excel and PowerPoint embedded Macros, and other files that contain embedded content threats.  Set to true to allow these file types.  Default is false (recommended).
		$restrict_file_types = ""; // string | Specify a restricted set of file formats to allow as clean as a comma-separated list of file formats, such as .pdf,.docx,.png would allow only PDF, PNG and Word document files.  All files must pass content verification against this list of file formats, if they do not, then the result will be returned as CleanResult=false.  Set restrictFileTypes parameter to null or empty string to disable; default is disabled.

		try {
			$result = $apiInstance->scanFileAdvanced( $input_file, $allow_executables, $allow_invalid_files, $allow_scripts, $allow_password_protected_files, $allow_macros, $restrict_file_types );
			// Is the file infected?
            if ( count( $result->getFoundViruses() ) > 0 ) {
                Bs_Virus_Scanner_Functions::logit( $result, '[DANGER]: INFECTED FILE' );
                $file['error'] = __( 'The uploaded file is infected!', $this->plugin_name );
            }
		} catch ( Exception $e ) {
            Bs_Virus_Scanner_Functions::logit( $file, '[ERROR]: Error scanning file with Cloudmersive API' );

            // Calculate file hash
            $file_hash = hash_file( 'sha224', $input_file );
            $failed_scan_hashes = get_option( 'bs_virus_scanner_failed_scan_hashes', array() );
            $failed_scan_hashes[] = $file_hash;
            update_option( 'bs_virus_scanner_failed_scan_hashes', $failed_scan_hashes );
		}

		return $file;
	}

    public function generate_file_hash( $post_id ) {
        $file_hash = hash_file( 'sha224', get_attached_file( $post_id ) );
        update_post_meta( $post_id, 'bs_virus_scanner_file_hash', $file_hash );
    }

    /**
     *
     * @param $post_id
     */
    public function quarantine_file_upload( $post_id ) {
        // We can get the hash of the file and be sure that it exists, since it is calculated and stored before this function
        $file_hash = get_post_meta( $post_id, 'bs_virus_scanner_file_hash', true );
        // But still make sure that the hash was calculated :)
        if ( empty( $file_hash ) )
            return;

        if ( Bs_Virus_Scanner_Functions::is_scan_failed( $file_hash ) ) {
            // We must move the file to the temporary quarantine directory for later scanning
            $public_name = get_attached_file( $post_id );
            $quarantine_dir_path = get_option( 'bs_virus_scanner_quarantine_dir' );
            if ( empty( $quarantine_dir_path ) ) {
                return;
            } elseif ( file_exists( $quarantine_dir_path ) ) {
                $public_path_info = pathinfo( $public_name );
                $quarantine_name =  $quarantine_dir_path . '/' . $file_hash . '_' . time() . '.' . $public_path_info['extension'];

                // Move the file
                $file_quarantined = @copy( $public_name, $quarantine_name );
                // Delete the file
                unlink( $public_name );
            } else {
                return;
            }

            // If the file was successfully moved to quarantine
            if ( $file_quarantined ) {
                // Let's write information about the file to the quarantine table
                global $wpdb;
                $quarantine_table_name = $wpdb->prefix . 'bs_vs_quarantine';

                $wpdb->insert( $quarantine_table_name, array(
                    'post_id'           =>  $post_id,
                    'hash'              =>  $file_hash,
                    'public_name'       =>  $public_name,
                    'quarantine_name'   =>  $quarantine_name,
                    'status'            =>  'unscanned'
                ), array( '%d', '%s', '%s', '%s', '%s' ) );

                Bs_Virus_Scanner_Functions::logit( array( 'post_id' => $post_id, 'file_hash' => $file_hash ), '[INFO]: quarantine_file_upload | The file moved to quarantine' );
            }
        }
    }

    public function finish_quarantine_upload( $post_id ) {
        $file_hash = get_post_meta( $post_id, 'bs_virus_scanner_file_hash', true );
        if ( Bs_Virus_Scanner_Functions::is_scan_failed( $file_hash ) ) {
            // Remove the file hash from wp_options
            $failed_scan_hashes = get_option( 'bs_virus_scanner_failed_scan_hashes', array() );
            foreach ( $failed_scan_hashes as $key => $hash ) {
                if ( $file_hash == $hash ) {
                    unset( $failed_scan_hashes[$key] );
                }
            }

            update_option( 'bs_virus_scanner_failed_scan_hashes', $failed_scan_hashes );
        }

        if ( ! wp_schedule_single_event( time() + 60 * 60, 'bs_vs/check_quarantine_file_for_viruses', array( $post_id, $file_hash ) ) ) {
            global $wpdb;
            $quarantine_table_name = $wpdb->prefix . 'bs_vs_quarantine';

            $file_path = get_attached_file( $post_id );
            unlink( $file_path );
            wp_delete_attachment( $post_id, true );
            $wpdb->update( $quarantine_table_name, array( 'status' => 'error', 'last_check' => date( 'Y-m-d H:i:s', time() ) ), array( 'post_id' => $post_id ), array( '%s', '%s' ), array( '%d' ) );
        }
    }

	public function check_quarantine_file_for_viruses($post_id, $file_hash ) {
        global $wpdb;
        $quarantine_table_name = $wpdb->prefix . 'bs_vs_quarantine';

        $file_path = get_attached_file( $post_id );
        $api_key = get_option( 'bs_virus_scanner_api_key' );

        // Configure API key authorization: Apikey
        $config = Swagger\Client\Configuration::getDefaultConfiguration()->setApiKey( 'Apikey', $api_key );

        $apiInstance = new Swagger\Client\Api\ScanApi(
            new GuzzleHttp\Client(),
            $config
        );

        $input_file = $file_path; // \SplFileObject | Input file to perform the operation on.
        $allow_executables = false; // bool | Set to false to block executable files (program code) from being allowed in the input file.  Default is false (recommended).
        $allow_invalid_files = true; // bool | Set to false to block invalid files, such as a PDF file that is not really a valid PDF file, or a Word Document that is not a valid Word Document.  Default is false (recommended).
        $allow_scripts = false; // bool | Set to false to block script files, such as a PHP files, Python scripts, and other malicious content or security threats that can be embedded in the file.  Set to true to allow these file types.  Default is false (recommended).
        $allow_password_protected_files = true; // bool | Set to false to block password protected and encrypted files, such as encrypted zip and rar files, and other files that seek to circumvent scanning through passwords.  Set to true to allow these file types.  Default is false (recommended).
        $allow_macros = true; // bool | Set to false to block macros and other threats embedded in document files, such as Word, Excel and PowerPoint embedded Macros, and other files that contain embedded content threats.  Set to true to allow these file types.  Default is false (recommended).
        $restrict_file_types = ""; // string | Specify a restricted set of file formats to allow as clean as a comma-separated list of file formats, such as .pdf,.docx,.png would allow only PDF, PNG and Word document files.  All files must pass content verification against this list of file formats, if they do not, then the result will be returned as CleanResult=false.  Set restrictFileTypes parameter to null or empty string to disable; default is disabled.

        try {
            $result = $apiInstance->scanFileAdvanced( $input_file, $allow_executables, $allow_invalid_files, $allow_scripts, $allow_password_protected_files, $allow_macros, $restrict_file_types );
            // Is the file infected?
            // Remove the file, notify a user
            if ( count( $result->getFoundViruses() ) > 0 ) {
                Bs_Virus_Scanner_Functions::logit( $result, '[DANGER]: INFECTED FILE' );

                $post = get_post( $post_id );
                $file_author = $post->post_author;

                // Remove the file
                unlink( $file_path );
                // Update the db record
                $wpdb->update( $quarantine_table_name, array( 'status' => 'infected', 'last_check' => date( 'Y-m-d H:i:s', time() ) ), array( 'post_id' => $post_id ), array( '%s', '%s' ), array( '%d' ) );
                // Remove the attachment
                wp_delete_attachment( $post_id, true );

                // TODO: notify a user

                // Restore the file, update info in wp_bs_vs_quarantine
            } else {
                $quarantine_row = $wpdb->get_row( "SELECT * FROM {$quarantine_table_name} WHERE post_id = {$post_id}" );
                if ( is_null( $quarantine_row ) ) {
                    Bs_Virus_Scanner_Functions::logit( $post_id, '[ERROR]: check_quarantine_file_for_viruses | There is no record in the database with the current post ID' );
                    return;
                }

                // Restore the file
                $file_restored = @copy( $quarantine_row->quarantine_name, $quarantine_row->public_name );
                if ( $file_restored ) {
                    // Delete the quarantine file
                    unlink( $quarantine_row->quarantine_name );

                    // Update the db record
                    $wpdb->update( $quarantine_table_name, array( 'status' => 'clean', 'last_check' => date( 'Y-m-d H:i:s', time() ) ), array( 'post_id' =>  $post_id ), array( '%s', '%s' ), array( '%d' ) );
                }
            }
        // Reschedule scan on API failure
        } catch ( Exception $e ) {
            Bs_Virus_Scanner_Functions::logit( $file_path, '[ERROR]: Error scanning file with Cloudmersive API' );

            // Schedule next scan
            // Remove file and attachment on failed scheduling
            if ( ! wp_schedule_single_event( time() + 60 * 60 * 24 * 7, 'bs_vs/check_quarantine_file_for_viruses', array( $post_id, $file_hash ) ) ) {
                $file_path = get_attached_file( $post_id );
                unlink( $file_path );
                wp_delete_attachment( $post_id, true );
                $wpdb->update( $quarantine_table_name, array( 'status' => 'error', 'last_check' => date( 'Y-m-d H:i:s', time() ) ), array( 'post_id' => $post_id ), array( '%s', '%s' ), array( '%d' ) );
            }
        }
    }

}
