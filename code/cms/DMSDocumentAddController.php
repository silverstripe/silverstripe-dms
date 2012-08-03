<?php

class DMSDocumentAddController extends LeftAndMain {

	static $url_segment = 'pages/adddocument';
	static $url_priority = 60;
	static $required_permission_codes = 'CMS_ACCESS_AssetAdmin';
	static $menu_title = 'Edit Page';
	public static $tree_class = 'SiteTree';
	
//	public function upload($request) {
//		$formHtml = $this->renderWith(array('AssetAdmin_UploadContent'));
//		if($request->isAjax()) {
//			return $formHtml;
//		} else {
//			return $this->customise(array(
//				'Content' => $formHtml
//			))->renderWith(array('AssetAdmin', 'LeftAndMain'));
//		}
//	}

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

		$page = $this->currentPage();

		$uploadField = DMSUploadField::create('AssetUploadField', '');
		$uploadField->setConfig('previewMaxWidth', 40);
		$uploadField->setConfig('previewMaxHeight', 30);
		$uploadField->addExtraClass('ss-assetuploadfield');
		$uploadField->removeExtraClass('ss-uploadfield');
		$uploadField->setTemplate('AssetUploadField');
		$uploadField->setRecord($page);

		$exts = $uploadField->getValidator()->getAllowedExtensions();
		asort($exts);
		$backlink = $this->Backlink();
		$done = "
		<a class=\"ss-ui-button ss-ui-action-constructive cms-panel-link ui-corner-all\" href=\"".$backlink."\">
			Done!
		</a>";
		$form = new Form(
			$this,
			'getEditForm',
			new FieldList(
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

}


?>