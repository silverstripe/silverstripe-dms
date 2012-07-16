<?php
/**
 * Interface for a Document used in the Document Management System. A document is create by storing a File object in an
 * instance of the DMSInterface. All write operations on the Document create a new relation, so there is no explicit
 * write() method that needs to be called.
 */
interface DocumentInterface {

	/**
	 * Deletes the document, its underlying file, as well as any tags related to this document.
	 * @abstract
	 * @return null
	 */
	function delete();

	/**
	 * Could be a simple wrapper around $myDoc->Pages()->add($myPage)
	 * @abstract
	 * @param $pageObject
	 * @return mixed
	 */
	function addPage($pageObject);
	function removePage($pageObject);
	function getPages();

	/**
	 * Can be implemented as a key/value store table (although it is more like category/value, because the same category can occur multiple times)
	 * @abstract
	 * @param $category
	 * @param $value
	 * @return mixed
	 */
	function addTag($category, $value, $multiValue = true);

	function addTags($twoDimensionalArray);
	function removeTag($category = null, $value = null);
	function removeAllTags();
	function getAllTags();

	function downloadLink();

	function getVersions();

	function embargo();
	function embargoUntilDate();
	function clearEmbargo();
	function expire();
	function expireAtDate();
	function clearExpiry();
}