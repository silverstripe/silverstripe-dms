<?php

class DMSDocumentAddControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'dms/tests/dmstest.yml';

    /**
     * @var DMSDocumentAddController
     */
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->logInWithPermission();
        $this->controller = new DMSDocumentAddController;
        $this->controller->init();
    }

    /**
     * Ensure that if no ID is provided then a SiteTree singleton is returned (which will not have an ID). If one is
     * provided then it should be loaded from the database via versioning.
     */
    public function testCurrentPageReturnsSiteTree()
    {
        $page = $this->objFromFixture('SiteTree', 's1');

        $this->assertInstanceOf('SiteTree', $this->controller->currentPage());
        $this->assertEmpty($this->controller->currentPage()->ID);
        $this->controller->setRequest(new SS_HTTPRequest('GET', '/', array('page_id' => $page->ID)));
        $this->assertEquals($page->ID, $this->controller->currentPage()->ID, 'Specified page is loaded and returned');
    }

    /**
     * Ensure that if no "dsid" is given a singleton is returned (which will not have an ID). If one is provided
     * it should be loaded from the database
     */
    public function testGetCurrentDocumentSetReturnsDocumentSet()
    {
        $set = $this->objFromFixture('DMSDocumentSet', 'ds1');

        $this->assertInstanceOf('DMSDocumentSet', $this->controller->getCurrentDocumentSet());
        $this->assertEmpty($this->controller->getCurrentDocumentSet()->ID, 'Singleton does not have an ID');
        $this->controller->setRequest(new SS_HTTPRequest('GET', '/', array('dsid' => $set->ID)));
        $this->assertEquals($set->ID, $this->controller->getCurrentDocumentSet()->ID, 'Specified document set is returned');
    }

    /**
     * Test that extra allowed extensions are merged into the default upload field allowed extensions
     */
    public function testGetAllowedExtensions()
    {
        Config::inst()->remove('File', 'allowed_extensions');
        Config::inst()->update('File', 'allowed_extensions', array('jpg', 'gif'));
        $this->assertSame(array('jpg', 'gif'), $this->controller->getAllowedExtensions());

        Config::inst()->update('DMSDocumentAddController', 'allowed_extensions', array('php', 'php5'));
        $this->assertSame(array('jpg', 'gif', 'php', 'php5'), $this->controller->getAllowedExtensions());
    }

    /**
     * Test that the back link will be the document set that a file is uploaded into if relevant, otherwise the model
     * admin that it was uploaded from
     */
    public function testBacklink()
    {
        // No page ID and no document set ID
        $this->assertContains('admin/documents', $this->controller->Backlink());

        // No page ID, has document set ID
        $request = new SS_HTTPRequest('GET', '/', array('dsid' => 123));
        $this->controller->setRequest($request);
        $this->assertContains('EditForm', $this->controller->Backlink());
        $this->assertContains('123', $this->controller->Backlink());

        // Has page ID and document set ID
        $request = new SS_HTTPRequest('GET', '/', array('dsid' => 123, 'page_id' => 234));
        $this->controller->setRequest($request);
        $this->assertContains('admin/pages', $this->controller->Backlink());
        $this->assertContains('123', $this->controller->Backlink());
    }

    /**
     * Test that the document autocomplete endpoint returns JSON, matching on ID, title or filename (case insensitive)
     */
    public function testDocumentAutocomplete()
    {
        $result = (string) $this->get('admin/pages/adddocument/documentautocomplete?term=EXIST')->getBody();
        $this->assertJson($result, 'Autocompleter should return JSON');
        $this->assertContains("File That Doesn't Exist (Title)", $result);
        $this->assertContains('test-file-file-doesnt-exist-1', $result);
        $this->assertNotContains('doc-logged-in-users', $result);

        $document = $this->objFromFixture('DMSDocument', 'd2');
        $result = (string) $this->get('admin/pages/adddocument/documentautocomplete?term=' . $document->ID)->getBody();
        $this->assertContains($document->ID . " - File That Doesn't Exist (Title)", $result);
    }
}
