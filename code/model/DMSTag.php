<?php

/**
 * Hold a set of metadata category/value tags associated with a DMSDocument
 *
 * @package dms
 */
class DMSTag extends DataObject {

	private static $db = array(
		'Category' => 'Varchar(1024)',
		'Value' => 'Varchar(1024)',
		'MultiValue' => 'Boolean(1)'
	);

	private static $belongs_many_many = array(
		'Documents' => 'DMSDocument'
	);
}
