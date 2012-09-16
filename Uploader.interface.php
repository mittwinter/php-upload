<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/

interface UploaderInterface {
	public function processPHPFilesArray();
	public function process( $upload );
	public function getErrors();
	public function clearErrors();
}

interface JanitorInterface {
	public function getStoredFiles();
	public function getTotalSpace();
	public function getFreeSpace();
	public function purgeFileStorage();
}

?>
