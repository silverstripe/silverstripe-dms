<?php

class DMSUploadFieldTest extends SapphireTest
{
    /**
     * @var DMSUploadField
     */
    protected $field;

    public function setUp()
    {
        parent::setUp();

        $this->field = new DMSUploadField('StubUploadField');
    }

    /**
     * SS 3.x injector will return an overloaded parent of a child class if the child is not injected.
     * This is a sanity check.
     */
    public function testDmsUploadFieldIsInjectable()
    {
        $this->assertInstanceOf('DMSUploadField', DMSUploadField::create('Stub'));
    }

    /**
     * The validator is coded to always return true. Replace this test if this behaviour changes in future.
     */
    public function testValidatorAlwaysReturnsTrue()
    {
        $this->assertTrue($this->field->validate('foo'));
    }

    public function testGetItemHandler()
    {
        $this->assertInstanceOf('DMSUploadField_ItemHandler', $this->field->getItemHandler(123));
    }

    /**
     * Ensure that the folder name can be get/set and that the value set is casted to a string
     */
    public function testCanGetAndSetFolderName()
    {
        $this->field->setFolderName('qwerty');
        $this->assertSame('qwerty', $this->field->getFolderName());
        $this->field->setFolderName(123);
        $this->assertSame('123', $this->field->getFolderName());
    }
}
