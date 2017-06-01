<?php

class DMSDocumentAdminTest extends FunctionalTest
{
    protected static $fixture_file = 'DMSDocumentAdminTest.yml';

    public function setUp()
    {
        parent::setUp();

        $this->logInWithPermission('ADMIN');
    }

    /**
     * Check that the default "add new" button is gone, and replaced with our customised version of it
     */
    public function testGridFieldHasCustomisedAddNewButton()
    {
        $modelAdmin = new DMSDocumentAdmin;
        $modelAdmin->init();

        $form = $modelAdmin->getEditForm();
        $gridFieldConfig = $form->Fields()->first()->getConfig();

        // Our button is an instance of the original, so is returned when asking for the original
        $addNewButtons = $gridFieldConfig->getComponentsByType('GridFieldAddNewButton');
        foreach ($addNewButtons as $key => $addNewButton) {
            if ($addNewButton instanceof DMSGridFieldAddNewButton) {
                // Remove our version for testing's sake
                $addNewButtons->remove($addNewButton);
            }
        }

        $this->assertCount(0, $addNewButtons, 'Original add new button is removed');
        $this->assertInstanceOf(
            'DMSGridFieldAddNewButton',
            $gridFieldConfig->getComponentByType('DMSGridFieldAddNewButton'),
            'Model admin for documents contains customised DMS add new button'
        );
    }

    /**
     * Quick check to ensure that the ModelAdmin endpoint is working
     */
    public function testModelAdminEndpointWorks()
    {
        $this->assertEquals(200, $this->get('admin/documents')->getStatusCode());
    }

    /**
     * Check that the document sets GridField has a data column for the parent page title. Here we check for the
     * Page title existing in the DOM, since "Page" is guaranteed to exist somewhere else.
     */
    public function testDocumentSetsGridFieldHasParentPageColumn()
    {
        $result = (string) $this->get('admin/documents/DMSDocumentSet')->getBody();
        $this->assertContains('Home Test Page', $result);
        $this->assertContains('About Us Test Page', $result);
    }
}
