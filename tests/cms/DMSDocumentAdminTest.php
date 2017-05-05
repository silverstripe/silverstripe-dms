<?php

class DMSDocumentAdminTest extends FunctionalTest
{
    /**
     * Check that the default "add new" button is gone, and replaced with our customised version of it
     */
    public function testGridFieldHasCustomisedAddNewButton()
    {
        $modelAdmin = new DMSDocumentAdmin;
        // SS < 3.3 doesn't have a response setter, this initialises it
        $modelAdmin->handleRequest(new SS_HTTPRequest('GET', '/'), DataModel::inst());
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
}
