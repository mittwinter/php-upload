<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/
class Config {
	protected $config = array();

	public function __construct() {
		$this->config[ 'fileStoragePath' ] = './files/'; // path where files are stored, absolute or relative to script
		$this->config[ 'fileStorageDirMode' ] = 0775; // octal mode for directories created within fileStoragePath, set to NULL to disable chmod()
		$this->config[ 'fileStorageQuota' ] = 512 * 1024 * 1024; // in bytes, set to 0 to disable
		$this->config[ 'fileExpiration' ] = 30 * 24 * 60 * 60; // in seconds, set to 0 to disable
		$this->config[ 'baseURL' ] = 'https://example.org/upload/files/'; // must end with '/'
	}

	public function set( $key, $value ) {
		$this->check( $key );
		$this->config[ $key ] = $value;
	}

	public function get( $key ) {
		$this->check( $key );
		return $this->config[ $key ];
	}

	protected function check( $key ) {
		if( ! isset( $this->config[ $key ] ) ) {
			throw new Exception( 'Unknown config key "' . $key . '".' );
		}
	}
}
?>
