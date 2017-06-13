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
     * Check that the default "add new" and "edit" buttons are gone, and replaced with our customised version of it
     */
    public function testGridFieldHasCustomisedButtons()
    {
        $modelAdmin = new DMSDocumentAdmin;
        $modelAdmin->init();

        $form = $modelAdmin->getEditForm();
        $gridFieldConfig = $form->Fields()->first()->getConfig();

        $replacements = array(
            'GridFieldAddNewButton'=>'DMSGridFieldAddNewButton',
            'GridFieldEditButton'=>'DMSGridFieldEditButton'
        );

        foreach ($replacements as $oldClass => $newClass) {
            // Our button is an instance of the original, so is returned when asking for the original
            $newButtons = $gridFieldConfig->getComponentsByType($oldClass);
            foreach ($newButtons as $key => $newButton) {
                if ($newButton instanceof $newClass) {
                    // Remove our version for testing's sake
                    $newButtons->remove($newButton);
                }
            }

            $this->assertCount(0, $newButtons, 'Original button is removed');
            $this->assertInstanceOf(
                $newClass,
                $gridFieldConfig->getComponentByType($newClass),
                "Model admin for documents contains customised {$newClass} button"
            );
        }
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

    /**
     * Checks that the document sets GridField has a data column which links to the DocumentSets tab on
     * the actual page in the CMS
     */
    public function testDocumentSetsGridFieldHasLinkToCMSPageEditor()
    {
        $result = (string)$this->get('admin/documents/DMSDocumentSet')->getBody();
        $this->assertContains(
            "<a class='dms-doc-sets-link'",
            $result
        );
    }
}
