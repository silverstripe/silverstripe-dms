<?php
define('DMS_DIR', 'dms');
if (!file_exists(BASE_PATH . DIRECTORY_SEPARATOR . DMS_DIR)) user_error("DMS directory named incorrectly. Please install the DMS module into a folder named: ".DMS_DIR);

Object::add_extension('SiteTree','DMSSiteTreeExtension');
Object::add_extension('HtmlEditorField_Toolbar','DocumentHtmlEditorFieldToolbar');
CMSMenu::remove_menu_item('DMSDocumentAddController');

ShortcodeParser::get('default')->register('dms_document_link', array('DMSDocument_Controller', 'dms_link_shortcode_handler'));

DMSDocument_versions::$enable_versions = true;

if (DMSDocument_versions::$enable_versions) {
	//using the same db relations for the versioned documents, as for the actual documents
	Config::inst()->update('DMSDocument_versions', 'db', DMSDocument::$db);
}

