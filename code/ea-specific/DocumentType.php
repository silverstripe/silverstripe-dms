<?php

class DocumentType extends DataObject {
	static $db = array(
		'Name' => 'Varchar(255)'
	);

	static $has_many = array(
		'Documents' => 'EADocument'
	);
}
