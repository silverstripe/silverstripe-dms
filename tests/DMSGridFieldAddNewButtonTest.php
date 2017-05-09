<?php

class DMSGridFieldAddNewButtonTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

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
    public function testNoPageIdInAddUrlWhenNotProvided()
    {
        $fragments = $this->button->getHTMLFragments($this->gridField);
        $result = array_pop($fragments)->getValue();
        $this->assertNotContains('?dsid', $result);
    }

    /**
     * Test that when a document set ID is provided, it is added onto the "add document" link
     */
    public function testPageIdAddedToLinkWhenProvided()
    {
        $this->button->setDocumentSetId(123);

        $fragments = $this->button->getHTMLFragments($this->gridField);
        $result = array_pop($fragments)->getValue();
        $this->assertContains('?dsid=123', $result);
    }
}
