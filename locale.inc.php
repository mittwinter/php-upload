<?php
$locale = 'en_US.UTF-8';
if( isset( $_GET[ 'locale' ] ) ) {
	$locale = $_GET[ 'locale' ] . '.UTF-8';
}
$domain = 'messages';
putenv( 'LC_ALL=' . $locale );
setlocale( LC_ALL, $locale );
bindtextdomain( $domain, './locale/' );
textdomain( $domain );
bind_textdomain_codeset($domain, 'UTF-8');
?>
