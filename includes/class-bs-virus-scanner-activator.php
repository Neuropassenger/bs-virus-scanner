<?php

/**
 * Fired during plugin activation
 *
 * @link       https://neuropassenger.ru
 * @since      1.0.0
 *
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bs_Virus_Scanner
 * @subpackage Bs_Virus_Scanner/includes
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Virus_Scanner_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        self::create_quarantine_table();
        self::create_quarantine_directory();
	}

    private static function create_quarantine_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bs_vs_quarantine';
        $charset_collate = $wpdb->get_charset_collate();

        $sql_query = "CREATE TABLE {$table_name} (
			post_id INT NOT NULL,
			hash VARCHAR(100) NOT NULL,
			public_name VARCHAR(1000) NOT NULL,
			quarantine_name VARCHAR(1000) NOT NULL,
			last_check DATETIME,
			status VARCHAR(100) NOT NULL,
			PRIMARY KEY (post_id)
		) {$charset_collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_query );
    }

    private static function create_quarantine_directory() {
	    $quarantine_dir_option = get_option( 'bs_virus_scanner_quarantine_dir' );
	    if ( ! empty( $quarantine_dir_option ) )
	        return;

        $quarantine_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'bvsq-' . hash( 'sha224', time() );
        wp_mkdir_p( $quarantine_dir );
        update_option( 'bs_virus_scanner_quarantine_dir', $quarantine_dir );
    }

}
