<?php
class DMSSiteTreeExtension extends DataExtension {

	static $belongs_many_many = array(
		'Documents' => 'DMSDocument'
	);
}