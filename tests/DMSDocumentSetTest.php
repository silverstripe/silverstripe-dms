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
}
