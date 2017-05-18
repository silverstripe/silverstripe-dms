<?php

class DMSTaxonomyTypeExtensionTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected $requiredExtensions = array(
        'TaxonomyType' => array('DMSTaxonomyTypeExtension')
    );

    /**
     * Ensure that the configurable list of default records are created
     */
    public function testDefaultRecordsAreCreated()
    {
        Config::inst()->update('DMSTaxonomyTypeExtension', 'default_records', array('Food', 'Beverage', 'Books'));

        TaxonomyType::create()->requireDefaultRecords();

        $this->assertDOSContains(
            array(
                array('Name' => 'Food'),
                array('Name' => 'Beverage'),
                array('Name' => 'Books'),
            ),
            TaxonomyType::get()
        );
    }
}
