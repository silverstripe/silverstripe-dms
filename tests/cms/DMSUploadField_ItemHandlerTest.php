<?php

use Sunnysideup\DMS\Model\DMSDocument;
use Sunnysideup\DMS\Cms\DMSUploadField;
use Sunnysideup\DMS\Cms\DMSUploadField_ItemHandler;
use SilverStripe\Forms\Form;
use SilverStripe\Dev\SapphireTest;

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

        $this->document = $this->objFromFixture(DMSDocument::class, 'd1');
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

        $this->assertInstanceOf(Form::class, $result);
        $this->assertInstanceOf(DMSDocument::class, $result->getRecord());
        $this->assertSame($this->document->ID, $result->getRecord()->ID);
    }
}
