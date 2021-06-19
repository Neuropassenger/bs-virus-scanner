<?php
class Bs_Virus_Scanner_Functions {
	public static function logit( $data, $description = '[INFO]' ) {
		$filename = WP_CONTENT_DIR . '/bs_log.log';

		$text = "===[ " . $description . " ]===\n";
		$text .= "===[ " . date( 'M d Y, G:i:s', time() ) . " ]===\n";
		$text .= print_r( $data, true ) . "\n";
		$file = fopen( $filename, 'a' );
		fwrite( $file, $text );
		fclose( $file );
	}

    public static function array_push_autoinc( array &$array, $item ): int {
        $next = sizeof($array);
        $array[$next] = $item;
        return $next;
    }

    public static function is_scan_failed( $file_hash ): bool {
        // Let's look for a hash of the file in wp_options
        $failed_scan_hashes = get_option( 'bs_virus_scanner_failed_scan_hashes', array() );
        // If any errors occurred while scanning the file, then this array will contain the hash of the file
        if ( in_array( $file_hash, $failed_scan_hashes ) )
            return true;
        else
            return false;
    }

    public function are_viruses_found( $file_path ) {

    }
}