<?php
class DMSSiteTreeExtension extends DataExtension {

	static $belongs_many_many = array(
		'Documents' => 'DMSDocument'
	);
	
	function updateCMSFields(FieldList $fields){
		//javascript to customize the grid field for the DMS document (overriding entwine in FRAMEWORK_DIR.'/javascript/GridField.js'
		Requirements::javascript('dms/javascript/DMSGridField.js');
		Requirements::css('dms/css/DMSMainCMS.css');

		//javascript for the link editor pop-up in TinyMCE
		Requirements::javascript("dms/javascript/DocumentHtmlEditorFieldToolbar.js");

		// Document listing
		$gridFieldConfig = GridFieldConfig::create()->addComponents(
			new GridFieldToolbarHeader(),
			new GridFieldFilterHeader(),
			new GridFieldSortableHeader(),
			new GridFieldDataColumns(),
			new GridFieldPaginator(15),
			new GridFieldEditButton(),
			new DMSGridFieldDeleteAction(), //special delete dialog to handle custom behaviour of unlinking and deleting
			new GridFieldDetailForm()
			//GridFieldLevelup::create($folder->ID)->setLinkSpec('admin/assets/show/%d')
		);

		// HACK: Create a singleton of DMSDocument to ensure extensions are applied before we try to get display fields.
		singleton('DMSDocument');
		$gridFieldConfig->getComponentByType('GridFieldDataColumns')->setDisplayFields(Config::inst()->get('DMSDocument', 'display_fields'))
			->setFieldCasting(array('LastChanged'=>"Date->Ago"))
 			->setFieldFormatting(array('FilenameWithoutID'=>'<a target=\'_blank\' class=\'file-url\' href=\'$DownloadLink\'>$FilenameWithoutID</a>'));

		//override delete functionality with this class
		$gridFieldConfig->getComponentByType('GridFieldDetailForm')->setItemRequestClass('DMSGridFieldDetailForm_ItemRequest');

		$gridField = GridField::create(
			'Documents', 
			false, 
			$this->owner->Documents(),
			$gridFieldConfig
		);

		$uploadBtn = new LiteralField(
			'UploadButton', 
			sprintf(
				'<a class="ss-ui-button ss-ui-action-constructive cms-panel-link" data-pjax-target="Content" data-icon="add" href="%s">%s</a>',
				Controller::join_links(singleton('DMSDocumentAddController')->Link(), '?ID=' . $this->owner->ID),
				"Add Documents"
			)
		);	

		$fields->addFieldsToTab(
			'Root.Documents',
			array(
				$uploadBtn,
				$gridField
			)
		);
	}

	function onBeforeDelete() {
		$dmsDocuments = $this->owner->Documents();
		foreach($dmsDocuments as $document) {
			//if the document is only associated with one page, i.e. only associated with this page
			if ($document->Pages()->Count() <= 1) {
				//delete the document before deleting this page
				$document->delete();
			}
		}
	}

	function onBeforePublish() {
		$embargoedDocuments = $this->owner->Documents()->filter('EmbargoedUntilPublished',true);
		if ($embargoedDocuments->Count() > 0) {
			foreach($embargoedDocuments as $doc) {
				$doc->EmbargoedUntilPublished = false;
				$doc->write();
			}
		}

	}

	function getTitleWithNumberOfDocuments() {
		return $this->owner->Title . ' (' . $this->owner->Documents()->Count() . ')';
	}
}