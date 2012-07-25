<?php
class DMSTagTest extends SapphireTest {

	//static $fixture_file = "dms/tests/dmstest.yml";

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

		$fruits = $doc->getTags("fruit");
		$this->assertNotNull($fruits,"Something returned for fruit tags");
		$this->assertEquals(count($fruits),3,"3 fruit tags returned");
		$this->assertArrayHasKey("banana",$fruits,"correct fruit tags returned");
	}
}