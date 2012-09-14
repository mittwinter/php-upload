<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/
class PHPIniReader {
	public static function get( $key ) {
		return ini_get( $key );
	}

	public static function parseSizeToBytes( $size ) {
		$size = trim( $size );
		$sizeSuffix = strtolower( $size[ strlen( $size ) - 1 ] );
		$size = intval( $size );
		switch( $sizeSuffix ) {
			case 'g':
				$size *= 1024;
			case 'm':
				$size *= 1024;
			case 'k':
				$size *= 1024;
		}
		return $size;
	}
}
?>
