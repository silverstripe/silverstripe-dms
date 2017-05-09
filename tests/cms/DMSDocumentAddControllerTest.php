<?php

class DMSDocumentAddControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'dms/tests/dmstest.yml';

    public function setUp()
    {
        parent::setUp();
        $this->logInWithPermission();
    }

    public function testCurrentPageReturnsSiteTree()
    {
        $controller = new DMSDocumentAddController;
        $this->assertInstanceOf('SiteTree', $controller->currentPage());
    }

    public function testGetCurrentDocumentSetReturnsDocumentSet()
    {
        $controller = new DMSDocumentAddController;
        $this->assertInstanceOf('DMSDocumentSet', $controller->getCurrentDocumentSet());
    }

    /**
     * Test that extra allowed extensions are merged into the default upload field allowed extensions
     */
    public function testGetAllowedExtensions()
    {
        $controller = new DMSDocumentAddController;
        Config::inst()->remove('File', 'allowed_extensions');
        Config::inst()->update('File', 'allowed_extensions', array('jpg', 'gif'));
        $this->assertSame(array('jpg', 'gif'), $controller->getAllowedExtensions());

        Config::inst()->update('DMSDocumentAddController', 'allowed_extensions', array('php', 'php5'));
        $this->assertSame(array('jpg', 'gif', 'php', 'php5'), $controller->getAllowedExtensions());
    }
}
