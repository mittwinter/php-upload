<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/

require_once( './locale.inc.php' );
require_once( './Uploader.interface.php' );
require_once( './UploaderBase.class.php' );

class Uploader extends UploaderBase implements UploaderInterface {
	public function __construct( $config ) {
		parent::__construct( $config );
	}

	public function processPHPFilesArray() {
		$processedUploads = array();
		for( $i = 0; $i < count( $_FILES[ 'files' ][ 'name' ] ); $i++ ) {
			$upload = new Upload( $_FILES[ 'files' ][ 'name' ][ $i ]
			                    , $_FILES[ 'files' ][ 'size' ][ $i ]
			                    , $_FILES[ 'files' ][ 'type' ][ $i ]
			                    , $_FILES[ 'files' ][ 'tmp_name' ][ $i ]
			                    , $_FILES[ 'files' ][ 'error'][ $i ]
			                    );
			$this->process( $upload );
			$processedUploads[] = $upload;
		}
		return $processedUploads;
	}

	public function process( $upload ) {
		if( $upload->getErrorCode() == UPLOAD_ERR_OK ) {
			// Generate "encrypted" path for upload:
			$encryptedUploadDir = $this->encryptFilename( $upload->getName() );
			$storePath = $this->config->get( 'fileStoragePath' ) . $encryptedUploadDir;
			// Create "encrypted" path:
			if( ! @mkdir( $storePath) ) {
				$this->errors[] = sprintf( _('mkdir() of %s failed for %s.'), $storePath, $upload->getName() );
				return false;
			}
			// chmod() "encrypted" path:
			if( $this->config->get( 'fileStorageDirMode' ) != 0 && ! chmod( $storePath, $this->config->get( 'fileStorageDirMode' ) ) ) {
				$this->errors[] = sprintf( _('chmod() to %s of %s failed for %s.'), $this->config->get( 'fileStorageDirMode' ), $storePath, $upload->getName() );
				return false;
			}
			// Check for enough free space:
			if( $upload->getSize() > $this->getFreeSpace() ) {
				$this->errors[] = sprintf( _('Free space exceeded while storing %s!'), $upload->getName() );
				return false;
			}
			// Store upload in "encrypted" path:
			if( ! $upload->store( $storePath ) ) {
				$this->errors[] = sprintf( _('Failed to store %s in %s.'), $upload->getName(), $storePath);
				if( ! rmdir( $storePath ) ) {
					$this->errors[] = sprintf( _('Failed to remove %s while cleaning up.'), $storePath );
				}
				return false;
			}
			// Set download link of upload:
			$upload->setDownloadLink( $this->config->get( 'baseURL' ) . $encryptedUploadDir . '/' . rawurlencode( $upload->getName() ) );
			$this->resetStoredFilesCache();
			return true;
		}
		else {
			$this->errors[] = $upload->getErrorString();
			return false;
		}
	}
}
?>
