<?php

class DMSSiteTreeExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'dms/tests/dmstest.yml';

    protected $requiredExtensions = array(
        'SiteTree' => array('DMSSiteTreeExtension')
    );

    /**
     * Ensure that setting the configuration property "documents_enabled" to false for a page type will prevent the
     * CMS fields from being modified.
     *
     * Also ensures that a correctly named Document Sets GridField is added to the fields in the right place.
     *
     * Note: the (1) is the number of sets defined for this SiteTree in the fixture
     *
     * @dataProvider documentSetEnabledConfigProvider
     */
    public function testCanDisableDocumentSetsTab($configSetting, $assertionMethod)
    {
        Config::inst()->update('SiteTree', 'documents_enabled', $configSetting);
        $siteTree = $this->objFromFixture('SiteTree', 's2');
        $this->$assertionMethod($siteTree->getCMSFields()->fieldByName('Root.DocumentSets.DocumentSets'));
    }

    /**
     * @return array[]
     */
    public function documentSetEnabledConfigProvider()
    {
        return array(
            array(true, 'assertNotNull'),
            array(false, 'assertNull')
        );
    }

    /**
     * Tests for the Document Sets GridField.
     *
     * Note: the (1) is the number of sets defined for this SiteTree in the fixture
     */
    public function testDocumentSetsGridFieldIsCorrectlyConfigured()
    {
        Config::inst()->update('SiteTree', 'documents_enabled', true);
        $siteTree = $this->objFromFixture('SiteTree', 's2');
        $gridField = $siteTree->getCMSFields()->fieldByName('Root.DocumentSets.DocumentSets');

        $this->assertInstanceOf('GridField', $gridField);
        $this->assertTrue((bool) $gridField->hasClass('documentsets'));
    }

    /**
     * Ensure that a page title can be retrieved with the number of related documents it has (across all document sets).
     *
     * See fixtures for relationships that define this result.
     */
    public function testGetTitleWithNumberOfDocuments()
    {
        $siteTree = $this->objFromFixture('SiteTree', 's1');
        $this->assertSame('testPage1 has document sets (5)', $siteTree->getTitleWithNumberOfDocuments());
    }

    /**
     * Ensure that documents marked as "embargo until publish" are unmarked as such when a page containing them is
     * published
     */
    public function testOnBeforePublishUnEmbargoesDocumentsSetAsEmbargoedUntilPublish()
    {
        $siteTree = $this->objFromFixture('SiteTree', 's7');
        $siteTree->doPublish();

        // Fixture defines this page as having two documents via one set
        foreach (array('embargo-until-publish1', 'embargo-until-publish2') as $filename) {
            $this->assertFalse(
                (bool) $siteTree->getAllDocuments()
                    ->filter('Filename', 'embargo-until-publish1')
                    ->first()
                    ->EmbargoedUntilPublished
            );
        }
        $this->assertCount(0, $siteTree->getAllDocuments()->filter('EmbargoedUntilPublished', true));
    }

    /**
     * Ensure that document sets that are assigned to pages to not show up in "link existing" autocomplete
     * search results
     */
    public function testGetRelatedDocumentsForAutocompleter()
    {
        $page = $this->objFromFixture('SiteTree', 's1');
        $gridField = $page->getCMSFields()->fieldByName('Root.DocumentSets.DocumentSets');
        $this->assertInstanceOf('GridField', $gridField);

        $autocompleter = $gridField->getConfig()->getComponentByType('GridFieldAddExistingAutocompleter');
        $jsonResult = $autocompleter->doSearch(
            $gridField,
            new SS_HTTPRequest('GET', '/', array('gridfield_relationsearch' => 'Document Set'))
        );

        $this->assertContains('Document Set not linked', $jsonResult);
        $this->assertNotContains('Document Set 1', $jsonResult);
    }
}
