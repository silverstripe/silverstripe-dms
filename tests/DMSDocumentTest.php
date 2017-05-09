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
        $gridField = $this->getGridFieldFromDocument($document);
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

    /**
     * Ensure that the related documents list does not include the current document itself
     */
    public function testGetRelatedDocumentsForAutocompleter()
    {
        $document = $this->objFromFixture('DMSDocument', 'd1');
        $gridField = $this->getGridFieldFromDocument($document);

        $config = $gridField->getConfig();

        $autocompleter = $config->getComponentByType('GridFieldAddExistingAutocompleter');
        $autocompleter->setResultsFormat('$Filename');

        $jsonResult = $autocompleter->doSearch(
            $gridField,
            new SS_HTTPRequest('GET', '/', array('gridfield_relationsearch' => 'test'))
        );

        $this->assertNotContains('test-file-file-doesnt-exist-1', $jsonResult);
        $this->assertContains('test-file-file-doesnt-exist-2', $jsonResult);
    }

    /**
     * @return GridField
     */
    protected function getGridFieldFromDocument(DMSDocument $document)
    {
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
        return $gridField;
    }

    /*
     * Tests whether the permissions fields are added
     */
    public function testAddPermissionsFields()
    {
        $document = $this->objFromFixture('DMSDocument', 'd1');
        $fields = $document->getCMSFields();

        $this->assertNotNull($fields->fieldByName('CanViewType'));
        $this->assertNotNull($fields->fieldByName('ViewerGroups'));
    }

    /**
     * Test view permissions
     */
    public function testCanView()
    {
        /** @var DMSDocument $document */
        $document = $this->objFromFixture('DMSDocument', 'doc-logged-in-users');
        // Make sure user is logged out
        if ($member = Member::currentUser()) {
            $member->logOut();
        }

        // Logged out user test
        $this->assertFalse($document->canView());

        // Logged in user test
        $adminID = $this->logInWithPermission();
        $admin = Member::get()->byID($adminID);
        $this->assertTrue($document->canView($admin));
        /** @var Member $member */
        $admin->logout();

        // Check anyone
        $document = $this->objFromFixture('DMSDocument', 'doc-anyone');
        $this->assertTrue($document->canView());

        // Check OnlyTheseUsers
        $document = $this->objFromFixture('DMSDocument', 'doc-only-these-users');
        $reportAdminID = $this->logInWithPermission('cable-guy');
        /** @var Member $reportAdmin */
        $reportAdmin = Member::get()->byID($reportAdminID);
        $this->assertFalse($document->canView($reportAdmin));
        // Add reportAdmin to group
        $reportAdmin->addToGroupByCode('content-author');
        $this->assertTrue($document->canView($reportAdmin));
        $reportAdmin->logout();
    }

    /**
     * Tests edit permissions
     */
    public function testCanEdit()
    {
        // Make sure user is logged out
        if ($member = Member::currentUser()) {
            $member->logOut();
        }

        /** @var DMSDocument $document1 */
        $document1 = $this->objFromFixture('DMSDocument', 'doc-logged-in-users');

        // Logged out user test
        $this->assertFalse($document1->canEdit());

        //Logged in user test
        $contentAuthor = $this->objFromFixture('Member', 'editor');
        $this->assertTrue($document1->canEdit($contentAuthor));

        // Check OnlyTheseUsers
        /** @var DMSDocument $document2 */
        $document2 = $this->objFromFixture('DMSDocument', 'doc-only-these-users');
        /** @var Member $cableGuy */
        $cableGuy = $this->objFromFixture('Member', 'non-editor');
        $this->assertFalse($document2->canEdit($cableGuy));

        $cableGuy->addToGroupByCode('content-author');
        $this->assertTrue($document2->canEdit($cableGuy));
    }

    /**
     * Test permission denied reasons for documents
     */
    public function testGetPermissionDeniedReason()
    {
        /** @var DMSDocument $document1 */
        $doc1 = $this->objFromFixture('DMSDocument', 'doc-logged-in-users');
        $this->assertContains('Please log in to view this document', $doc1->getPermissionDeniedReason());

        /** @var DMSDocument $doc2 */
        $doc2 = $this->objFromFixture('DMSDocument', 'doc-only-these-users');
        $this->assertContains('You are not authorised to view this document', $doc2->getPermissionDeniedReason());

        /** @var DMSDocument $doc3 */
        $doc3 = $this->objFromFixture('DMSDocument', 'doc-anyone');
        $this->assertEquals('', $doc3->getPermissionDeniedReason());
    }

    /**
     * Ensure that all pages that a document belongs to (via many document sets) can be retrieved in one list
     */
    public function testGetRelatedPages()
    {
        $document = $this->objFromFixture('DMSDocument', 'd1');
        $result = $document->getRelatedPages();
        $this->assertCount(3, $result, 'Document 1 is related to 3 Pages');
        $this->assertSame(array('s1', 's2', 's3'), $result->column('URLSegment'));
    }
}
