<?php

/**
 * Field for uploading files into a DMSDocument. Replacing the existing file.
 *
 * <b>NOTE: this Field will call write() on the supplied record</b>
 *
 * <b>Features (some might not be available to old browsers):</b>
 *
 *
 * @author Julian Seidenberg
 * @package dms
 */
class DMSUploadField extends UploadField {

	/**
	 * Override the default behaviour of the UploadField and take the uploaded file (uploaded to assets) and
	 * add it into the DMS storage, deleting the old/uploaded file.
	 * @param File
	 */
	protected function attachFile($file) {
		$dmsDocument = $this->getRecord();
		$dmsDocument->ingestFile($file);
	}

}
