<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Director;
use Sunnysideup\DMS\Cms\DMSDocumentAddController;
use SilverStripe\Admin\CMSMenu;
use SilverStripe\View\Parsers\ShortcodeParser;
use Sunnysideup\DMS\DMS;
use Sunnysideup\DMS\DMSShortcodeHandler;
use Sunnysideup\DMS\Model\DMSDocument_versions;
use Sunnysideup\DMS\Model\DMSDocument;
use SilverStripe\Assets\Filesystem;

$config = Config::inst();

define('DMS_DIR', basename(__DIR__));

if (!file_exists(Director::baseFolder() . DIRECTORY_SEPARATOR . DMS_DIR)) {
    user_error('DMS directory named incorrectly. Please install the DMS module into a folder named: ' . DMS_DIR);
}

// Ensure compatibility with PHP 7.2 ("object" is a reserved word),
// with SilverStripe 3.6 (using Object) and SilverStripe 3.7 (using SS_Object)
// if (!class_exists('SS_Object')) class_alias('Object', 'SS_Object');

CMSMenu::remove_menu_item(DMSDocumentAddController::class);

ShortcodeParser::get('default')->register(
    $config->get(DMS::class, 'shortcode_handler_key'),
    array(DMSShortcodeHandler::class, 'handle')
);

if ($config->get(DMSDocument_versions::class, 'enable_versions')) {
    //using the same db relations for the versioned documents, as for the actual documents
    // Config::modify()->update(DMSDocument_versions::class, 'db', $config->get(DMSDocument::class, 'db'));
}

// add dmsassets folder to file system sync exclusion
if (strpos($config->get(DMS::class, 'folder_name'), 'assets/') === 0) {
    $folderName = substr($config->get(DMS::class, 'folder_name'), 7);
    // Config::modify()->update(Filesystem::class, 'sync_blacklisted_patterns', array("/^" . $folderName . "$/i",));
}
