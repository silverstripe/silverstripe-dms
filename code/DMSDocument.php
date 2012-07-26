<?php
class DMSDocument extends DataObject implements DMSDocumentInterface {

	static $db = array(
		"Filename" => "Varchar(255)", // eg. 3469~2011-energysaving-report.pdf
		"Folder" => "Varchar(255)",	// eg.	0
		"Title" => 'Varchar(1024)', // eg. "Energy Saving Report for Year 2011, New Zealand LandCorp"
		"Description" => 'Text',
		"LastChanged" => 'SS_DateTime' //when this document was created or last replaced (small changes like updating title don't count)
	);

	static $many_many = array(
		'Pages' => 'SiteTree',
		'Tags' => 'DMSTag'
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
			$currentTag = $this->Tags()->filter(array('Category' => $category, 'Value' => $value));
			if ($currentTag->Count() == 0) {
				//multi value tag
				$tag = new DMSTag();
				$tag->Category = $category;
				$tag->Value = $value;
				$tag->MultiValue = true;
				$tag->write();
				$tag->Documents()->add($this);
			} else {
				//add the relation between the tag and document
				foreach($currentTag as $tagObj) {
					$tagObj->Documents()->add($this);
				}
			}
		} else {
			//single value tag
			$currentTag = $this->Tags()->filter(array('Category' => $category));
			$tag = null;
			if ($currentTag->Count() == 0) {
				//create the single-value tag
				$tag = new DMSTag();
				$tag->Category = $category;
				$tag->Value = $value;
				$tag->MultiValue = false;
				$tag->write();
			} else {
				//update the single value tag
				$tag = $currentTag->first();
				$tag->Value = $value;
				$tag->MultiValue = false;
				$tag->write();
			}

			//regardless of whether we created a new tag or are just updating an existing one, add the relation
			$tag->Documents()->add($this);
		}
	}

	protected function getTagsObjects($category, $value = null) {
		$valueFilter = array("Category" => $category);
		if (!empty($value)) $valueFilter['Value'] = $value;

		if ($this->ID == 2) {
		Debug::Show($this);
		Debug::Show($this->Tags());
		}
		$tags = $this->Tags()->filter($valueFilter);
		return $tags;
	}

	/**
	 * Fetches all tags associated with this DMSDocument within a given category. If a value is specified this method
	 * tries to fetch that specific tag.
	 * @abstract
	 * @param $category String of the metadata category to get
	 * @param null $value String of the value of the tag to get
	 * @return array of Strings of all the tags or null if there is no match found
	 */
	function getTags($category, $value = null) {
		$tags = $this->getTagsObjects($category, $value);

		//convert DataList into array of Values
		$returnArray = null;
		if ($tags->Count() > 0) {
			$returnArray = array();
			foreach($tags as $t) {
				$returnArray[] = $t->Value;
			}
		}
		return $returnArray;
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
		$tags = $this->getTagsObjects($category, $value);

		if ($tags->Count() > 0) {
			$tagsToDelete = array();

			foreach($tags as $t) {
				$documentList = $t->Documents();

				//remove the relation between the tag and the document
				$documentList->remove($this);

				//delete the entire tag if it has no relations left
				if ($documentList->Count() == 0) $tagsToDelete[] = $t->ID;
			}

			//delete after the loop, so it doesn't conflict with the loop of the $tags list
			foreach($tagsToDelete as $tID) {
				$tag = DataObject::get_by_id("DMSTag",$tID);
				$tag->delete();
			}
		}
	}

	/**
	 * Deletes all tags associated with this Document.
	 * @return null
	 */
	function removeAllTags() {
		$allTags = $this->Tags();
		foreach($allTags as $tag) {
			if ($tag->Documents()->Count() == 0) $tag->delete();
		}
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

	/**
	 * Takes a File object or a String (path to a file) and copies it into the DMS, replacing the original document file
	 * but keeping the rest of the document unchanged.
	 * @param $file File object, or String that is path to a file to store
	 * @return DMSDocumentInstance Document object that we replaced the file in
	 */
	function replaceDocument($file) {
		// TODO: Implement replace() method.
	}

}