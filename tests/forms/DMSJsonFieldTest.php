<?php

class DMSJsonFieldTest extends SapphireTest
{
    public function testJsonFieldConstructorMultiWays()
    {
        $jsonField = new DMSJsonField('MyJsonField', new FieldList(
            new TextField('FirstName', 'Given name'),
            new TextField('Surname', 'Last name')
        ));
        $this->assertEquals($jsonField->FieldList()->count(), 2);
        $this->assertNotNull($jsonField->FieldList()->dataFieldByName('MyJsonField[FirstName]'));

        $jsonField = new DMSJsonField('MyJsonField', array(new TextField('FirstName', 'Given name'),
            new TextField('Surname', 'Last name')));
        $this->assertEquals($jsonField->FieldList()->count(), 2);
        $this->assertNotNull($jsonField->FieldList()->dataFieldByName('MyJsonField[FirstName]'));

        $jsonField = new DMSJsonField(
            'MyJsonField',
            new TextField('FirstName', 'Given name'),
            new TextField('Surname', 'Last name')
        );
        $this->assertEquals($jsonField->FieldList()->count(), 2);
        $this->assertNotNull($jsonField->FieldList()->dataFieldByName('MyJsonField[FirstName]'));
    }

    public function testJsonFieldDataValueCouldDealWithArray()
    {
        $jsonField = new DMSJsonField('MyJsonField', new FieldList(
            new TextField('FirstName', 'Given name'),
            new TextField('Surname', 'Last name')
        ));
        $jsonField->setValue($value = array(
            'MyJsonField'=>array(
                'FirstName' => 'Normann',
                'Surname' => 'Lou',
            ),
        ));

        $this->assertEquals($jsonField->dataValue(), Convert::array2json($value));
        $jsonField->setValue($value = array(
            'MyJsonField'=>array(),
        ));
        $this->assertNull($jsonField->dataValue());
    }
}
