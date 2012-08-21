<?php
Object::add_extension('SiteTree','DMSSiteTreeExtension');
Object::add_extension('HtmlEditorField_Toolbar','DocumentHtmlEditorFieldToolbar');
CMSMenu::remove_menu_item('DMSDocumentAddController');

ShortcodeParser::get('default')->register('dms_document_link', array('DMSDocument_Controller', 'dms_link_shortcode_handler'));