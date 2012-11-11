<?php
/*** Released under the terms of "THE BEER-WARE LICENSE" (Revision 42):
 *** Martin Wegner < dev -at- mittwinter -dot- de > wrote this file. As long as you
 *** retain this notice you can do whatever you want with this stuff. If we meet some
 *** day, and you think this stuff is worth it, you can buy me a beer in return.
 ***/

// ***
// *** Localization
// ***
require_once( './locale.inc.php' );

// ***
// *** Class definitions
// ***
require_once( './Config.class.php' );
require_once( './PHPIniReader.class.php' );
require_once( './Upload.class.php' );
require_once( './Uploader.class.php' );
require_once( './CompressingUploader.class.php' );

// ***
// *** Global function definitions
// ***
require_once( './functions.inc.php' );

// ***
// *** "Globally" available instances and convenience variables:
// ***
$config = new Config();
$uploaderImplementation = $config->get( 'uploader' );
$uploader = new $uploaderImplementation( $config );
//$uploader = new CompressingUploader( $config );
$uploader->purgeFileStorage();
$maxFileSize = PHPIniReader::parseSizeToBytes( PHPIniReader::get( 'upload_max_filesize' ) );
$maxTotalFileSize = PHPIniReader::parseSizeToBytes( PHPIniReader::get( 'post_max_size' ) );
$maxNumFiles = PHPIniReader::get( 'max_file_uploads' );
?>
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>php-upload</title>
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
				labelElementText = document.createTextNode( '<?php echo( _('Upload file') . ': ' ); ?>' );
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
		<h2><?php echo( _('Upload files') . ':' ); ?></h2>
		<?php
		// ***
		// *** Process uploads:
		// ***
		if( !empty( $_FILES ) ) {
			$uploads = $uploader->processPHPFilesArray();
			echo( '<p><strong>' . _('Uploaded files') . ':</strong></p>' );
			echo( '<ul>' );
			foreach( $uploads as $upload ) {
				echo( '<li>' );
				echo( '<strong>' . htmlspecialchars( $upload->getName() ) . ':</strong> ' );
				if( $upload->isSuccessfullyProcessed() ) {
					echo( _('Upload successful') . '. ' );
					echo( sprintf( _('Download available at %s'), '<a href="' . htmlspecialchars( $upload->getDownloadLink() ) . '" title="' . sprintf( _('Download link for %s'), htmlspecialchars( $upload->getName() ) ) . '">' . htmlspecialchars( $upload->getDownloadLink() ) . '</a>' ) );
 					if( $config->get( 'fileExpiration' ) != 0 ) {
						echo( ' ' . _('for' ) . ' ' . htmlspecialchars( secondsToReadable( $config->get( 'fileExpiration' ) ) ) );
					}
					echo( '.' );
				}
				else {
					echo( _('Upload failed') . '.' );
				}
				echo( '</li>');
			}
			echo( '</ul>' );
			if( count( $uploader->getErrors() ) > 0 ) {
				echo( '<p><em>' . _('Errors') . ':</em></p>' );
				echo( '<ul>' );
				echo( '<li>' . implode( '</li><li>', array_map( 'htmlspecialchars', $uploader->getErrors() ) ) . '</li>' );
				echo( '</ul>' );
				$uploader->clearErrors();
			}
		}
		?>
		<form id="file-upload" enctype="multipart/form-data" action="<?php echo( htmlspecialchars( '?' . $_SERVER[ 'QUERY_STRING' ] ) ); ?>" method="post" onSubmit="javascript: showActivityIndicator();">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo( $maxFileSize ); ?>" />
			<p><strong><?php echo( _('Limits') . ':' ); ?></strong></p>
			<ul>
				<li><?php echo( _('Maximum filesize') . ': ' . sizeToReadable( $maxFileSize ) ); ?></li>
				<li><?php echo( _('Maximum number of files to be uploaded simultaneously') . ': ' . $maxNumFiles ); ?></li>
				<li><?php echo( _('Maximum total filesize') . ': ' . sizeToReadable( $maxTotalFileSize ) ); ?></li>
				<li><?php echo( _('Available space') . ': ' . sizeToReadable( $uploader->getFreeSpace() ) . ' ' . _('of') . ' ' . sizeToReadable( $uploader->getTotalSpace() ) ); ?></li>
			</ul>
			<?php if( $config->get( 'uploader' ) == 'CompressingUploader' ): ?>
			<label for="uploadName"><?php echo( _('Upload name') . ': ' ); ?></label>
			<input name="uploadName" type="text" size="20" />
			<br />
			<?php endif; ?>
			<label for="files[]"><?php echo( _('Upload file') . ': ' ); ?></label>
			<input name="files[]" type="file" multiple="multiple" />
			<a href="javascript: appendFileUploadField();" id="file-upload-more"><?php echo( _('more') ); ?></a>
			<img src="./activity.gif" id="activity-indicator" alt="<?php echo( _('Upload in progress') . '...' ); ?>" title="<?php echo( _('Upload in progress') . '...' ); ?>" style="visibility: hidden;" />
			<input type="submit" name="upload" value="<?php echo( _('upload') ); ?>" />
		</form>
		<h2><?php echo( _('Uploaded files') . ':' ); ?></h2>
		<ul>
			<?php
			if( count( $uploader->getStoredFiles() ) == 0 ) {
				echo( '<li>' . _('None') . '.</li>' );
			}
			else {
				foreach( $uploader->getStoredFiles() as $file ) {
					echo( '<li>' );
					echo( '<strong>' . htmlspecialchars( $file[ 'name' ] ) . '</strong>, ' . sizeToReadable( $file[ 'size' ] ) );
					echo( ', <a href="' . htmlspecialchars( $file[ 'link' ] ) . '" title="' . sprintf( _('Download %s'), htmlspecialchars( $file[ 'name' ] ) ) . '">'. htmlspecialchars( $file[ 'link' ] ) .'</a>' );
					if( $config->get( 'fileExpiration' ) != 0 ) {
						echo( ' ' . _('valid for') . ' ' . secondsToReadable( $config->get( 'fileExpiration' ) - (time() - $file[ 'modificationTime' ]) ) );
					}
					echo( '</li>' );
				}
			}
			?>
		</ul>
	</body>
</html>
