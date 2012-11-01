<?php
class DMSTagTest extends SapphireTest {

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

	function testAddingTags() {
		$doc = new DMSDocument();
		$doc->Filename = "test file";
		$doc->Folder = "0";
		$doc->write();

		$doc->addTag("fruit","banana");
		$doc->addTag("fruit","orange");
		$doc->addTag("fruit","apple");
		$doc->addTag("company","apple");
		$doc->addTag("company","SilverStripe");

		$fruits = $doc->getTagsList("fruit");
		$this->assertNotNull($fruits,"Something returned for fruit tags");
		$this->assertEquals(count($fruits),3,"3 fruit tags returned");
		$this->assertTrue(in_array("banana",$fruits),"correct fruit tags returned");

		//sneakily create another document and link one of the tags to that, too
		$doc2 = new DMSDocument();
		$doc2->Filename = "sneaky file";
		$doc2->Folder = "0";
		$doc2->write();
		$doc2->addTag("fruit","banana");

		$fruits = $doc2->getTagsList("fruit");
		$this->assertNotNull($fruits,"Something returned for fruit tags");
		$this->assertEquals(count($fruits),1,"Only 1 fruit tags returned");

		//tidy up by deleting all tags from doc 1 (But the banana fruit tag should remain)
		$doc->removeAllTags();

		//banana fruit remains
		$fruits = $doc2->getTagsList("fruit");
		$this->assertNotNull($fruits,"Something returned for fruit tags");
		$this->assertEquals(count($fruits),1,"Only 1 fruit tags returned");

		$tags = DataObject::get("DMSTag");
		$this->assertEquals($tags->Count(),1,"A single DMS tag objects remain after deletion of all tags on doc1");

		//delete all tags off doc2 to complete the tidy up
		$doc2->removeAllTags();

		$tags = DataObject::get("DMSTag");
		$this->assertEquals($tags->Count(),0,"No DMS tag objects remain after deletion");
	}

	function testRemovingTags() {
		$doc = new DMSDocument();
		$doc->Filename = "test file";
		$doc->Folder = "0";
		$doc->write();

		$doc->addTag("fruit","banana");
		$doc->addTag("fruit","orange");
		$doc->addTag("fruit","apple");
		$doc->addTag("company","apple");
		$doc->addTag("company","SilverStripe");

		$companies = $doc->getTagsList("company");
		$this->assertNotNull($companies,"Companies returned before deletion");
		$this->assertEquals(count($companies),2,"Two companies returned before deletion");

		//delete an entire category
		$doc->removeTag("company");

		$companies = $doc->getTagsList("company");
		$this->assertNull($companies,"All companies deleted");

		$fruit = $doc->getTagsList("fruit");
		$this->assertEquals(count($fruit),3,"Three fruits returned before deletion");

		//delete a single tag
		$doc->removeTag("fruit","apple");

		$fruit = $doc->getTagsList("fruit");
		$this->assertEquals(count($fruit),2,"Two fruits returned after deleting one");

		//delete a single tag
		$doc->removeTag("fruit","orange");

		$fruit = $doc->getTagsList("fruit");
		$this->assertEquals(count($fruit),1,"One fruits returned after deleting two");

		//nothing happens when deleting tag that doesn't exist
		$doc->removeTag("fruit","jellybean");

		$fruit = $doc->getTagsList("fruit");
		$this->assertEquals(count($fruit),1,"One fruits returned after attempting to delete non-existent fruit");

		//delete the last fruit
		$doc->removeTag("fruit","banana");

		$fruit = $doc->getTagsList("fruit");
		$this->assertNull($fruit,"All fruits deleted");

		$tags = DataObject::get("DMSTag");
		$this->assertEquals($tags->Count(),0,"No DMS tag objects remain after deletion");
	}

}