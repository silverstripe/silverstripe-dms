<?php
class DMSDocumentTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

    public function tearDownOnce()
    {
        self::$is_running_test = true;

        $d = DataObject::get('DMSDocument');
        foreach ($d as $d1) {
            $d1->delete();
        }
        $t = DataObject::get('DMSTag');
        foreach ($t as $t1) {
            $t1->delete();
        }

        self::$is_running_test = $this->originalIsRunningTest;
    }

    public function testPageRelations()
    {
        $s1 = $this->objFromFixture('SiteTree', 's1');
        $s2 = $this->objFromFixture('SiteTree', 's2');
        $s3 = $this->objFromFixture('SiteTree', 's3');
        $s4 = $this->objFromFixture('SiteTree', 's4');
        $s5 = $this->objFromFixture('SiteTree', 's5');
        $s6 = $this->objFromFixture('SiteTree', 's6');

        $d1 = $this->objFromFixture('DMSDocument', 'd1');

        $pages = $d1->Pages();
        $pagesArray = $pages->toArray();
        $this->assertEquals($pagesArray[0]->ID, $s1->ID, 'Page 1 associated correctly');
        $this->assertEquals($pagesArray[1]->ID, $s2->ID, 'Page 2 associated correctly');
        $this->assertEquals($pagesArray[2]->ID, $s3->ID, 'Page 3 associated correctly');
        $this->assertEquals($pagesArray[3]->ID, $s4->ID, 'Page 4 associated correctly');
        $this->assertEquals($pagesArray[4]->ID, $s5->ID, 'Page 5 associated correctly');
        $this->assertEquals($pagesArray[5]->ID, $s6->ID, 'Page 6 associated correctly');
    }

    public function testAddPageRelation()
    {
        $s1 = $this->objFromFixture('SiteTree', 's1');
        $s2 = $this->objFromFixture('SiteTree', 's2');
        $s3 = $this->objFromFixture('SiteTree', 's3');

        $doc = new DMSDocument();
        $doc->Filename = 'test file';
        $doc->Folder = '0';
        $doc->write();

        $doc->addPage($s1);
        $doc->addPage($s2);
        $doc->addPage($s3);

        $pages = $doc->Pages();
        $pagesArray = $pages->toArray();
        $this->assertEquals($pagesArray[0]->ID, $s1->ID, 'Page 1 associated correctly');
        $this->assertEquals($pagesArray[1]->ID, $s2->ID, 'Page 2 associated correctly');
        $this->assertEquals($pagesArray[2]->ID, $s3->ID, 'Page 3 associated correctly');

        $doc->removePage($s1);
        $pages = $doc->Pages();
        $pagesArray = $pages->toArray();    // Page 1 is missing
        $this->assertEquals($pagesArray[0]->ID, $s2->ID, 'Page 2 still associated correctly');
        $this->assertEquals($pagesArray[1]->ID, $s3->ID, 'Page 3 still associated correctly');

        $documents = $s2->Documents();
        $documentsArray = $documents->toArray();
        $this->assertDOSContains(
            array(
                array('Filename' => $doc->Filename)
            ),
            $documentsArray,
            'Document associated with page'
        );

        $doc->removeAllPages();
        $pages = $doc->Pages();
        $this->assertEquals($pages->Count(), 0, 'All pages removed');

        $documents = $s2->Documents();
        $documentsArray = $documents->toArray();
        $this->assertNotContains($doc, $documentsArray, 'Document no longer associated with page');
    }

    public function testDeletingPageWithAssociatedDocuments()
    {
        $s1 = $this->objFromFixture('SiteTree', 's1');
        $s2 = $this->objFromFixture('SiteTree', 's2');
        $s2->publish('Stage', 'Live');
        $s2ID = $s2->ID;

        $doc = new DMSDocument();
        $doc->Filename = 'delete test file';
        $doc->Folder = '0';
        $doc->write();

        $doc->addPage($s1);
        $doc->addPage($s2);

        $s1->delete();

        $documents = DataObject::get("DMSDocument", "\"Filename\" = 'delete test file'", false);
        $this->assertEquals(
            $documents->Count(),
            '1',
            "Deleting one of the associated page doesn't affect the single document we created"
        );

        $s2->delete();
        $documents = DataObject::get("DMSDocument", "\"Filename\" = 'delete test file'");
        $this->assertEquals(
            $documents->Count(),
            '1',
            "Deleting a page from draft stage doesn't delete the associated docs,"
            . "even if it's the last page they're associated with"
        );

        $s2 = Versioned::get_one_by_stage('SiteTree', 'Live', sprintf('"SiteTree"."ID" = %d', $s2ID));
        $s2->doDeleteFromLive();
        $documents = DataObject::get("DMSDocument", "\"Filename\" = 'delete test file'");
        $this->assertEquals(
            $documents->Count(),
            '0',
            'However, deleting the live version of the last page that a document is '
            . 'associated with causes that document to be deleted as well'
        );
    }

    public function testUnpublishPageWithAssociatedDocuments()
    {
        $s2 = $this->objFromFixture('SiteTree', 's2');
        $s2->publish('Stage', 'Live');
        $s2ID = $s2->ID;

        $doc = new DMSDocument();
        $doc->Filename = 'delete test file';
        $doc->Folder = '0';
        $doc->write();

        $doc->addPage($s2);

        $s2->doDeleteFromLive();
        $documents = DataObject::get("DMSDocument", "\"Filename\" = 'delete test file'");
        $this->assertEquals(
            $documents->Count(),
            '1',
            "Deleting a page from live stage doesn't delete the associated docs,"
            . "even if it's the last page they're associated with"
        );

        $s2 = Versioned::get_one_by_stage('SiteTree', 'Stage', sprintf('"SiteTree"."ID" = %d', $s2ID));
        $s2->delete();
        $documents = DataObject::get("DMSDocument", "\"Filename\" = 'delete test file'");
        $this->assertEquals(
            $documents->Count(),
            '0',
            'However, deleting the draft version of the last page that a document is '
            . 'associated with causes that document to be deleted as well'
        );
    }

    public function testDefaultDownloadBehabiourCMSFields()
    {
        $document = singleton('DMSDocument');
        Config::inst()->update('DMSDocument', 'default_download_behaviour', 'open');
        $cmsFields = $document->getCMSFields();
        $this->assertEquals('open', $cmsFields->dataFieldByName('DownloadBehavior')->Value());


        Config::inst()->update('DMSDocument', 'default_download_behaviour', 'download');
        $cmsFields = $document->getCMSFields();
        $this->assertEquals('download', $cmsFields->dataFieldByName('DownloadBehavior')->Value());
    }

    /**
     * Ensure that related documents can be retrieved for a given DMS document
     */
    public function testRelatedDocuments()
    {
        $document = $this->objFromFixture('DMSDocument', 'document_with_relations');
        $this->assertGreaterThan(0, $document->RelatedDocuments()->count());
        $this->assertEquals(
            array('test-file-file-doesnt-exist-1', 'test-file-file-doesnt-exist-2'),
            $document->getRelatedDocuments()->column('Filename')
        );
    }

    /**
     * Test the extensibility of getRelatedDocuments
     */
    public function testGetRelatedDocumentsIsExtensible()
    {
        DMSDocument::add_extension('StubRelatedDocumentExtension');

        $emptyDocument = new DMSDocument;
        $relatedDocuments = $emptyDocument->getRelatedDocuments();

        $this->assertCount(1, $relatedDocuments);
        $this->assertSame('Extended', $relatedDocuments->first()->Filename);
    }

    /**
     * Ensure that the DMS Document CMS actions contains a grid field for managing related documents
     */
    public function testDocumentHasCmsFieldForManagingRelatedDocuments()
    {
        $document = $this->objFromFixture('DMSDocument', 'document_with_relations');

        $documentFields = $document->getCMSFields();
        /** @var FieldGroup $actions */
        $actions = $documentFields->fieldByName('ActionsPanel');

        $gridField = null;
        foreach ($actions->getChildren() as $child) {
            /** @var FieldGroup $child */
            if ($gridField = $child->fieldByName('RelatedDocuments')) {
                break;
            }
        }
        $this->assertInstanceOf('GridField', $gridField);

        /** @var GridFieldConfig $gridFieldConfig */
        $gridFieldConfig = $gridField->getConfig();

        $this->assertNotNull(
            'GridFieldAddExistingAutocompleter',
            $addExisting = $gridFieldConfig->getComponentByType('GridFieldAddExistingAutocompleter'),
            'Related documents GridField has an "add existing" autocompleter'
        );

        $this->assertNull(
            $gridFieldConfig->getComponentByType('GridFieldAddNewButton'),
            'Related documents GridField does not have an "add new" button'
        );
    }
}
