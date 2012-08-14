<?php
class DMSDocument extends DataObject implements DMSDocumentInterface {
	static $db = array(
		"Filename" => "Varchar(255)", // eg. 3469~2011-energysaving-report.pdf
		"Folder" => "Varchar(255)",	// eg.	0
		"Title" => 'Varchar(1024)', // eg. "Energy Saving Report for Year 2011, New Zealand LandCorp"
		"Description" => 'Text',
		"LastChanged" => 'SS_DateTime', //when this document's file was created or last replaced (small changes like updating title don't count)

		"EmbargoedIndefinitely" => 'Boolean(false)',
		"EmbargoedUntilPublished" => 'Boolean(false)',
		"EmbargoedUntilDate" => 'SS_DateTime',
		"ExpireAtDate" => 'SS_DateTime'
	);

	static $many_many = array(
		'Pages' => 'SiteTree',
		'Tags' => 'DMSTag'
	);
	
	static $display_fields = array(
		'ID'=>'ID',
		'Title'=>'Title',
		'FilenameWithoutID'=>'Filename',
		'LastChanged'=>'LastChanged'
	);

	/**
	 * Associates this document with a Page. This method does nothing if the association already exists.
	 * This could be a simple wrapper around $myDoc->Pages()->add($myPage) to add a many_many relation
	 * @param $pageObject Page object to associate this Document with
	 * @return null
	 */
	function addPage($pageObject) {
		$this->Pages()->add($pageObject);
	}
	
