<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/

// ***
// *** Class definitions
// ***
require_once( './Config.class.php' );
require_once( './PHPIniReader.class.php' );
require_once( './Upload.class.php' );
require_once( './Uploader.class.php' );

// ***
// *** Global function definitions
// ***
require_once( './functions.inc.php' );

// ***
// *** "Globally" available instances and convenience variables:
// ***
$config = new Config();
$uploader = new Uploader( $config );
$uploader->purgeFileStorage();
$maxFileSize = PHPIniReader::parseSizeToBytes( PHPIniReader::get( 'upload_max_filesize' ) );
$maxTotalFileSize = PHPIniReader::parseSizeToBytes( PHPIniReader::get( 'post_max_size' ) );
$maxNumFiles = PHPIniReader::get( 'max_file_uploads' );
?>
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Upload</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<script type="text/javascript">
		function appendFileUploadField() {
			fileUploadChilds = document.getElementById( 'file-upload' ).childNodes;
			fileUploadCounter = 0;
			fileUploadCounterMax = <?php echo( $maxNumFiles ); ?>;
			for( i = 0; i < fileUploadChilds.length; i++ ) {
				if( fileUploadChilds[ i ].nodeType == 1 && fileUploadChilds[ i ].nodeName.toLowerCase() == 'input' && fileUploadChilds[ i ].getAttribute( 'name' ).toLowerCase() == 'files[]' ) {
						fileUploadCounter++;
				}
			}
			if( fileUploadCounter < fileUploadCounterMax ) {
				document.getElementById( 'file-upload' ).appendChild( document.createElement( 'br' ) );
				// Create and append file input field label:
				labelElement = document.createElement( 'label' );
				labelElement.setAttribute( 'for', 'files[]' );
				labelElementText = document.createTextNode( 'Upload file: ' );
				labelElement.appendChild( labelElementText );
				document.getElementById( 'file-upload' ).appendChild( labelElement );
				// Create and append file input field:
				inputElement = document.createElement( 'input' );
				inputElement.setAttribute( 'name', 'files[]' );
				inputElement.setAttribute( 'type', 'file' );
				inputElement.setAttribute( 'multiple', 'multiple' );
				document.getElementById( 'file-upload' ).appendChild( inputElement );
				// Hide link to add more file upload fields if fileUploadCounterMax is reached:
				if( fileUploadCounter == (fileUploadCounterMax - 1) ) {
					document.getElementById( 'file-upload-more' ).setAttribute( 'style', 'visibility: hidden;' );
				}
			}
		}

		function showActivityIndicator() {
			document.getElementById( 'activity-indicator' ).style.visibility = 'visible';
		}
		</script>
	</head>
	<body>
		<h2>Upload files:</h2>
		<?php
		// ***
		// *** Process uploads:
		// ***
		if( !empty( $_FILES ) ) {
			echo( '<ul>' );
			for( $i = 0; $i < count( $_FILES[ 'files' ][ 'name' ] ); $i++ ) {
				$upload = new Upload( $_FILES[ 'files' ][ 'name' ][ $i ]
				                    , $_FILES[ 'files' ][ 'size' ][ $i ]
				                    , $_FILES[ 'files' ][ 'type' ][ $i ]
				                    , $_FILES[ 'files' ][ 'tmp_name' ][ $i ]
				                    , $_FILES[ 'files' ][ 'error'][ $i ]
				                    );
				echo( '<li>' );
				echo( '<strong>' . htmlspecialchars( $upload->getName() ) . ':</strong> ' );
				if( $uploader->process( $upload ) ) {
					echo( 'Upload successful. ' );
					echo( 'Download available at <a href="' . htmlspecialchars( $upload->getDownloadLink() ) . '" title="Download link for ' . htmlspecialchars( $upload->getName() ) . '">' . htmlspecialchars( $upload->getDownloadLink() ) . '</a>');
 					if( $config->get( 'fileExpiration' ) != 0 ) {
						echo( ' for ' . htmlspecialchars( secondsToReadable( $config->get( 'fileExpiration' ) ) ) );
					}
					echo( '.' );
				}
				else {
					echo( 'Upload failed: ' . htmlspecialchars( implode( ' ', $uploader->getErrors() ) ) );
				}
				echo( '</li>');
				$uploader->clearErrors();
			}
			echo( '</ul>' );
		}
		?>
		<form id="file-upload" enctype="multipart/form-data" action="<?php echo( htmlspecialchars( $_SERVER[ 'PHP_SELF' ] ) ); ?>" method="post" onSubmit="javascript: showActivityIndicator();">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo( $maxFileSize ); ?>" />
			<ul>
				<li>Maximum filesize: <?php echo( sizeToReadable( $maxFileSize ) ); ?></li>
				<li>Maximum number of files to be uploaded: <?php echo( $maxNumFiles ); ?></li>
				<li>Maximum total filesize: <?php echo( sizeToReadable( $maxTotalFileSize ) ) ?></li>
				<li>Available space: <?php echo( sizeToReadable( $uploader->getFreeSpace() ) ); ?> of <?php echo( sizeToReadable( $uploader->getTotalSpace() ) ); ?>
			</ul>
			<label for="files[]">Upload file: </label>
			<input name="files[]" type="file" multiple="multiple" />
			<a href="javascript: appendFileUploadField();" id="file-upload-more">more</a>
			<img src="./activity.gif" id="activity-indicator" alt="Upload in progress..." title="Upload in progress..." style="visibility: hidden;" />
			<input type="submit" name="upload" value="upload" />
		</form>
		<h2>Uploaded files:</h2>
		<ul>
			<?php
			if( count( $uploader->getStoredFiles() ) == 0 ) {
				echo( '<li>None.</li>' );
			}
			else {
				foreach( $uploader->getStoredFiles() as $file ) {
					echo( '<li>' );
					echo( '<strong>' . htmlspecialchars( $file[ 'name' ] ) . '</strong>, ' . sizeToReadable( $file[ 'size' ] ) );
					echo( ', <a href="' . htmlspecialchars( $file[ 'link' ] ) . '" title="Download ' . htmlspecialchars( $file[ 'name' ] ) . '">'. htmlspecialchars( $file[ 'link' ] ) .'</a>' );
					if( $config->get( 'fileExpiration' ) != 0 ) {
						echo( ' valid for ' . secondsToReadable( $config->get( 'fileExpiration' ) - (time() - $file[ 'modificationTime' ]) ) );
					}
					echo( '</li>' );
				}
			}
			?>
		</ul>
	</body>
</html>
