<?php

class DMSGridFieldAddNewButtonTest extends SapphireTest
{
    protected static $fixture_file = 'dms/tests/dmstest.yml';

    /**
     * @var DMSGridFieldAddNewButton
     */
    protected $button;

    /**
     * @var GridField
     */
    protected $gridField;

    public function setUp()
    {
        parent::setUp();

        $fakeList = DMSDocument::get();
        $this->gridField = GridField::create('TestGridField', false, $fakeList);
        $this->button = new DMSGridFieldAddNewButton;
    }

    /**
     * Test that when no document set ID is present then it is not added to the URL for "add document"
     */
    public function testNoDocumentSetIdInAddUrlWhenNotProvided()
    {
        $this->assertNotContains('?dsid', $this->getButtonHtml());
    }

    /**
     * Test that when a document set ID is provided, it is added onto the "add document" link
     */
    public function testDocumentSetIdAddedToLinkWhenProvided()
    {
        $this->button->setDocumentSetId(123);
        $this->assertContains('?dsid=123', $this->getButtonHtml());
    }

    /**
     * If a set is saved and associated to a page, that page's ID should be added to the "add document" link to help
     * to ensure the user gets redirected back to the correct place afterwards
     */
    public function testPageIdIsAddedWhenAvailableViaDocumentSetRelationship()
    {
        $set = $this->objFromFixture('DMSDocumentSet', 'ds1');
        $this->button->setDocumentSetId($set->ID);

        $controller = new ContentController;
        $controller->pushCurrent();

        $result = $this->getButtonHtml();
        $this->assertContains('dsid=' . $set->ID, $result, 'Add new button contains document set ID');
        $this->assertNotContains('page_id=' . $set->Page()->ID, $result, 'No page ID when not editing in page context');

        $controller = new CMSPageEditController;
        $controller->pushCurrent();

        $this->assertContains(
            'page_id=' . $set->Page()->ID,
            $this->getButtonHtml(),
            'Button contains page ID when in edit page context'
        );
    }

    /**
     * Returns the HTML result of the "add new" button, used for DRY in test class
     *
     * @return string
     */
    protected function getButtonHtml()
    {
        $fragments = $this->button->getHTMLFragments($this->gridField);
        return array_pop($fragments)->getValue();
    }
}
