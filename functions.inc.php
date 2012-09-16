<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/

require_once( './locale.inc.php' );

function sizeToReadable( $size, $base = 1024 ) {
	$si = array( 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB' );
	if( $size == 0 ) {
		return $size . ' ' . $si[ 0 ];
	}
	else {
		$i = floor( log( $size, $base ) );
		return round( $size / pow( $base, $i ), 1 ).' '.$si[ $i ];
	}
}

function secondsToReadable( $seconds ) {
	$minutes = $seconds / 60;
	$hours   = $seconds / (60 * 60);
	$days    = $seconds / (24 * 60 * 60);
	if( $minutes > 1 ) {
		if( $hours > 1 ) {
			if( $days > 1 ) {
				return round( $days ) . ' ' . _('days');
			}
			else {
				return round( $hours ) . ' ' . _('hours');
			}
		}
		else {
			return round( $minutes ) . ' ' . _('minutes');
		}
	}
	else {
		return $seconds . ' ' . _('seconds');
	}
}
?>
