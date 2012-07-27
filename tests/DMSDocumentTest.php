<?php
class DMSDocumentTest extends SapphireTest {

	static $fixture_file = "dms/tests/dmstest.yml";

	function tearDownOnce() {
		$d = DataObject::get("DMSDocument");
		foreach($d as $d1) {
			$d1->delete();
		}
		$t = DataObject::get("DMSTag");
		foreach($t as $t1) {
			$t1->delete();
		}
	}

	function testPageRelations() {
		$s1 = $this->objFromFixture('SiteTree','s1');
		$s2 = $this->objFromFixture('SiteTree','s2');
		$s3 = $this->objFromFixture('SiteTree','s3');
		$s4 = $this->objFromFixture('SiteTree','s4');
		$s5 = $this->objFromFixture('SiteTree','s5');
		$s6 = $this->objFromFixture('SiteTree','s6');

		$d1 = $this->objFromFixture('DMSDocument','d1');

		$pages = $d1->Pages();
		$pagesArray = $pages->toArray();
		$this->assertEquals($pagesArray[0], $s1, "Page 1 associated correctly");
		$this->assertEquals($pagesArray[1], $s2, "Page 2 associated correctly");
		$this->assertEquals($pagesArray[2], $s3, "Page 3 associated correctly");
		$this->assertEquals($pagesArray[3], $s4, "Page 4 associated correctly");
		$this->assertEquals($pagesArray[4], $s5, "Page 5 associated correctly");
		$this->assertEquals($pagesArray[5], $s6, "Page 6 associated correctly");
	}

	function testAddPageRelation() {
		$s1 = $this->objFromFixture('SiteTree','s1');
		$s2 = $this->objFromFixture('SiteTree','s2');
		$s3 = $this->objFromFixture('SiteTree','s3');

		$doc = new DMSDocument(DMS::getDMSInstance());
		$doc->Filename = "test file";
		$doc->Folder = "0";
		$doc->write();

		$doc->addPage($s1);
		$doc->addPage($s2);
		$doc->addPage($s3);

		$pages = $doc->Pages();
		$pagesArray = $pages->toArray();
		$this->assertEquals($pagesArray[0], $s1, "Page 1 associated correctly");
		$this->assertEquals($pagesArray[1], $s2, "Page 2 associated correctly");
		$this->assertEquals($pagesArray[2], $s3, "Page 3 associated correctly");

		$doc->removePage($s1);
		$pages = $doc->Pages();
		$pagesArray = $pages->toArray();    //page 1 is missing
		$this->assertEquals($pagesArray[0], $s2, "Page 2 still associated correctly");
		$this->assertEquals($pagesArray[1], $s3, "Page 3 still associated correctly");

		$documents = $s2->Documents();
		$documentsArray = $documents->toArray();
		$this->assertDOSContains(array(array('Filename'=>$doc->Filename)), $documentsArray, "Document associated with page");

		$doc->removeAllPages();
		$pages = $doc->Pages();
		$this->assertEquals($pages->Count(), 0, "All pages removed");

		$documents = $s2->Documents();
		$documentsArray = $documents->toArray();
		$this->assertNotContains($doc, $documentsArray, "Document no longer associated with page");
	}
}