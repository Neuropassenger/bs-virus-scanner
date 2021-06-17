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
}