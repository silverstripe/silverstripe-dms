<?php

class DMSDocumentSetTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

    /**
     * Ensure that getDocuments is extensible
     */
    public function testGetDocumentsIsExtensible()
    {
        DMSDocumentSet::add_extension('StubRelatedDocumentExtension');

        $set = new DMSDocumentSet;
        $documents = $set->getDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame('Extended', $documents->first()->Filename);
    }

    /**
     * Test that the GridField for documents isn't shown until you've saved the set
     */
    public function testGridFieldShowsWhenSetIsSaved()
    {
        $set = DMSDocumentSet::create();

        // Not in database yet
        $fields = $set->getCMSFields();
        $this->assertNull($fields->fieldByName('Root.Main.Documents'));
        $gridFieldNotice = $fields->fieldByName('Root.Main.GridFieldNotice');
        $this->assertNotNull($gridFieldNotice);
        $this->assertContains('Managing documents will be available', $gridFieldNotice->getContent());

        // In the database
        $set->Title = 'Testing';
        $set->write();
        $fields = $set->getCMSFields();
        $this->assertNotNull($fields->fieldByName('Root.Main.Documents'));
        $this->assertNull($fields->fieldByName('Root.Main.GridFieldNotice'));
    }

    public function testRelations()
    {
        $s1 = $this->objFromFixture('SiteTree', 's1');
        $s2 = $this->objFromFixture('SiteTree', 's2');
        $s4 = $this->objFromFixture('SiteTree', 's4');

        $ds1 = $this->objFromFixture('DMSDocumentSet', 'ds1');
        $ds2 = $this->objFromFixture('DMSDocumentSet', 'ds2');
        $ds3 = $this->objFromFixture('DMSDocumentSet', 'ds3');

        $this->assertCount(0, $s4->getDocumentSets(), 'Page 4 has no document sets associated');
        $this->assertCount(2, $s1->getDocumentSets(), 'Page 1 has 2 document sets');
        $this->assertEquals(array($ds1->ID, $ds2->ID), $s1->getDocumentSets()->column('ID'));
    }

    /**
     * Test that various components exist in the GridField config. See {@link DMSDocumentSet::getCMSFields} for context.
     */
    public function testDocumentGridFieldConfig()
    {
        $set = $this->objFromFixture('DMSDocumentSet', 'ds1');
        $fields = $set->getCMSFields();
        $gridField = $fields->fieldByName('Root.Main.Documents');
        $this->assertTrue((bool) $gridField->hasClass('documents'));

        /** @var GridFieldConfig $config */
        $config = $gridField->getConfig();

        $this->assertNotNull($config->getComponentByType('DMSGridFieldDeleteAction'));
        $this->assertNotNull($addNew = $config->getComponentByType('DMSGridFieldAddNewButton'));
        $this->assertSame($set->ID, $addNew->getDocumentSetId());

        if (class_exists('GridFieldPaginatorWithShowAll')) {
            $this->assertNotNull($config->getComponentByType('GridFieldPaginatorWithShowAll'));
        } else {
            $paginator = $config->getComponentByType('GridFieldPaginator');
            $this->assertNotNull($paginator);
            $this->assertSame(15, $paginator->getItemsPerPage());
        }

        $sortableAssertion = class_exists('GridFieldSortableRows') ? 'assertNotNull' : 'assertNull';
        $this->$sortableAssertion($config->getComponentByType('GridFieldSortableRows'));
    }

    /**
     * Test that query fields can be added to the gridfield
     */
    public function testAddQueryFields()
    {

        /** @var DMSDocumentSet $set */
        $set = $this->objFromFixture('DMSDocumentSet', 'ds6');
        /** @var FieldList $fields */
        $fields = new FieldList(new TabSet('Root'));
        /** @var FieldList $fields */
        $set->addQueryFields($fields);
        $keyValuePairs = $fields->dataFieldByName('KeyValuePairs');
        $this->assertNotNull(
            $keyValuePairs,
            'addQueryFields() includes KeyValuePairs composite field'
        );
        $this->assertNotNull(
            $keyValuePairs->fieldByName('KeyValuePairs[Title]'),
            'addQueryFields() includes KeyValuePairs composite field'
        );
    }

    public function testAddQueryFieldsIsExtensible()
    {

        DMSDocumentSet::add_extension('StubDocumentSetMockExtension');

        $fields = new FieldList(new TabSet('Root'));
        $set = new DMSDocumentSet;
        $set->addQueryFields($fields);

        $this->assertNotNull(
            $fields->dataFieldByName('ExtendedField'),
            'addQueryFields() is extendible as it included the field from the extension'
        );
    }

    /**
     * Test that extra documents are added after write
     */
    public function testSaveLinkedDocuments()
    {
        /** @var DMSDocumentSet $set */
        $set = $this->objFromFixture('DMSDocumentSet', 'dsSaveLinkedDocuments');
        // Assert initially docs
        $this->assertEquals(1, $set->getDocuments()->count(), 'Set has 1 document');
        // Now apply the query and see if 2 extras were added with CreatedByID filter
        $set->KeyValuePairs = '{"Filename":"extradoc3"}';
        $set->saveLinkedDocuments();
        $this->assertEquals(2, $set->getDocuments()->count(), 'Set has 2 documents');
    }
}
