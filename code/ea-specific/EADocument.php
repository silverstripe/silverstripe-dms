<?php
class EADocument extends DMSDocument {

	static $has_one = array(
		'DocumentType' => 'DocumentType',
		'ContentType' => 'ContentType'
	);

}

