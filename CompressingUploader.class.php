<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/

require_once( './locale.inc.php' );
require_once( './Uploader.interface.php' );
require_once( './UploaderBase.class.php' );

class CompressingUploader extends UploaderBase implements UploaderInterface {
	public function __construct( $config ) {
		parent::__construct( $config );
	}

	public function processPHPFilesArray() {
		$tmpArchiveFilename = tempnam( sys_get_temp_dir(), 'CompressingUploader' );
		if( $tmpArchiveFilename === false ) {
			$this->errors[] = sprintf( _('Failed to create temporary filename in %s.'), sys_get_temp_dir() );
			return array();
		}
		$zipArchive = new ZipArchive;
		if( $zipArchive->open( $tmpArchiveFilename, ZipArchive::CREATE ) === false ) {
			$this->errors[] = sprintf( _('Failed to create ZIP archive %s.'), $tmpArchiveFilename );
		}
		for( $i = 0; $i < count( $_FILES[ 'files' ][ 'name' ] ); $i++ ) {
			$upload = new Upload( $_FILES[ 'files' ][ 'name' ][ $i ]
			                    , $_FILES[ 'files' ][ 'size' ][ $i ]
			                    , $_FILES[ 'files' ][ 'type' ][ $i ]
			                    , $_FILES[ 'files' ][ 'tmp_name' ][ $i ]
			                    , $_FILES[ 'files' ][ 'error'][ $i ]
			                );
			if( $upload->getErrorCode() == UPLOAD_ERR_OK ) {
				$zipArchive->addFile( $upload->getTempName(), $upload->getName() );
			}
			else {
				$this->errors[] = sprintf( _('Upload of file %s failed. Aborting complete upload processing.'), $upload->getName() );
				return array();
			}
		}
		$zipArchive->close();
		if( isset( $_POST[ 'uploadName' ] ) && $_POST[ 'uploadName' ] != '' ) {
			$uploadName = preg_replace( '/[^A-Za-z0-9-_ ]/', '', $_POST[ 'uploadName' ] );
		}
		else {
			$uploadName = 'upload';
		}
		// Generate "encrypted" path for upload:
		$encryptedUploadDir = $this->encryptFilename( $uploadName );
		$storePath = $this->config->get( 'fileStoragePath' ) . '/' . $encryptedUploadDir;
		$uploadFileName = $uploadName . '.zip';
		// Create "encrypted" path:
		if( ! mkdir( $storePath) ) {
			$this->errors[] = sprintf( _('mkdir() of %s failed.'), $storePath );
			return array();
		}
		// chmod() "encrypted" path:
		if( $this->config->get( 'fileStorageDirMode' ) != 0 && ! chmod( $storePath, $this->config->get( 'fileStorageDirMode' ) ) ) {
			$this->errors[] = sprintf( _('chmod() to %s of %s failed.'), $this->config->get( 'fileStorageDirMode' ), $storePath );
			return array();
		}
		// Check for enough free space:
		if( filesize( $tmpArchiveFilename ) > $this->getFreeSpace() ) {
			$this->errors[] = sprintf( _('Free space exceeded while storing %s!'), $tmpArchiveFilename );
			return array();
		}
		// Store upload in "encrypted" path:
		if( ! @rename( $tmpArchiveFilename, $storePath . '/' . $uploadFileName ) ) {
			if( ! @copy( $tmpArchiveFilename, $storePath . '/' . $uploadFileName ) ) {
				$this->errors[] = sprintf( _('Failed to rename() or (as fallback) copy() %s to %s.'), $tmpArchiveFilename, $storePath . '/' . $uploadFileName );
				if( ! rmdir( $storePath ) ) {
					$this->errors[] = sprintf( _('Failed to remove %s while cleaning up.'), $storePath );
				}
				return array();
			 }
		}
		// Set download link of upload:
		$zipUpload = new Upload( $uploadFileName
		                       , filesize( $storePath . '/' . $uploadFileName )
		                       , 'application/zip'
		                       , ''
		                       , UPLOAD_ERR_OK
		                   );
		$zipUpload->setDownloadLink( $this->config->get( 'baseURL' ) . $encryptedUploadDir . '/' . rawurlencode( $zipUpload->getName() ) );
		$zipUpload->setSuccessfullyProcessed( true );
		$this->resetStoredFilesCache();
		return array( $zipUpload );
	}

	public function process( $upload ) {
		// ToDo: Implement this! :)
	}
}
?>
