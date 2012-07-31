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

	protected $folderName = 'DMSTemporaryUploads';

	/**
	 * Override the default behaviour of the UploadField and take the uploaded file (uploaded to assets) and
	 * add it into the DMS storage, deleting the old/uploaded file.
	 * @param File
	 */
	protected function attachFile($file) {
		$dmsDocument = $this->getRecord();
		$dmsDocument->ingestFile($file);
	}


	/**
	 * Never directly display items uploaded
	 * @return SS_List
	 */
	public function getItems() {
		return new ArrayList();
	}

	public function Field($properties = array()) {
		$fields = parent::Field($properties);

		//replace the download template with a new one
		Requirements::block(FRAMEWORK_DIR . '/javascript/UploadField_downloadtemplate.js');
		Requirements::javascript('dms/javascript/DMSUploadField_downloadtemplate.js');

		return $fields;
	}
}
