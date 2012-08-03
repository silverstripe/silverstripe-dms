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
		$page = $this->getRecord();

		$dms = DMS::getDMSInstance();
		$document = $dms->storeDocument($file);
		$file->delete();
		$document->addPage($page);

		return $document;
	}

	/**
	 * Action to handle upload of a single file
	 * 
	 * @param SS_HTTPRequest $request
	 * @return string json
	 */
	public function upload(SS_HTTPRequest $request) {
		if($this->isDisabled() || $this->isReadonly()) return $this->httpError(403);

		// Protect against CSRF on destructive action
		$token = $this->getForm()->getSecurityToken();
		if(!$token->checkRequest($request)) return $this->httpError(400);

		$name = $this->getName();
		$tmpfile = $request->postVar($name);
		$record = $this->getRecord();
		
		// Check if the file has been uploaded into the temporary storage.
		if (!$tmpfile) {
			$return = array('error' => _t('UploadField.FIELDNOTSET', 'File information not found'));
		} else {
			$return = array(
				'name' => $tmpfile['name'],
				'size' => $tmpfile['size'],
				'type' => $tmpfile['type'],
				'error' => $tmpfile['error']
			);
		}

		// Check for constraints on the record to which the file will be attached.
		if (!$return['error'] && $this->relationAutoSetting && $record && $record->exists()) {
			$tooManyFiles = false;
			// Some relationships allow many files to be attached.
			if ($this->getConfig('allowedMaxFileNumber') && ($record->has_many($name) || $record->many_many($name))) {
				if(!$record->isInDB()) $record->write();
				$tooManyFiles = $record->{$name}()->count() >= $this->getConfig('allowedMaxFileNumber');
			// has_one only allows one file at any given time.
			} elseif($record->has_one($name)) {
				$tooManyFiles = $record->{$name}() && $record->{$name}()->exists();
			}

			// Report the constraint violation.
			if ($tooManyFiles) {
				if(!$this->getConfig('allowedMaxFileNumber')) $this->setConfig('allowedMaxFileNumber', 1);
				$return['error'] = _t(
					'UploadField.MAXNUMBEROFFILES', 
					'Max number of {count} file(s) exceeded.',
					array('count' => $this->getConfig('allowedMaxFileNumber'))
				);
			}
		}

		// Process the uploaded file
		if (!$return['error']) {
			$fileObject = null;

			if ($this->relationAutoSetting) {
				// Search for relations that can hold the uploaded files.
				if ($relationClass = $this->getRelationAutosetClass()) {
					// Create new object explicitly. Otherwise rely on Upload::load to choose the class.
					$fileObject = Object::create($relationClass);
				}
			}

			// Get the uploaded file into a new file object.
			try {
				$this->upload->loadIntoFile($tmpfile, $fileObject, $this->folderName);
			} catch (Exception $e) {
				// we shouldn't get an error here, but just in case
				$return['error'] = $e->getMessage();
			}

			if (!$return['error']) {
				if ($this->upload->isError()) {
					$return['error'] = implode(' '.PHP_EOL, $this->upload->getErrors());
				} else {
					$file = $this->upload->getFile();

					// Attach the file to the related record.
					$document = $this->attachFile($file);
					
					//TODO: both $document->UploadFieldThumbnailURL and $document->UploadFieldFileButtons are null,
					// check the code from UploadField.php where they use $file->UploadFieldThumbnailURL and $file->UploadFieldFileButtons
					// and $file is_a File but in our case $document is a Document, that is why it doesn't work.

					// Collect all output data.
					$return = array_merge($return, array(
						'id' => $document->ID,
						'name' => $document->getTitle(),
						'thumbnail_url' => $document->UploadFieldThumbnailURL,
						'edit_url' => $this->getItemHandler($document->ID)->EditLink(),
						'size' => $document->getFileSizeFormatted(),
						'buttons' => $document->UploadFieldFileButtons,
						'showeditform' => true
					));
				}
			}
		}
		$response = new SS_HTTPResponse(Convert::raw2json(array($return)));
		$response->addHeader('Content-Type', 'text/plain');
		return $response;
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

	/**
	 * @param int $itemID
	 * @return UploadField_ItemHandler
	 */
	public function getItemHandler($itemID) {
		return DMSUploadField_ItemHandler::create($this, $itemID);
	}
}

class DMSUploadField_ItemHandler extends UploadField_ItemHandler {
	function getItem() {
		return DataObject::get_by_id('DMSDocument', $this->itemID);
	}

}
