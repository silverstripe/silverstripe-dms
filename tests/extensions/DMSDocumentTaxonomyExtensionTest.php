<?php

class DMSDocumentTaxonomyExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'DMSDocumentTaxonomyExtensionTest.yml';

    public function setUp()
    {
        parent::setUp();

        if (!class_exists('TaxonomyType')) {
            $this->markTestSkipped('This test requires silverstripe/taxonomy ^1.2 to be installed. Skipping.');
        }
    }

    /**
     * Ensure that appropriate tags by taxonomy type are returned, and that their hierarchy is displayd in the title
     */
    public function testGetAllTagsMap()
    {
        $extension = new DMSDocumentTaxonomyExtension;
        $result = $extension->getAllTagsMap();

        $this->assertContains('Subject > Mathematics', $result);
        $this->assertContains('Subject', $result);
        $this->assertContains('Subject > Science > Chemistry', $result);
        $this->assertNotContains('Physical Education', $result);
    }
}
