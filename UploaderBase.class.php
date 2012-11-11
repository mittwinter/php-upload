<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/

class UploaderBase implements JanitorInterface {
	protected $config;
	protected $storedFilesCache = array();
	protected $errors = array();

	public function __construct( $config ) {
		$this->config = $config;
		$this->cacheStoredFiles();
	}

	public function getErrors() { return $this->errors; }
	public function clearErrors() {
		$this->errors = array();
	}

	protected function encryptFilename( $name ) {
		return hash( 'sha256', $name . microtime() . rand() );
	}

	public function getStoredFiles() {
		if( count( $this->storedFilesCache ) == 0 ) {
			$this->cacheStoredFiles();
		}
		return $this->storedFilesCache;
	}

	protected function resetStoredFilesCache() {
		$this->storedFilesCache = array();
	}

	protected function cacheStoredFiles() {
		$this->resetStoredFilesCache();
		$fileStorageHandle = @dir( $this->config->get( 'fileStoragePath' ) );
		if( $fileStorageHandle !== false ) {
			while( ($e = $fileStorageHandle->read()) !== false ) {
				if( $e != '.' && $e != '..' && is_dir( $this->config->get( 'fileStoragePath' ) . '/' . $e ) ) {
					$fileUploadDir = $this->config->get( 'fileStoragePath' ) . '/' . $e;
					$fileUploadDirHandle = dir( $fileUploadDir );
					while( ($f = $fileUploadDirHandle->read()) !== false ) {
						if( $f != '.' && $f != '..' && is_file( $fileUploadDir . '/' . $f ) ) {
							$this->storedFilesCache[] = array( 'name' => $f
							                                 , 'encryptedDir' => $e
							                                 , 'size' => filesize( $fileUploadDir . '/' . $f )
							                                 , 'link' => $this->config->get( 'baseURL' ) . $e . '/' . rawurlencode( $f )
							                                 , 'modificationTime' => filemtime( $fileUploadDir )
							                                 );
						}
					}
					$fileUploadDirHandle->close();
				}
			}
			$fileStorageHandle->close();
		}
		else {
			$this->errors[] = 'Unable to open directory "' . $this->config->get( 'fileStoragePath' ) . '" for listing.';
		}
	}

	public function getTotalSpace() {
		if( $this->config->get( 'fileStorageQuota' ) != 0 ) {
			return $this->config->get( 'fileStorageQuota' );
		}
		else {
			return disk_total_space( $this->config->get( 'fileStoragePath' ) );
		}
	}

	public function getFreeSpace() {
		if( $this->config->get( 'fileStorageQuota' ) == 0 ) {
			return disk_free_space( $this->config->get( 'fileStoragePath' ) );
		}
		else {
			$quotaUsedSize = 0;
			foreach( $this->getStoredFiles() as $file ) {
				$quotaUsedSize += $file[ 'size' ];
			}
			return ($this->config->get( 'fileStorageQuota' ) - $quotaUsedSize);
		}
	}

	public function purgeFileStorage() {
		if( $this->config->get( 'fileExpiration' ) != 0 ) {
			foreach( $this->getStoredFiles() as $file ) {
				if( $file[ 'modificationTime' ] < (time() - $this->config->get( 'fileExpiration' )) ) {
					unlink( $this->config->get( 'fileStoragePath' ) . '/' . $file[ 'encryptedDir' ] . '/' . $file[ 'name' ] );
					rmdir( $this->config->get( 'fileStoragePath' ) . '/' . $file[ 'encryptedDir' ] );
				}
			}
			$this->resetStoredFilesCache();
		}
	}
}

