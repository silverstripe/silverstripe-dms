<?php
class DMSSiteTreeExtension extends DataExtension {

	static $belongs_many_many = array(
		'Documents' => 'DMSDocument'
	);
	
	function updateCMSFields(FieldList $fields){
		$documentsListConfig = GridFieldConfig_RecordEditor::create();
		$modelClass = DMS::$modelClass;
		$documentsListConfig->getComponentByType('GridFieldDataColumns')->setDisplayFields($modelClass::$display_fields);
		
		$fields->addFieldToTab(
			'Root.Documents', 
			GridField::create(
				'Documents',
				false, 
				$this->owner->Documents(),
				$documentsListConfig
			)
		);
	}
}