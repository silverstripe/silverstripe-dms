<?php
/**
 * Interface for a DMSDocument used in the Document Management System. A DMSDocument is create by storing a File object in an
 * instance of the DMSInterface. All write operations on the DMSDocument create a new relation, so we never need to
 * explicitly call the write() method on the DMSDocument DataObject
 */
interface DMSDocumentInterface {

	/**
	 * Deletes the DMSDocument, its underlying file, as well as any tags related to this DMSDocument.
	 *
	 * @todo Can't be applied to classes which already implement the DataObjectInterface (naming conflict)
	 * 
	 * @abstract
	 * @return null
	 */
	// function delete();

	/**
	 * Associates this DMSDocument with a Page. This method does nothing if the association already exists.
	 * This could be a simple wrapper around $myDoc->Pages()->add($myPage) to add a many_many relation
	 * @abstract
	 * @param $pageObject Page object to associate this DMSDocument with
	 * @return null
	 */
	function addPage($pageObject);
	
	/**
	 * Associates this DMSDocument with a set of Pages. This method loops through a set of page ids, and then associates this
	 * DMSDocument with the individual Page with the each page id in the set
	 * @abstract
	 * @param $pageIDs array of page ids used for the page objects associate this DMSDocument with
	 * @return null
	 */
	function addPages($pageIDs);

	/**
	 * Removes the association between this DMSDocument and a Page. This method does nothing if the association does not exist.
	 * @abstract
	 * @param $pageObject Page object to remove the association to
	 * @return mixed
	 */
	function removePage($pageObject);

	/**
	 * Returns a list of the Page objects associated with this DMSDocument
	 * @abstract
	 * @return DataList
	 */
	function getPages();

	/**
	 * Removes all associated Pages from the DMSDocument
	 * @abstract
	 * @return null
	 */
	function removeAllPages();

	/**
	 * Adds a metadata tag to the DMSDocument. The tag has a category and a value.
	 * Each category can have multiple values by default. So: addTag("fruit","banana") addTag("fruit", "apple") will add two items.
	 * However, if the third parameter $multiValue is set to 'false', then all updates to a category only ever update a single value. So:
	 * addTag("fruit","banana") addTag("fruit", "apple") would result in a single metadata tag: fruit->apple.
	 * Can could be implemented as a key/value store table (although it is more like category/value, because the
	 * same category can occur multiple times)
	 * @abstract
	 * @param $category String of a metadata category to add (required)
	 * @param $value String of a metadata value to add (required)
	 * @param bool $multiValue Boolean that determines if the category is multi-value or single-value (optional)
	 * @return null
	 */
	function addTag($category, $value, $multiValue = true);

	/**
	 * Fetches all tags associated with this DMSDocument within a given category. If a value is specified this method
	 * tries to fetch that specific tag.
	 * @abstract
	 * @param $category String of the metadata category to get
	 * @param null $value String of the value of the tag to get
	 * @return array of Strings of all the tags or null if there is no match found
	 */
	function getTagsList($category, $value = null);

	/**
	 * Removes a tag from the DMSDocument. If you only set a category, then all values in that category are deleted.
	 * If you specify both a category and a value, then only that single category/value pair is deleted.
	 * Nothing happens if the category or the value do not exist.
	 * @abstract
	 * @param $category Category to remove (required)
	 * @param null $value Value to remove (optional)
	 * @return null
	 */
	function removeTag($category, $value = null);

	/**
	 * Deletes all tags associated with this DMSDocument.
	 * @abstract
	 * @return null
	 */
	function removeAllTags();

	/**
	 * Returns a link to download this DMSDocument from the DMS store
	 * @abstract
	 * @return String
	 */
	function getLink();
	
	/**
	 * Return the extension of the file associated with the document
	 */
	function getExtension();
	
	/**
	 * Returns the size of the file type in an appropriate format.
	 *
	 * @return string
	 */
	function getSize();

	/**
	 * Return the size of the file associated with the document, in bytes.
	 *
	 * @return int
	 */
	function getAbsoluteSize();


	/**
	 * Takes a File object or a String (path to a file) and copies it into the DMS, replacing the original document file
	 * but keeping the rest of the document unchanged.
	 * @param $file File object, or String that is path to a file to store
	 * @return DMSDocumentInstance Document object that we replaced the file in
	 */
	function replaceDocument($file);

	/**
	 * Hides the DMSDocument, so it does not show up when getByPage($myPage) is called
	 * (without specifying the $showEmbargoed = true parameter). This is similar to expire, except that this method
	 * should be used to hide DMSDocuments that have not yet gone live.
	 * @abstract
	 * @return null
	 */
	function embargoIndefinitely();

	/**
	 * Returns if this is DMSDocument is embargoed or expired.
	 * @abstract
	 * @return bool True or False depending on whether this DMSDocument is embargoed or expired
	 */
	function isHidden();


	/**
	 * Returns if this is DMSDocument is embargoed.
	 * @abstract
	 * @return bool True or False depending on whether this DMSDocument is embargoed
	 */
	function isEmbargoed();

	/**
	 * Hides the DMSDocument, so it does not show up when getByPage($myPage) is called. Automatically un-hides the
	 * DMSDocument at a specific date.
	 * @abstract
	 * @param $datetime String date time value when this DMSDocument should expire
	 * @return null
	 */
	function embargoUntilDate($datetime);

	/**
	 * Hides the document until any page it is linked to is published
	 * @return null
	 */
	function embargoUntilPublished();

	/**
	 * Clears any previously set embargos, so the DMSDocument always shows up in all queries.
	 * @abstract
	 * @return null
	 */
	function clearEmbargo();

	/**
	 * Returns if this is DMSDocument is expired.
	 * @abstract
	 * @return bool True or False depending on whether this DMSDocument is expired
	 */
	function isExpired();

	/**
	 * Hides the DMSDocument at a specific date, so it does not show up when getByPage($myPage) is called.
	 * @abstract
	 * @param $datetime String date time value when this DMSDocument should expire
	 * @return null
	 */
	function expireAtDate($datetime);

	/**
	 * Clears any previously set expiry.
	 * @abstract
	 * @return null
	 */
	function clearExpiry();

	/*---- FROM HERE ON: optional API features ----*/

	/**
	 * Returns a DataList of all previous Versions of this DMSDocument (check the LastEdited date of each
	 * object to find the correct one)
	 * @abstract
	 * @return DataList List of DMSDocument objects
	 */
	function getVersions();

}