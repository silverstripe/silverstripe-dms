<?php
class DMSDocumentTest extends SapphireTest {

	static $fixture_file = "dms/tests/dmstest.yml";

	function tearDownOnce() {
		self::$is_running_test = true;
		
		$d = DataObject::get("DMSDocument");
		foreach($d as $d1) {
			$d1->delete();
		}
		$t = DataObject::get("DMSTag");
		foreach($t as $t1) {
			$t1->delete();
		}

		self::$is_running_test = $this->originalIsRunningTest;
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
		$this->assertEquals($pagesArray[0]->ID, $s1->ID, "Page 1 associated correctly");
		$this->assertEquals($pagesArray[1]->ID, $s2->ID, "Page 2 associated correctly");
		$this->assertEquals($pagesArray[2]->ID, $s3->ID, "Page 3 associated correctly");
		$this->assertEquals($pagesArray[3]->ID, $s4->ID, "Page 4 associated correctly");
		$this->assertEquals($pagesArray[4]->ID, $s5->ID, "Page 5 associated correctly");
		$this->assertEquals($pagesArray[5]->ID, $s6->ID, "Page 6 associated correctly");
	}

	function testAddPageRelation() {
		$s1 = $this->objFromFixture('SiteTree','s1');
		$s2 = $this->objFromFixture('SiteTree','s2');
		$s3 = $this->objFromFixture('SiteTree','s3');

		$doc = new DMSDocument();
		$doc->Filename = "test file";
		$doc->Folder = "0";
		$doc->write();

		$doc->addPage($s1);
		$doc->addPage($s2);
		$doc->addPage($s3);

		$pages = $doc->Pages();
		$pagesArray = $pages->toArray();
		$this->assertEquals($pagesArray[0]->ID, $s1->ID, "Page 1 associated correctly");
		$this->assertEquals($pagesArray[1]->ID, $s2->ID, "Page 2 associated correctly");
		$this->assertEquals($pagesArray[2]->ID, $s3->ID, "Page 3 associated correctly");

		$doc->removePage($s1);
		$pages = $doc->Pages();
		$pagesArray = $pages->toArray();    //page 1 is missing
		$this->assertEquals($pagesArray[0]->ID, $s2->ID, "Page 2 still associated correctly");
		$this->assertEquals($pagesArray[1]->ID, $s3->ID, "Page 3 still associated correctly");

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

	function testDeletingPageWithAssociatedDocuments() {
		$s1 = $this->objFromFixture('SiteTree','s1');
		$s2 = $this->objFromFixture('SiteTree','s2');
		$s2->publish('Stage', 'Live');
		$s2ID = $s2->ID;

		$doc = new DMSDocument();
		$doc->Filename = "delete test file";
		$doc->Folder = "0";
		$doc->write();

		$doc->addPage($s1);
		$doc->addPage($s2);

		$s1->delete();

		$documents = DataObject::get("DMSDocument","\"Filename\" = 'delete test file'", false);
		$this->assertEquals(
			$documents->Count(),
			'1',
			"Deleting one of the associated page doesn't affect the single document we created"
		);

		$s2->delete();
		$documents = DataObject::get("DMSDocument","\"Filename\" = 'delete test file'");
		$this->assertEquals(
			$documents->Count(),
			'1',
			"Deleting a page from draft stage doesn't delete the associated docs,"
			. "even if it's the last page they're associated with"
		);

		$s2 = Versioned::get_one_by_stage('SiteTree', 'Live', sprintf('"SiteTree"."ID" = %d', $s2ID));
		$s2->doDeleteFromLive();
		$documents = DataObject::get("DMSDocument","\"Filename\" = 'delete test file'");
		$this->assertEquals(
			$documents->Count(),
			'0',
			"However, deleting the live version of the last page that a document is "
			 ."associated with causes that document to be deleted as well"
		);
	}

	function testUnpublishPageWithAssociatedDocuments() {
		$s2 = $this->objFromFixture('SiteTree','s2');
		$s2->publish('Stage', 'Live');
		$s2ID = $s2->ID;

		$doc = new DMSDocument();
		$doc->Filename = "delete test file";
		$doc->Folder = "0";
		$doc->write();

		$doc->addPage($s2);

		$s2->doDeleteFromLive();
		$documents = DataObject::get("DMSDocument","\"Filename\" = 'delete test file'");
		$this->assertEquals(
			$documents->Count(),
			'1',
			"Deleting a page from live stage doesn't delete the associated docs,"
			. "even if it's the last page they're associated with"
		);

		$s2 = Versioned::get_one_by_stage('SiteTree', 'Stage', sprintf('"SiteTree"."ID" = %d', $s2ID));
		$s2->delete();
		$documents = DataObject::get("DMSDocument","\"Filename\" = 'delete test file'");
		$this->assertEquals(
			$documents->Count(),
			'0',
			"However, deleting the draft version of the last page that a document is "
			 ."associated with causes that document to be deleted as well"
		);
	}

}