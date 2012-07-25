<?php
/**
 * Hold a set of metadata category/value tags associated with a DMSDocument
 */
class DMSTag extends DataObject {

	static $db = array(
		'Category' => 'varchar(1024)',
		'Value' => 'varchar(1024)'
	);

	static $belongs_many_many = array(
		'Documents' => 'DMSDocument'
	);
}