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
	 * Factory method that returns an instance of the DMS. This could be anything that implements the DMSInterface
	 * @static
	 * @abstract
	 * @return DMSinstance
	 */
	static function getDMSInstance();

	/**
	 * When storing a document, sets the fields on the File has "tag" metadata. E.g: filename, path, etc. all become
	 * single-value tags on the Document.
	 * @abstract
	 * @param File $file
	 * @return mixed
	 */
	function storeDocument(File $file);

	function getByTag($category = null, $value = null);
	function getByFullTextSearch($searchText);
	function getByTitle($searchTitle);

}