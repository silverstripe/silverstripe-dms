<?php
/**
 * DataObject to store versions of uploaded Documents. Versions are only created when replacing a document, not on every
 * save of the DMSDocument dataobject. So, versions store the various versions of the underlying Document, not the
 * DataObject with information about that object.
 */
class DMSDocument_versions extends DataObject {

	public static $enable_versions = true;   //flag that turns on or off versions of documents when replacing them

	private static $db = array(
		'VersionCounter' => 'Int',
		'VersionViewCount' => 'Int'
	); //config system call in _config creates this to mirror DMSDocument

	private static $has_one = array(
		'Document' => 'DMSDocument' //ID of the original DMSDocument object this is a version of
	);

	private static $defaults = array(
		'VersionCounter' => 0
	);

	private static $display_fields = array(
		'VersionCounter' => 'Version Counter',
		'FilenameWithoutID' => 'Filename',
		'LastChanged' => 'Last Changed'
	);

	private static $summary_fields = array(
		'VersionCounter',
		'FilenameWithoutID'
	);

	private static $field_labels = array(
		'FilenameWithoutID'=>'Filename'
	);

	private static $default_sort = array(
		'LastChanged' => 'DESC'
	);


	/**
	 * Creates a new version of a document by moving the current file and renaming it to the versioned filename.
	 * This method assumes that the method calling this is just about to upload a new file to replace the old file.
	 * @static
	 * @param DMSDocument $doc
	 * @return bool Success or failure
	 */
	static function create_version(DMSDocument $doc) {
		$success = false;

		$existingPath = $doc->getFullPath();
		if (is_file($existingPath)) {
			$docData = $doc->toMap();
			unset($docData['ID']);
			$version = new DMSDocument_versions($docData);  //create a copy of the current DMSDocument as a version

			$previousVersionCounter  = 0;
			$newestExistingVersion = self::get_versions($doc)->sort(array('Created'=>'DESC','ID'=>'DESC'))->limit(1);
			if ($newestExistingVersion && $newestExistingVersion->Count() > 0) {
				$previousVersionCounter = $newestExistingVersion->first()->VersionCounter;
			}

			//change the filename field to a field containing the new soon-to-be versioned file
			$version->VersionCounter = $previousVersionCounter + 1; //start versions at 1
			$newFilename = $version->generateVersionedFilename($doc, $version->VersionCounter);
			$version->Filename = $newFilename;

			//add a relation back to the origin ID;
			$version->DocumentID = $doc->ID;
			$id = $version->write();

			if (!empty($id)) {
				rename($existingPath, $version->getFullPath());
				$success = true;
			}
		}

		return $success;
	}

	public function delete() {
		$path = $this->getFullPath();
		if (file_exists($path)) unlink($path);

		parent::delete();
	}

	/**
     * Returns a DataList of all previous Versions of a document (check the LastEdited date of each
     * object to find the correct one)
	 * @static
	 * @param DMSDocument $doc
	 * @return DataList List of Document objects
	 */
	static function get_versions(DMSDocument $doc) {
		if (!DMSDocument_versions::$enable_versions) user_error("DMSDocument versions are disabled",E_USER_WARNING);
		return DMSDocument_versions::get()->filter(array('DocumentID' => $doc->ID));
	}

	public function __construct($record = null, $isSingleton = false, $model = null) {
		//check what the constructor was passed
		$dmsObject = null;
		if ($record && is_subclass_of($record,'DMSDocumentInterface')) {
			$dmsObject = $record;
			$record = null; //cancel the record creation to just create an empty object
		}

		//create the object
		parent::__construct($record, $isSingleton, $model);

		//copy the DMSDocument object, if passed into the constructor
		if ($dmsObject) {
			foreach(array_keys(DataObject::custom_database_fields($dmsObject->ClassName)) as $key) {
				$this->$key = $dmsObject->$key;
			}
		}
	}

	/**
	 * Returns a link to download this document from the DMS store
	 * @return String
	 */
	function getLink() {
		return Controller::join_links(Director::baseURL(),'dmsdocument/version'.$this->ID);
	}

	/**
	 * Document versions are always hidden from outside viewing. Only admins can download them
	 * @return bool
	 */
	function isHidden() {
		return true;
	}

	/**
	 * Returns the full filename of the document stored in this object. Can optionally specify which filename to use at the end
	 * @return string
	 */
	function getFullPath($filename = null) {
		if (!$filename) $filename = $this->Filename;
		return DMS::get_dms_path() . DIRECTORY_SEPARATOR . $this->Folder . DIRECTORY_SEPARATOR . $filename;
	}

	function getFilenameWithoutID() {
		$filenameParts = explode('~',$this->Filename);
		$filename = array_pop($filenameParts);
		return $filename;
	}

	/**
	 * Creates a new filename for the current Document's file when replacing the current file with a new file
	 * @param $filename The original filename to generate the versioned filename from
	 * @return String The new filename
	 */
	protected function generateVersionedFilename(DMSDocument $doc, $versionCounter) {
		$filename = $doc->Filename;

		do {
			$versionPaddingString = str_pad($versionCounter, 4, '0', STR_PAD_LEFT); //add leading zeros to make sorting accurate up to 10,000 documents
			$newVersionFilename = preg_replace('/([0-9]+~)(.*?)/','$1~'.$versionPaddingString.'~$2',$filename);

			if ($newVersionFilename == $filename || empty($newVersionFilename)) {   //sanity check for crazy document names
				user_error('Cannot generate new document filename for file: '.$filename,E_USER_ERROR);
			}

			$versionCounter++;  //increase the counter for the next loop run, if necessary
		} while(file_exists($this->getFullPath($newVersionFilename)));

		return $newVersionFilename;
	}

	/**
	 * Return the extension of the file associated with the document
	 */
	function getExtension() {
		return strtolower(pathinfo($this->Filename, PATHINFO_EXTENSION));
	}

	function getSize() {
		$size = $this->getAbsoluteSize();
		return ($size) ? File::format_size($size) : false;
	}

	/**
	 * Return the size of the file associated with the document
	 */
	function getAbsoluteSize() {
		return filesize($this->getFullPath());
	}

	/**
	 * An alias to DMSDocument::getSize()
	 */
	function getFileSizeFormatted(){
		return $this->getSize();
	}

	/**

	 */
	function trackView(){
		if ($this->ID > 0) {
			$count = $this->VersionViewCount + 1;
			DB::query("UPDATE \"DMSDocument_versions\" SET \"VersionViewCount\"='$count' WHERE \"ID\"={$this->ID}");
		}
	}

}

?>
