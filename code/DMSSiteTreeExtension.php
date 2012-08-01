<?php
class DMSSiteTreeExtension extends DataExtension {

	static $belongs_many_many = array(
		'Documents' => 'DMSDocument'
	);
	
	function updateCMSFields(FieldList $fields){
		//javascript to customize the grid field for the DMS document (overriding entwine in FRAMEWORK_DIR.'/javascript/GridField.js'
		Requirements::javascript('dms/javascript/DMSGridField.js');

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
		$modelClass = DMS::$modelClass;
		$gridFieldConfig->getComponentByType('GridFieldDataColumns')->setDisplayFields($modelClass::$display_fields)
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
				"Add Document"
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
}