	/**
	 * Associates this DMSDocument with a set of Pages. This method loops through a set of page ids, and then associates this
	 * DMSDocument with the individual Page with the each page id in the set
	 * @abstract
	 * @param $pageIDs array of page ids used for the page objects associate this DMSDocument with
	 * @return null
	 */
	function addPages($pageIDs){
		foreach($pageIDs as $id){
			$pageObject = DataObject::get_by_id("SiteTree", $id);
			if($pageObject && $pageObject->exists()) {
				$this->addPage($pageObject);
			}
		}
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
	function getTagsList($category, $value = null) {
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
			foreach($tags as $t) {
				$documentList = $t->Documents();

				//remove the relation between the tag and the document
				$documentList->remove($this);

				//delete the entire tag if it has no relations left
				if ($documentList->Count() == 0) $t->delete();
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
			$documentlist = $tag->Documents();
			$documentlist->remove($this);
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
	function getDownloadLink() {
		return Controller::join_links(Director::baseURL(),'dmsdocument/'.$this->ID);
	}

	/**
	 * Hides the document, so it does not show up when getByPage($myPage) is called
	 * (without specifying the $showEmbargoed = true parameter). This is similar to expire, except that this method
	 * should be used to hide documents that have not yet gone live.
	 * @return null
	 */
	function embargoIndefinitely($write = true) {
		$this->EmbargoedIndefinitely = true;
		if ($write) $this->write();
	}

	/**
	 * Hides the document until any page it is linked to is published
	 * @return null
	 */
	function embargoUntilPublished($write = true) {
		$this->EmbargoedUntilPublished = true;
		if ($write) $this->write();
	}

	/**
	 * Returns if this is Document is embargoed or expired.
	 * @return bool True or False depending on whether this document is embargoed
	 */
	function isHidden() {
		return $this->isEmbargoed() || $this->isExpired();
	}

	/**
	 * Returns if this is Document is embargoed.
	 * @return bool True or False depending on whether this document is embargoed
	 */
	function isEmbargoed() {
		if (is_object($this->EmbargoedUntilDate)) $this->EmbargoedUntilDate = $this->EmbargoedUntilDate->Value;

		$embargoed = false;
		if ($this->EmbargoedIndefinitely) $embargoed = true;
		elseif ($this->EmbargoedUntilPublished) $embargoed = true;
		elseif (!empty($this->EmbargoedUntilDate) && SS_Datetime::now()->Value < $this->EmbargoedUntilDate) $embargoed = true;

		return $embargoed;
	}

	/**
	 * Hides the document, so it does not show up when getByPage($myPage) is called. Automatically un-hides the
	 * Document at a specific date.
	 * @param $datetime String date time value when this Document should expire
	 * @return null
	 */
	function embargoUntilDate($datetime, $write = true) {
		$this->EmbargoedUntilDate = DBField::create_field('SS_Datetime', $datetime);;
		if ($write) $this->write();
	}

	/**
	 * Clears any previously set embargos, so the Document always shows up in all queries.
	 * @return null
	 */
	function clearEmbargo($write = true) {
		$this->EmbargoedIndefinitely = false;
		$this->EmbargoedUntilPublished = false;
		$this->EmbargoedUntilDate = null;
		if ($write) $this->write();
	}

	/**
	 * Returns if this is Document is expired.
	 * @return bool True or False depending on whether this document is expired
	 */
	function isExpired() {
		if (is_object($this->ExpireAtDate)) $this->ExpireAtDate = $this->ExpireAtDate->Value;

		$expired = false;
		if (!empty($this->ExpireAtDate) && SS_Datetime::now()->Value >= $this->ExpireAtDate) $expired = true;

		return $expired;
	}

	/**
	 * Hides the document at a specific date, so it does not show up when getByPage($myPage) is called.
	 * @param $datetime String date time value when this Document should expire
	 * @return null
	 */
	function expireAtDate($datetime, $write = true) {
		$this->ExpireAtDate = DBField::create_field('SS_Datetime', $datetime);
		if ($write) $this->write();
	}

	/**
	 * Clears any previously set expiry.
	 * @return null
	 */
	function clearExpiry($write = true) {
		$this->ExpireAtDate = null;
		if ($write) $this->write();
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
		return DMS::get_dms_path() . DIRECTORY_SEPARATOR . $this->Folder . DIRECTORY_SEPARATOR . $this->Filename;
	}

	/**
	 * Deletes the DMSDocument, its underlying file, as well as any tags related to this DMSDocument. Also calls the
	 * parent DataObject's delete method.
	 */
	function delete() {
		//remove tags
		$this->removeAllTags();

		//delete the file (and previous versions of files)
		$filesToDelete = array();
		$storageFolder = DMS::get_dms_path() . DIRECTORY_SEPARATOR . DMS::get_storage_folder($this->ID);
		if (file_exists($storageFolder)) {
			if ($handle = opendir($storageFolder)) { //Open directory
				//List files in the directory
				while (false !== ($entry = readdir($handle))) {
					if(strpos($entry,$this->ID.'~') !== false) $filesToDelete[] = $entry;
				}
				closedir($handle);

				//delete all this files that have the id of this document
				foreach($filesToDelete as $file) {
					$filePath = $storageFolder .DIRECTORY_SEPARATOR . $file;
					if (is_file($filePath)) unlink($filePath);
				}
			}
		}

		$this->removeAllPages();

		//delete the dataobject
		parent::delete();
	}

	/**
	 * Relate an existing file on the filesystem to the document.
	 * Copies the file to the new destination, as defined in {@link get_DMS_path()}.
	 *
	 * @param String Path to file, relative to webroot.
	 */
	function storeDocument($filePath) {
		if (empty($this->ID)) user_error("Document must be written to database before it can store documents",E_USER_ERROR);

		//calculate all the path to copy the file to
		$fromFilename = basename($filePath);
		$toFilename = $this->ID. '~' . $fromFilename; //add the docID to the start of the Filename
		$toFolder = DMS::get_storage_folder($this->ID);
		$toPath = DMS::get_dms_path() . DIRECTORY_SEPARATOR . $toFolder . DIRECTORY_SEPARATOR . $toFilename;
		DMS::create_storage_folder(DMS::get_dms_path() . DIRECTORY_SEPARATOR . $toFolder);

		//copy the file into place
		$fromPath = BASE_PATH . DIRECTORY_SEPARATOR . $filePath;
		copy($fromPath, $toPath);   //this will overwrite the existing file (if present)

		//write the filename of the stored document
		$this->Filename = $toFilename;
		$this->Folder = $toFolder;
		if (empty($this->Title)) $this->Title = $fromFilename; //don't overwrite existing document titles
		$this->LastChanged = SS_Datetime::now()->Rfc2822();

		$this->write();

		return $this;
	}

	/**
	 * Takes a File object or a String (path to a file) and copies it into the DMS, replacing the original document file
	 * but keeping the rest of the document unchanged.
	 * @param $file File object, or String that is path to a file to store
	 * @return DMSDocumentInstance Document object that we replaced the file in
	 */
	function replaceDocument($file) {
		$filePath = DMS::transform_file_to_file_path($file);
		$doc = $this->storeDocument($filePath); //replace the document
		return $doc;
	}


	/**
	 * Return the type of file for the given extension
	 * on the current file name.
	 *
	 * @return string
	 */
	static function get_file_type($ext) {
		$types = array(
			'gif' => 'GIF image - good for diagrams',
			'jpg' => 'JPEG image - good for photos',
			'jpeg' => 'JPEG image - good for photos',
			'png' => 'PNG image - good general-purpose format',
			'ico' => 'Icon image',
			'tiff' => 'Tagged image format',
			'doc' => 'Word document',
			'xls' => 'Excel spreadsheet',
			'zip' => 'ZIP compressed file',
			'gz' => 'GZIP compressed file',
			'dmg' => 'Apple disk image',
			'pdf' => 'Adobe Acrobat PDF file',
			'mp3' => 'MP3 audio file',
			'wav' => 'WAV audo file',
			'avi' => 'AVI video file',
			'mpg' => 'MPEG video file',
			'mpeg' => 'MPEG video file',
			'js' => 'Javascript file',
			'css' => 'CSS file',
			'html' => 'HTML file',
			'htm' => 'HTML file'
		);

		return isset($types[$ext]) ? $types[$ext] : $ext;
	}

	function getFilenameWithoutID() {
		$filenameParts = explode('~',$this->Filename);
		$filename = array_pop($filenameParts);
		return $filename;
	}


	function getCMSFields() {
		//include JS to handling showing and hiding of bottom "action" tabs
		Requirements::javascript('dms/javascript/DMSDocumentCMSFields.js');
		Requirements::css('dms/css/DMSDocumentCMSFields.css');

		$fields = new FieldList();  //don't use the automatic scaffolding, it is slow and unnecessary here

		$fieldsTop = $this->getFieldsForFile();
		$fields->add($fieldsTop);

		$fields->add(new TextField('Title','Title'));
		$fields->add(new TextareaField('Description','Description'));

		//create upload field to replace document
		$uploadField = new DMSUploadField('ReplaceFile', 'Replace file');
		$uploadField->setConfig('allowedMaxFileNumber', 1);
		$uploadField->setConfig('useDMSReplaceTemplate', 1);
		$uploadField->setRecord($this);


		$gridFieldConfig = GridFieldConfig::create()->addComponents(
			new GridFieldToolbarHeader(),
			new GridFieldSortableHeader(),
			new GridFieldDataColumns(),
			new GridFieldPaginator(30),
			//new GridFieldEditButton(),
			new GridFieldDetailForm()
		);
		$gridFieldConfig->getComponentByType('GridFieldDataColumns')
			->setDisplayFields(array(
				'Title'=>'Title',
				'ClassName'=>'Page Type',
				'ID'=>'Page ID'
			))
			->setFieldFormatting(array(
				'Title'=>sprintf(
					'<a class=\"cms-panel-link\" href=\"%s/$ID\">$Title</a>',
					singleton('CMSPageEditController')->Link('show')
				)
			));
		$pagesGrid = GridField::create(
			'Pages',
			_t('DMSDocument.RelatedPages', 'Related Pages'),
			$this->Pages(),
			$gridFieldConfig
		);

		$fields->add(new LiteralField('BottomTaskSelection',
			'<div id="Actions" class="field actions"><label class="left">Actions</label><ul>'.
			'<li class="ss-ui-button" data-panel="embargo">Embargo</li>'.
			'<li class="ss-ui-button" data-panel="expiry">Expiry</li>'.
			'<li class="ss-ui-button" data-panel="replace">Replace</li>'.
			'<li class="ss-ui-button" data-panel="find-usage">Find usage</li>'.
			'</ul></div>'));

		$embargoValue = 'None';
		if ($this->EmbargoedIndefinitely) $embargoValue = 'Indefinitely';
		elseif ($this->EmbargoedUntilPublished) $embargoValue = 'Published';
		elseif (!empty($this->EmbargoedUntilDate)) $embargoValue = 'Date';
		$embargo = new OptionsetField('Embargo','Embargo',array('None'=>'None','Published'=>'Hide document until page is published','Indefinitely'=>'Hide document indefinitely','Date'=>'Hide until set date'),$embargoValue);
		$embargoDatetime = DatetimeField::create('EmbargoedUntilDate','');
		$embargoDatetime->getDateField()->setConfig('showcalendar', true)->setConfig('dateformat', 'yyyy-MM-dd')->setConfig('datavalueformat', 'yyyy-MM-dd');

		$expiryValue = 'None';
		if (!empty($this->ExpireAtDate)) $expiryValue = 'Date';
		$expiry = new OptionsetField('Expiry','Expiry',array('None'=>'None','Date'=>'Set document to expire on'),$expiryValue);
		$expiryDatetime = DatetimeField::create('ExpireAtDate','');
		$expiryDatetime->getDateField()->setConfig('showcalendar', true)->setConfig('dateformat', 'yyyy-MM-dd')->setConfig('datavalueformat', 'yyyy-MM-dd');

		// This adds all the actions details into a group.
		// Embargo, History, etc to go in here
		// These are toggled on and off via the Actions Buttons above
		$fields->add(FieldGroup::create(
				FieldGroup::create(
					$embargo,
					$embargoDatetime
				)->addExtraClass('embargo'),
				FieldGroup::create(
					$expiry,
					$expiryDatetime
				)->addExtraClass('expiry'),
				$uploadField->addExtraClass('replace'),
				$pagesGrid->addExtraClass('find-usage')
		)->setName("ActionsPanel")->addExtraClass('dmsupload ss-uploadfield'));
		return $fields;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();

		if (isset($this->Embargo)) {
			//set the embargo options from the OptionSetField created in the getCMSFields method
			//do not write after clearing the embargo (write happens automatically)
			$savedDate = $this->EmbargoedUntilDate;
			$this->clearEmbargo(false); //clear all previous settings and re-apply them on save

			if ($this->Embargo == 'Published') $this->embargoUntilPublished(false);
			if ($this->Embargo == 'Indefinitely') $this->embargoIndefinitely(false);
			if ($this->Embargo == 'Date') $this->embargoUntilDate($savedDate, false);
		}

		if (isset($this->Expiry)) {
			if ($this->Expiry == 'Date') $this->expireAtDate($this->ExpireAtDate, false);
			else $this->clearExpiry(false); //clear all previous settings
		}
	}

	/**
	 * Return the relative URL of an icon for the file type,
	 * based on the {@link appCategory()} value.
	 * Images are searched for in "dms/images/app_icons/".
	 *
	 * @return String
	 */
	function Icon($ext) {
		if(!Director::fileExists("dms/images/app_icons/{$ext}_32.png")) {
			$ext = File::get_app_category($ext);
		}

		if(!Director::fileExists("dms/images/app_icons/{$ext}_32.png")) {
			$ext = "generic";
		}

		return "dms/images/app_icons/{$ext}_32.png";
	}
	
	/**
	 * Return the extension of the file associated with the document
	 */
	function getFileExt() {
		return strtolower(pathinfo($this->Filename, PATHINFO_EXTENSION));
	}
	
	/**
	 * Return the size of the file associated with the document
	 */
	function getFileSize() {
		return filesize($this->getFullPath());
	}
	
	function getFileSizeFormatted(){
		if($size = $this->getFileSize()){
			if($size < 1024) return $size . ' bytes';
			if($size < 1024*10) return (round($size/1024*10)/10). ' KB';
			if($size < 1024*1024) return round($size/1024) . ' KB';
			if($size < 1024*1024*10) return (round(($size/1024)/1024*10)/10) . ' MB';
			if($size < 1024*1024*1024) return round(($size/1024)/1024) . ' MB';
			return round($size/(1024*1024*1024)*10)/10 . ' GB';
		}
	}


	/**
	 * @return FieldList
	 */
	protected function getFieldsForFile() {
		$extension = $this->getFileExt();

		$previewField = new LiteralField("ImageFull",
			"<img id='thumbnailImage' class='thumbnail-preview' src='{$this->Icon($extension)}?r=" . rand(1,100000)  . "' alt='{$this->Title}' />\n"
		);

		//count the number of pages this document is published on
		$publishedOnCount = $this->Pages()->Count();
		$publishedOnValue = "$publishedOnCount pages";
		if ($publishedOnCount == 1) $publishedOnValue = "$publishedOnCount page";

		$fields = new FieldGroup(
			$filePreview = CompositeField::create(
				CompositeField::create(
					$previewField
				)->setName("FilePreviewImage")->addExtraClass('cms-file-info-preview'),
				CompositeField::create(
					CompositeField::create(
						new ReadonlyField("ID", "ID number". ':', $this->ID),
						new ReadonlyField("FileType", _t('AssetTableField.TYPE','File type') . ':', self::get_file_type($extension)),
						new ReadonlyField("Size", _t('AssetTableField.SIZE','File size') . ':', $this->getFileSizeFormatted()),
						$urlField = new ReadonlyField('ClickableURL', _t('AssetTableField.URL','URL'),
							sprintf('<a href="%s" target="_blank" class="file-url">%s</a>', $this->getDownloadLink(), $this->getDownloadLink())
						),
						new ReadonlyField("FilenameWithoutIDField", "Filename". ':', $this->getFilenameWithoutID()),
						new DateField_Disabled("Created", _t('AssetTableField.CREATED','First uploaded') . ':', $this->Created),
						new DateField_Disabled("LastEdited", _t('AssetTableField.LASTEDIT','Last changed') . ':', $this->LastEdited),
						new DateField_Disabled("LastChanged", _t('AssetTableField.LASTCHANGED','Last replaced') . ':', $this->LastChanged),
						new ReadonlyField("PublishedOn", "Published on". ':', $publishedOnValue)
					)
				)->setName("FilePreviewData")->addExtraClass('cms-file-info-data')
			)->setName("FilePreview")->addExtraClass('cms-file-info')
		);
		$fields->setName('FileP');
		$urlField->dontEscape = true;

		return $fields;
	}

	/**
	 * Takes a file and adds it to the DMSDocument storage, replacing the current file.
	 * @param $file File to ingest
	 */
	function ingestFile($file) {
		$this->replaceDocument($file);
		$file->delete();
	}


	/**
	 * Returns if the document should be displayed on the front-end. Respecting the current reading mode
	 * of the site and the embargo status.
	 * I.e. if a document is embargoed until published, then it should still show up in draft mode.
	 */
	function getDisplayDocument() {
		$display = !$this->isHidden();
		$readingMode = Versioned::get_reading_mode();
		if ($readingMode == "Stage.Stage" && $this->EmbargoedUntilPublished == true) $display = true;
		return $display;
	}
}

class DMSDocument_Controller extends Controller {

	static $testMode = false;   //mode to switch for testing. Does not return document download, just document URL

	static $allowed_actions = array(
		'index'
	);

	/**
	 * Returns the document object from the request object's ID parameter.
	 * Returns null, if no document found
	 */
	protected function getDocumentFromID($request) {
		$doc = null;
		$id = Convert::raw2sql(intval($request->param('ID')));
		$doc = DataObject::get_by_id('DMSDocument', $id);
		return $doc;
	}

	/**
	 * Access the file download without redirecting user, so we can block direct access to documents.
	 */
	function index(SS_HTTPRequest $request) {
		$doc = $this->getDocumentFromID($request);

		if (!empty($doc)) {
			$canView = false;

			//Runs through all pages that this page links to and sets canView to true if the user can view ONE of these pages
			$pages = $doc->Pages();
			if ($pages->Count() > 0) {
				foreach($pages as $page) {
					if ($page->CanView()) {
						$canView = true;    //just one canView is enough to know that we can view the file
						break;
					}
				}
			} else {
				//if the document isn't on any page, then allow viewing of the document (because there is no canView() to consult)
				$canView = true;
			}

			// check for embargo or expiry
			if ($doc->isHidden()) $canView = false;

			if ($canView) {
				$path = $doc->getFullPath();
				if ( is_file($path) ) {
					$fileBin = trim(`whereis file`);
					if ( function_exists('finfo_file') ) {
						// discover the mime type properly
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$mime = finfo_file($finfo, $path);
					}
					else if ( is_executable($fileBin) ) {
						// try to use the system tool
						$mime = `$fileBin -i -b $path`;
						$mime = explode(';', $mime);
						$mime = trim($mime[0]);
					}
					else {
						// make do with what we have
						$ext = $doc->getFileExt();
						if ( $ext =='pdf') {
							$mime = 'application/pdf';
						}elseif ($ext == 'html' || $ext =='htm') {
							$mime = 'text/html';
						}else {
							$mime = 'application/octet-stream';
						}
					}

					if (self::$testMode) return $path;

					header('Content-Type: ' . $mime);
					header('Content-Length: ' . filesize($path), null);
					if (!empty($mime) && $mime != "text/html") header('Content-Disposition: attachment; filename="'.$doc->getFilenameWithoutID().'"');
					header('Content-transfer-encoding: 8bit');
					header('Expires: 0');
					header('Pragma: cache');
					header('Cache-Control: private');
					flush();
					readfile($path);
					exit;
				}
			}
		}

		if (self::$testMode) return 'This asset does not exist.';
		$this->httpError(404, 'This asset does not exist.');
	}


}

