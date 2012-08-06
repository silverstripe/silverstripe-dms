<?php
/**
 * When storing a document, the DMS sucks up the file and stores it separately from the assets section.
 * When initializing the DMS, it should create some kind of storage system. For file-based storage, that could be
 * a series of folders. Within the folders there are a number of files, keeping the same filename, but prefixed with
 * an ID number, which corresponds to the document's ID. So "my-important-document" becomes:
 * "/4000/4332~my-important-document" (folder structure to avoid storing too many files within one folder. Perhaps
 * 10000 files per folder is a good amount)
 *
 */
interface DMSInterface {

	/**
	 * Factory method that returns an instance of the DMS. This could be any class that implements the DMSInterface.
	 * @static
	 * @abstract
	 * @return DMSInterface An instance of the Document Management System
	 */
	static function inst();

	/**
	 * Takes a File object or a String (path to a file) and copies it into the DMS. The original file remains unchanged.
	 * When storing a document, sets the fields on the File has "tag" metadata.
	 * @abstract
	 * @param $file File object, or String that is path to a file to store
	 * @return DMSDocumentInstance Document object that we just created
	 */
	function storeDocument($file);

	/**
	 *
	 * Returns a number of Document objects based on the a search by tags. You can search by category alone,
	 * by tag value alone, or by both. I.e: getByTag("fruits",null); getByTag(null,"banana"); getByTag("fruits","banana")
	 * @abstract
	 * @param null $category The metadata category to search for
	 * @param null $value The metadata value to search for
	 * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
	 * @return DMSDocumentInterface
	 */
	function getByTag($category = null, $value = null, $showEmbargoed = false);

	/**
	 * Returns a number of Document objects that match a full-text search of the Documents and their contents
	 * (if contents is searchable and compatible search module is installed - e.g. FullTextSearch module)
	 * @abstract
	 * @param $searchText String to search for
	 * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
	 * @return DMSDocumentInterface
	 */
	function getByFullTextSearch($searchText, $showEmbargoed = false);


	/**
	 * Returns a list of Document objects associated with a Page
	 * @abstract
	 * @param $page SiteTree to fetch the associated Documents from
	 * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
	 * @return DataList Document list associated with the Page
	 */
	function getByPage($page, $showEmbargoed = false);
}