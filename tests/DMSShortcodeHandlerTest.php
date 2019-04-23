<?php

use SilverStripe\Core\Config\Config;
use Sunnysideup\DMS\DMS;
use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\Core\Injector\Injector;
use Sunnysideup\DMS\Model\DMSDocument;
use Sunnysideup\DMS\DMSShortcodeHandler;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\Dev\SapphireTest;
/**
 * Tests DMS shortcode linking functionality.
 *
 * @package dms
 * @subpackage tests
 */
class DMSShortcodeHandlerTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

    public function testShortcodeOperation()
    {
        Config::modify()->update(DMS::class, 'folder_name', 'assets/_unit-test-123');

        $file = 'dms/tests/DMS-test-lorum-file.pdf';
        $document = DMS::inst()->storeDocument($file);

        $result = ShortcodeParser::get('default')->parse(sprintf(
            '<p><a href="[dms_document_link id=\'%d\']">Document</a></p>',
            $document->ID
        ));

        $value = Injector::inst()->create('HTMLValue', $result);
        $link = $value->query('//a')->item(0);

        $this->assertStringEndsWith(
            '/dmsdocument/' . $document->ID . '-dms-test-lorum-file-pdf',
            $link->getAttribute('href')
        );
        $this->assertEquals($document->getExtension(), $link->getAttribute('data-ext'));
        $this->assertEquals($document->getFileSizeFormatted(), $link->getAttribute('data-size'));

        DMSFilesystemTestHelper::delete('assets/_unit-test-123');
    }

    /**
     * When the document is valid, the correct arguments are provided and some content is given, the content should
     * be parsed and added into an anchor to the document
     */
    public function testShortcodeWithContentReturnsParsedContentInLink()
    {
        $document = $this->objFromFixture(DMSDocument::class, 'd1');
        $arguments = array('id' => $document->ID);
        $result = DMSShortcodeHandler::handle($arguments, 'Some content', ShortcodeParser::get('default'), '');

        $this->assertSame(
            '<a href="/dmsdocument/' . $document->ID . '-test-file-file-doesnt-exist-1">Some content</a>',
            $result
        );
    }

    /**
     * An error page link should be returned if the arguments are not valid, empty or the document is not available etc.
     *
     * This only applies when an error page with a 404 error code exists.
     */
    public function testReturnErrorPageWhenIdIsEmpty()
    {
        ErrorPage::create(array('URLSegment' => 'testing', 'ErrorCode' => '404'))->write();
        $result = DMSShortcodeHandler::handle(array(), '', ShortcodeParser::get('default'), '');
        $this->assertContains('testing', $result);
    }

    /**
     * When invalid or no data is available to return from the arguments and no error page exists to use for a link,
     * return a blank string
     */
    public function testReturnEmptyStringWhenNoErrorPageExistsAndIdIsEmpty()
    {
        $this->assertSame('', DMSShortcodeHandler::handle(array(), '', ShortcodeParser::get('default'), ''));
    }
}
