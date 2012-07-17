<?php
class DMS implements DMSInterface {

	static $dmsFolder = 'dms-assets';   //folder to store the documents in

	//How many documents to store in a single folder. The square of this number is the maximum number of documents.
	//The number should be a multiple of 10
	static $dmsFolderSize = 1000;
	static $dmsPath;    //DMS path set on creation

	/**
	 * Factory method that returns an instance of the DMS. This could be any class that implements the DMSInterface.
	 * @static
	 * @return DMSInterface An instance of the Document Management System
	 */
	static function getDMSInstance() {
		self::$dmsPath = BASE_PATH . DIRECTORY_SEPARATOR . self::$dmsFolder;

		$dms = new DMS();
		$dms->createStorageFolder(self::$dmsPath);

		return $dms;
	}

	/**
	 * Takes a File object or a String (path to a file) and copies it into the DMS. The original file remains unchanged.
	 * When storing a document, sets the fields on the File has "tag" metadata. E.g: filename, path, etc. all become
	 * single-value tags on the Document.
	 * @param $file File object, or String that is path to a file to store
	 * @return DMSDocumentInstance Document object that we just created
	 */
	function storeDocument($file) {
		//confirm we have a file
		$fromPath = null;
		if (is_string($file)) $fromPath = $file;
		elseif (is_object($file) && $file->is_a("File")) $fromPath = $file->Filename;

		if (!$fromPath) throw new FileNotFoundException();

		//create a new document and get its ID
		$doc = new DMSDocument();
		$docID = $doc->write();

		//calculate all the path to copy the file to
		$fromFilename = basename($fromPath);
		$toFilename = $docID . '~' . $fromFilename; //add the docID to the start of the Filename
		$toFolder = self::getStorageFolder($docID);
		$toPath = self::$dmsPath . DIRECTORY_SEPARATOR . $toFolder . DIRECTORY_SEPARATOR . $toFilename;
		$this->createStorageFolder(self::$dmsPath . DIRECTORY_SEPARATOR . $toFolder);

		//copy the file into place
		copy($fromPath, $toPath);

		//write the filename of the stored document
		$doc->Filename = $toFilename;
		$doc->Folder = $toFolder;
		$doc->write();

		//set an initial title for the document from the filename
		$doc->addTag('title', $fromFilename, false);

		return $doc;
	}

	/**
	 *
	 * Returns a number of Document objects based on the a search by tags. You can search by category alone,
	 * by tag value alone, or by both. I.e: getByTag("fruits",null); getByTag(null,"banana"); getByTag("fruits","banana")
	 * @param null $category The metadata category to search for
	 * @param null $value The metadata value to search for
	 * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
	 * @return DocumentInterface
	 */
	function getByTag($category = null, $value = null, $showEmbargoed = false) {
		// TODO: Implement getByTag() method.
	}

	/**
	 * Returns a number of Document objects that match a full-text search of the Documents and their contents
	 * (if contents is searchable and compatible search module is installed - e.g. FullTextSearch module)
	 * @param $searchText String to search for
	 * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
	 * @return DocumentInterface
	 */
	function getByFullTextSearch($searchText, $showEmbargoed = false) {
		// TODO: Implement getByFullTextSearch() method.
	}

	/**
	 * Returns a list of Document objects associated with a Page
	 * @param $page SiteTree to fetch the associated Documents from
	 * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
	 * @return DataList Document list associated with the Page
	 */
	function getByPage($page, $showEmbargoed = false) {
		// TODO: Implement getByPage() method.
	}

	/**
	 * Creates a storage folder for the given path
	 * @param $path Path to create a folder for
	 */
	protected function createStorageFolder($path) {
		if (!is_dir($path)) {
			mkdir($path, 0777);
		}
	}

	/**
	 * Calculates the storage path from a database DMSDocument ID
	 */
	static function getStorageFolder($id) {
		$folderName = intval($id / self::$dmsFolderSize);
		return $folderName;
	}
}