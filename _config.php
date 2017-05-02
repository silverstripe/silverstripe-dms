<?php

$config = Config::inst();

define('DMS_DIR', basename(__DIR__));

if (!file_exists(BASE_PATH . DIRECTORY_SEPARATOR . DMS_DIR)) {
    user_error('DMS directory named incorrectly. Please install the DMS module into a folder named: ' . DMS_DIR);
}

CMSMenu::remove_menu_item('DMSDocumentAddController');

ShortcodeParser::get('default')->register(
    'dms_document_link',
    array('DMSShortcodeHandler', 'handle')
);

if ($config->get('DMSDocument_versions', 'enable_versions')) {
    //using the same db relations for the versioned documents, as for the actual documents
    $config->update('DMSDocument_versions', 'db', $config->get('DMSDocument', 'db'));
}
