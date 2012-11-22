<?php

class DMSDocumentAddController extends LeftAndMain {

	static $url_segment = 'pages/adddocument';
	static $url_priority = 60;
	static $required_permission_codes = 'CMS_ACCESS_AssetAdmin';
	static $menu_title = 'Edit Page';
	public static $tree_class = 'SiteTree';
	static $allowed_extensions = array();

	/**
	 * Add an array of additional allowed extensions
	 * @static
	 * @param $array
	 */
	static function add_allowed_extensions($array = null) {
		if (empty($array)) return;
		if (is_array($array)) self::$allowed_extensions = $array;
		else self::$allowed_extensions = array($array);
	}

	/**
	 * Custom currentPage() method to handle opening the 'root' folder
	 */
	public function currentPage() {
		$id = $this->currentPageID();

		if($id && is_numeric($id) && $id > 0) {
			return DataObject::get_by_id('SiteTree', $id);
		} else {
			// ID is either '0' or 'root'
			return singleton('SiteTree');
		}
	}

	/**
	 * Return fake-ID "root" if no ID is found (needed to upload files into the root-folder)
	 */
	public function currentPageID() {
		if(is_numeric($this->request->requestVar('ID')))	{
			return $this->request->requestVar('ID');
		} elseif (is_numeric($this->urlParams['ID'])) {
			return $this->urlParams['ID'];
		} elseif(Session::get("{$this->class}.currentPage")) {
			return Session::get("{$this->class}.currentPage");
		} else {
			return 0;
		}
	}

	/**
	 * @return Form
	 * @todo what template is used here? AssetAdmin_UploadContent.ss doesn't seem to be used anymore
	 */
	public function getEditForm($id = null, $fields = null) {
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/AssetUploadField.js');
		Requirements::css(FRAMEWORK_DIR . '/css/AssetUploadField.css');
		Requirements::css(DMS_DIR.'/css/DMSMainCMS.css');

		$page = $this->currentPage();

		$uploadField = DMSUploadField::create('AssetUploadField', '');
		$uploadField->setConfig('previewMaxWidth', 40);
		$uploadField->setConfig('previewMaxHeight', 30);
		// Required to avoid Solr reindexing (often used alongside DMS) to
		// return 503s because of too many concurrent reindex requests
		$uploadField->setConfig('sequentialUploads', 1);
		$uploadField->addExtraClass('ss-assetuploadfield');
		$uploadField->removeExtraClass('ss-uploadfield');
		$uploadField->setTemplate('AssetUploadField');
		$uploadField->setRecord($page);

		$uploadField->getValidator()->setAllowedExtensions(array_filter(array_merge(File::$allowed_extensions,self::$allowed_extensions)));
		$exts = $uploadField->getValidator()->getAllowedExtensions();

		asort($exts);
		$backlink = $this->Backlink();
		$done = "
		<a class=\"ss-ui-button ss-ui-action-constructive cms-panel-link ui-corner-all\" href=\"".$backlink."\">
			Done!
		</a>";

		$addExistingField = new DMSDocumentAddExistingField('AddExisting', 'Add Existing');
		$addExistingField->setRecord($page);
		$form = new Form(
			$this,
			'getEditForm',
			new FieldList(
				new TabSet('Main',
					new Tab('From your computer',
						new HiddenField('ID', false, $page->ID),
						$uploadField,
						new LiteralField(
							'AllowedExtensions',
							sprintf(
								'<p>%s: %s</p>',
								_t('AssetAdmin.ALLOWEDEXTS', 'Allowed extensions'),
								implode('<em>, </em>', $exts)
							)
						)
					),
					new Tab('From the CMS',
						$addExistingField
					)
				)
			),
			new FieldList(
				new LiteralField('doneButton', $done)
			)
		);
		$form->addExtraClass('center cms-edit-form ' . $this->BaseCSSClasses());
		$form->Backlink = $backlink;
		// Don't use AssetAdmin_EditForm, as it assumes a different panel structure
		$form->setTemplate($this->getTemplatesWithSuffix('_EditForm'));
		/*$form->Fields()->push(
			new LiteralField(
				'BackLink',
				sprintf(
					'<a href="%s" class="backlink ss-ui-button cms-panel-link" data-icon="back">%s</a>',
					Controller::join_links(singleton('AssetAdmin')->Link('show'), $folder ? $folder->ID : 0),
					_t('AssetAdmin.BackToFolder', 'Back to folder')
				)
			)
		);*/
		//$form->loadDataFrom($folder);

		return $form;
	}

	/**
	 * @return ArrayList
	 */
	public function Breadcrumbs($unlinked = false) {
		$items = parent::Breadcrumbs($unlinked);

		// The root element should explicitly point to the root node.
		$items[0]->Link = Controller::join_links(singleton('CMSPageEditController')->Link('show'), 0);

		// Enforce linkage of hierarchy to AssetAdmin
		foreach($items as $item) {
			$baselink = $this->Link('show');
			if(strpos($item->Link, $baselink) !== false) {
				$item->Link = str_replace($baselink, singleton('CMSPageEditController')->Link('show'), $item->Link);
			}
		}

		$items->push(new ArrayData(array(
			'Title' => 'Add Document',
			'Link' => $this->Link()
		)));
		
		return $items;
	}
	
	public function Backlink(){
		$pageID = $this->currentPageID();
		return singleton('CMSPagesController')->Link().'edit/show/'.$pageID;
	}

	public function documentautocomplete() {
		$term = $_GET['term'];
		$term_sql = Convert::raw2sql($term);
		$data = DataList::create('DMSDocument');
		$data->where("(\"ID\" LIKE '%$term_sql%' OR \"Filename\" LIKE '%$term_sql%' OR \"Title\" LIKE '%$term_sql%')");
		$data->limit(20);
		
		$return = array();
		foreach($data as $doc) {
			$return[] = array(
				'label' => $doc->ID . ' - ' . $doc->Title,
				'value' => $doc->ID
			);
		}


		return json_encode($return);
	}

	public function linkdocument() {
		$return = array('error' => _t('UploadField.FIELDNOTSET', 'Could not add document to page'));

		$page = $this->currentPage();
		if (!empty($page)) {
			$document = DataObject::get_by_id('DMSDocument', (int) $_GET['documentID']);
			$document->addPage($page);

			$buttonText = '<button class="ss-uploadfield-item-edit ss-ui-button ui-corner-all" title="Edit this document" data-icon="pencil">'.
				'Edit<span class="toggle-details"><span class="toggle-details-icon"></span></span></button>';

			// Collect all output data.
			$return = array(
				'id' => $document->ID,
				'name' => $document->getTitle(),
				'thumbnail_url' => $document->Icon($document->getExtension()),
				'edit_url' => $this->getEditForm()->Fields()->fieldByName('Main.From your computer.AssetUploadField')->getItemHandler($document->ID)->EditLink(),
				'size' => $document->getFileSizeFormatted(),
				'buttons' => $buttonText,
				'showeditform' => true
			);
		}

		return json_encode($return);
	}

	public function documentlist() {
		$page = DataObject::get_by_id('SiteTree', (int) $_GET['pageID']);
		if($page && $page->Documents()) {
			$list = '<ul>';

			foreach($page->Documents() as $document) {
				$list .= '<li><a class="add-document" data-document-id="' . $document->ID . '">';
				$list .= $document->ID . ' - ' . Convert::raw2xml($document->Title);
				$list .= '</a></li>';
			}

			$list .= '</ul>';

			return $list;
		} else {
			return '<p>There are no documents attached to the selected page.</p>';
		}
	}
}


?>