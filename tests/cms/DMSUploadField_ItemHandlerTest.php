<?php

class DMSUploadField_ItemHandlerTest extends SapphireTest
{
    protected static $fixture_file = 'dms/tests/dmstest.yml';

    /**
     * @var DMSDocument
     */
    protected $document;

    public function setUp()
    {
        parent::setUp();

        $this->document = $this->objFromFixture('DMSDocument', 'd1');
    }

    public function testGetItem()
    {
        $handler = new DMSUploadField_ItemHandler(DMSUploadField::create('Test'), $this->document->ID);
        $result = $handler->getItem();
        $this->assertSame($result->ID, $this->document->ID, 'getItem returns the correct document from the database');
    }

    public function testEditForm()
    {
        $handler = new DMSUploadField_ItemHandler(DMSUploadField::create('Test'), $this->document->ID);
        $result = $handler->EditForm();

        $this->assertInstanceOf('Form', $result);
        $this->assertInstanceOf('DMSDocument', $result->getRecord());
        $this->assertSame($this->document->ID, $result->getRecord()->ID);
    }
}
