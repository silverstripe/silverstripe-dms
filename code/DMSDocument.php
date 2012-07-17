<?php
class DMSDocument extends DataObject implements DMSDocumentInterface {

	static $db = array(
		"Filename" => "Text",
		"Folder" => "Text"
	);

	static $has_many = array(
		'Tags' => 'DMSTag'
	);

	static $many_many = array(
		'Pages' => 'SiteTree'
	);

	/**
	 * Associates this document with a Page. This method does nothing if the association already exists.
	 * This could be a simple wrapper around $myDoc->Pages()->add($myPage) to add a has_many relation
	 * @param $pageObject Page object to associate this Document with
	 * @return null
	 */
	function addPage($pageObject) {
		$this->Pages()->add($pageObject);
	}

	/**
	 * Removes the association between this Document and a Page. This method does nothing if the association does not exist.
	 * @param $pageObject Page object to remove the association to
	 * @return mixed
	 */
	function removePage($pageObject) {
		$this->Pages()->remove($pageObject);
	}

	/**
	 * Returns a list of the Page objects associated with this Document
	 * @return DataList
	 */
	function getPages() {
		$this->Pages();
	}

	/**
	 * Removes all associated Pages from the DMSDocument
	 * @return null
	 */
	function removeAllPages() {
		$this->Pages()->removeAll();
	}

	/**
	 * Adds a metadata tag to the Document. The tag has a category and a value.
	 * Each category can have multiple values by default. So: addTag("fruit","banana") addTag("fruit", "apple") will add two items.
	 * However, if the third parameter $multiValue is set to 'false', then all updates to a category only ever update a single value. So:
	 * addTag("fruit","banana") addTag("fruit", "apple") would result in a single metadata tag: fruit->apple.
	 * Can could be implemented as a key/value store table (although it is more like category/value, because the
	 * same category can occur multiple times)
	 * @param $category String of a metadata category to add (required)
	 * @param $value String of a metadata value to add (required)
	 * @param bool $multiValue Boolean that determines if the category is multi-value or single-value (optional)
	 * @return null
	 */
	function addTag($category, $value, $multiValue = true) {
		if ($multiValue) {
			//check for a duplicate tag, don't add the duplicate
			$currentTag = $this->Tags()->filter("Category = '$category' AND Value = '$value'");
			if (!$currentTag) {
				//multi value tag
				$tag = new DMSTag();
				$tag->Category = $category;
				$tag->Value = $value;
				$tag->DocumentID = $this->ID;
				$tag->write();
			}
		} else {
			//single value tag
			$currentTag = $this->Tags()->filter("Category = '$category'");
			if (!$currentTag) {
				//create the single-value tag
				$tag = new DMSTag();
				$tag->Category = $category;
				$tag->Value = $value;
				$tag->DocumentID = $this->ID;
				$tag->write();
			} else {
				//update the single value tag
				$tag = $currentTag->first();
				$tag->Value = $value;
				$tag->write();
			}
		}
	}

	/**
	 * Quick way to add multiple tags to a Document. This takes a multidimensional array of category/value pairs.
	 * The array should look like this:
	 * $twoDimensionalArray = new array(
	 *      array('fruit','banana'),
	 *      array('fruit','apple')
	 * );
	 * @param $twoDimensionalArray array containing a list of arrays
	 * @param bool $multiValue Boolean that determines if the category is multi-value or single-value (optional)
	 * @return null
	 */
	function addTags($twoDimensionalArray, $multiValue = true) {
		// TODO: Implement addTags() method.
	}

	/**
	 * Removes a tag from the Document. If you only set a category, then all values in that category are deleted.
	 * If you specify both a category and a value, then only that single category/value pair is deleted.
	 * Nothing happens if the category or the value do not exist.
	 * @param $category Category to remove (required)
	 * @param null $value Value to remove (optional)
	 * @return null
	 */
	function removeTag($category, $value = null) {
		// TODO: Implement removeTag() method.
	}

	/**
	 * Deletes all tags associated with this Document.
	 * @return null
	 */
	function removeAllTags() {
		// TODO: Implement removeAllTags() method.
	}

	/**
	 * Returns a multi-dimensional array containing all Tags associated with this Document. The array has the
	 * following structure:
	 * $twoDimensionalArray = new array(
	 *      array('fruit','banana'),
	 *      array('fruit','apple')
	 * );
	 * @return array Multi-dimensional array of tags
	 */
	function getAllTags() {
		// TODO: Implement getAllTags() method.
	}

	/**
	 * Returns a link to download this document from the DMS store
	 * @return String
	 */
	function downloadLink() {
		// TODO: Implement downloadLink() method.
	}

	/**
	 * Hides the document, so it does not show up when getByPage($myPage) is called
	 * (without specifying the $showEmbargoed = true parameter). This is similar to expire, except that this method
	 * should be used to hide documents that have not yet gone live.
	 * @return null
	 */
	function embargo() {
		// TODO: Implement embargo() method.
	}

	/**
	 * Returns if this is Document is embargoed.
	 * @return bool True or False depending on whether this document is embargoed
	 */
	function isEmbargoed() {
		// TODO: Implement isEmbargoed() method.
	}

	/**
	 * Hides the document, so it does not show up when getByPage($myPage) is called. Automatically un-hides the
	 * Document at a specific date.
	 * @param $datetime String date time value when this Document should expire
	 * @return null
	 */
	function embargoUntilDate($datetime) {
		// TODO: Implement embargoUntilDate() method.
	}

	/**
	 * Clears any previously set embargos, so the Document always shows up in all queries.
	 * @return null
	 */
	function clearEmbargo() {
		// TODO: Implement clearEmbargo() method.
	}

	/**
	 * Hides the document, so it does not show up when getByPage($myPage) is called.
	 * (without specifying the $showEmbargoed = true parameter). This is similar to embargo, except that it should be
	 * used to hide documents that are no longer useful.
	 * @return null
	 */
	function expire() {
		// TODO: Implement expire() method.
	}

	/**
	 * Returns if this is Document is expired.
	 * @return bool True or False depending on whether this document is expired
	 */
	function isExpired() {
		// TODO: Implement isExpired() method.
	}

	/**
	 * Hides the document at a specific date, so it does not show up when getByPage($myPage) is called.
	 * @param $datetime String date time value when this Document should expire
	 * @return null
	 */
	function expireAtDate($datetime) {
		// TODO: Implement expireAtDate() method.
	}

	/**
	 * Clears any previously set expiry.
	 * @return null
	 */
	function clearExpiry() {
		// TODO: Implement clearExpiry() method.
	}

	/**
	 * Returns a DataList of all previous Versions of this document (check the LastEdited date of each
	 * object to find the correct one)
	 * @return DataList List of Document objects
	 */
	function getVersions() {
		// TODO: Implement getVersions() method.
	}

	/**
	 * Returns the full filename of the document stored in this object
	 * @return string
	 */
	function getFullPath() {
		return DMS::$dmsPath . DIRECTORY_SEPARATOR . $this->Folder . DIRECTORY_SEPARATOR . $this->Filename;
	}

	/**
	 * Deletes the DMSDocument, its underlying file, as well as any tags related to this DMSDocument. Also calls the
	 * parent DataObject's delete method.
	 */
	function delete() {
		//remove tags
		$this->removeAllTags();

		//delete the file
		unlink($this->getFullPath());

		$this->removeAllPages();

		//delete the dataobject
		parent::delete();
	}

}