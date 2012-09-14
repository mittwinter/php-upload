<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/
class Upload {
	protected $name;
	protected $size;
	protected $mimeType;
	protected $tempName;
	protected $errorCode;
	protected $downloadLink;
	protected $successfullyProcessed;

	protected $uploadErrors = array( UPLOAD_ERR_OK          => "No errors."
	                               , UPLOAD_ERR_INI_SIZE    => "Larger than upload_max_filesize."
	                               , UPLOAD_ERR_FORM_SIZE   => "Larger than form MAX_FILE_SIZE."
	                               , UPLOAD_ERR_PARTIAL     => "Partial upload."
	                               , UPLOAD_ERR_NO_FILE     => "No file."
	                               , UPLOAD_ERR_NO_TMP_DIR  => "No temporary directory."
	                               , UPLOAD_ERR_CANT_WRITE  => "Can't write to disk."
	                               , UPLOAD_ERR_EXTENSION   => "File upload stopped by extension."
	                               );

	public function __construct( $name, $size, $mimeType, $tempName, $errorCode ) {
		$this->name = $name;
		$this->size = $size;
		$this->mimeType = $mimeType;
		$this->tempName = $tempName;
		$this->errorCode = $errorCode;
	}

	public function getName() { return $this->name; }
	public function getSize() { return $this->size; }
	public function getMimeType() { return $this->mimeType; }
	public function getTempName() { return $this->tempName; }
	public function getErrorCode() { return $this->errorCode; }
	public function getErrorString() { return $this->uploadErrors[ $this->errorCode ]; }
	public function getDownloadLink() { return $this->downloadLink; }
	public function setDownloadLink( $link ) { $this->downloadLink = $link; }
	public function isSuccessfullyProcessed() { return $this->successfullyProcessed; }
	public function setSuccessfullyProcessed( $successfullyProcessed ) { $this->successfullyProcessed = $successfullyProcessed; }

	public function store( $path ) {
		if( $this->errorCode == UPLOAD_ERR_OK ) {
			return move_uploaded_file( $this->tempName, $path . '/' . $this->name );
		}
		else {
			throw new Exception( 'Called store() on a failed upload!' );
		}
	}
}
?>