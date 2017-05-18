<?php
class DMSDocumentTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

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
        $this->assertEquals(array('Title', 'Filename'), $autocompleter->getSearchFields());
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

    /**
     * Ensure that HTML is returned containing list items with action panel steps
     */
    public function testGetActionTaskHtml()
    {
        $document = $this->objFromFixture('DMSDocument', 'd1');
        $document->addActionPanelTask('example', 'Example');

        $result = $document->getActionTaskHtml();

        $this->assertContains('<label class="left">Actions</label>', $result);
        $this->assertContains('<li class="ss-ui-button" data-panel="', $result);
        $this->assertContains('permission', $result);
        $this->assertContains('Example', $result);
    }

    /*
     * Tests whether the permissions fields are added
     */
    public function testGetPermissionsActionPanel()
    {
        $result = $this->objFromFixture('DMSDocument', 'd1')->getPermissionsActionPanel();

        $this->assertInstanceOf('CompositeField', $result);
        $this->assertNotNull($result->getChildren()->fieldByName('CanViewType'));
        $this->assertNotNull($result->getChildren()->fieldByName('ViewerGroups'));
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

    /**
     * Test that the title is returned if it is set, otherwise the filename without ID
     */
    public function testGetTitleOrFilenameWithoutId()
    {
        $d1 = $this->objFromFixture('DMSDocument', 'd1');
        $this->assertSame('test-file-file-doesnt-exist-1', $d1->getTitle());

        $d2 = $this->objFromFixture('DMSDocument', 'd2');
        $this->assertSame('File That Doesn\'t Exist (Title)', $d2->getTitle());
    }

    /**
     * Ensure that the folder a document's file is stored in can be retrieved, and that delete() will also delete
     * the file and the record
     */
    public function testGetStorageFolderThenDelete()
    {
        Config::inst()->update('DMS', 'folder_name', 'assets/_unit-tests');

        $document = DMS::inst()->storeDocument('dms/tests/DMS-test-lorum-file.pdf');
        $filename = $document->getStorageFolder() . '/' . $document->getFileName();

        $this->assertTrue(file_exists($filename));
        $document->delete();
        $this->assertFalse($document->exists());
        $this->assertFalse(file_exists($filename));

        DMSFilesystemTestHelper::delete('assets/_unit-tests');
    }

    /**
     * Ensure that the description can be returned in HTML format
     */
    public function testGetDescriptionWithLineBreak()
    {
        $document = DMSDocument::create();
        $document->Description = "Line 1\nLine 2\nLine 3";
        $document->write();

        $this->assertSame("Line 1<br />\nLine 2<br />\nLine 3", $document->getDescriptionWithLineBreak());
    }
}
