<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/
class Uploader {
	protected $config;
	protected $storedFilesCache = array();
	protected $errors = array();

	public function __construct( $config ) {
		$this->config = $config;
	}

	public function process( $upload ) {
		if( $upload->getErrorCode() == UPLOAD_ERR_OK ) {
			// Generate "encrypted" path for upload:
			$encryptedUploadDir = $this->encryptFilename( $upload->getName() );
			$storePath = $this->config->get( 'fileStoragePath' ) . $encryptedUploadDir;
			// Create "encrypted" path:
			if( ! mkdir( $storePath) ) {
				$this->errors[] = 'mkdir() of ' . $storePath . ' failed for ' . $upload->getName() . '.';
				return false;
			}
			// chmod() "encrypted" path:
			if( ! chmod( $storePath, $this->config->get( 'fileStorageDirMode' ) ) ) {
				$this->errors[] = 'chmod() to ' . $this->config->get( 'fileStorageDirMode' ) . ' of ' . $storePath . ' failed for ' . $upload->getName() . '.';
				return false;
			}
			// Check for enough free space:
			if( $upload->getSize() > $this->getFreeSpace() ) {
				$this->errors[] = 'Free space exceeded while storing ' . $upload->getName() . '!';
				return false;
			}
			// Store upload in "encrypted" path:
			if( ! $upload->store( $storePath ) ) {
				$this->errors[] = 'Failed to store ' . $upload->getName() . ' in ' . $storePath . '.';
				if( ! rmdir( $storePath ) ) {
					$this->errors[] = 'Failed to remove ' . $storePath . ' while cleaning up.';
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
		$fileStorageHandle = dir( $this->config->get( 'fileStoragePath' ) );
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
?>