<?php
/**
 * Tests DMS shortcode linking functionality.
 *
 * @package dms
 * @subpackage tests
 */
class DMSShortcodeTest extends SapphireTest
{
    public function testShortcodeOperation()
    {
        Config::inst()->update('DMS', 'folder_name', 'assets/_unit-test-123');

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
}
