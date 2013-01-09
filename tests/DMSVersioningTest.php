<?php
class DMSVersioningTest extends SapphireTest {

	static $testFile = 'dms/tests/DMS-test-lorum-file.pdf';
	static $testFile2 = 'dms/tests/DMS-test-document-2.pdf';

	//store values to reset back to after this test runs
	static $dmsFolderOld;
	static $dmsFolderSizeOld;
	static $dmsEnableVersionsOld;

	function setUp() {
		parent::setUp();

		self::$dmsFolderOld = DMS::$dmsFolder;
		self::$dmsFolderSizeOld = DMS::$dmsFolderSize;
		self::$dmsEnableVersionsOld = DMSDocument_versions::$enable_versions;
		DMSDocument_versions::$enable_versions = true;

		//use a test DMS folder, so we don't overwrite the live one
		DMS::$dmsFolder = 'dms-assets-test-versions';

		//clear out the test folder (in case a broken test doesn't delete it)
		$this->delete(BASE_PATH . DIRECTORY_SEPARATOR . 'dms-assets-test-versions');
	}

	function tearDown() {
		$d = DataObject::get("DMSDocument");
		foreach($d as $d1) {
			$d1->delete();
		}
		$t = DataObject::get("DMSTag");
		foreach($t as $t1) {
			$t1->delete();
		}

		//delete the test folder after the test runs
		$this->delete(BASE_PATH . DIRECTORY_SEPARATOR . 'dms-assets-test-versions');

		parent::tearDown();

		//set the old DMS folder back again
		DMS::$dmsFolder = self::$dmsFolderOld;
		DMS::$dmsFolderSize = self::$dmsFolderSizeOld;
		DMSDocument_versions::$enable_versions = self::$dmsEnableVersionsOld;
	}

	public function delete($path) {
		if (file_exists($path) || is_dir($path)) {
		    $it = new RecursiveIteratorIterator(
		        new RecursiveDirectoryIterator($path),
		        RecursiveIteratorIterator::CHILD_FIRST
		    );
		    foreach ($it as $file) {
		        if (in_array($file->getBasename(), array('.', '..'))) {
		            continue;
		        } elseif ($file->isDir()) {
		            rmdir($file->getPathname());
		        } elseif ($file->isFile() || $file->isLink()) {
		            unlink($file->getPathname());
		        }
		    }
		    rmdir($path);
		}
	}


	function testDMSVersionStorage() {
		$dms = DMS::inst();

		$document = $dms->storeDocument(self::$testFile);

		$this->assertNotNull($document, "Document object created");
		$this->assertTrue(file_exists(DMS::get_dms_path() . DIRECTORY_SEPARATOR . $document->Folder . DIRECTORY_SEPARATOR . $document->Filename),"Document file copied into DMS folder");

		$document->replaceDocument(self::$testFile2);
		$document->replaceDocument(self::$testFile);
		$document->replaceDocument(self::$testFile2);
		$document->replaceDocument(self::$testFile);

		$versionsList = $document->getVersions();

		$this->assertEquals(4, $versionsList->Count(),"4 Versions created");
		$versionsArray = $versionsList->toArray();

		$this->assertEquals($versionsArray[0]->VersionCounter, 1,"Correct version count");
		$this->assertEquals($versionsArray[1]->VersionCounter, 2,"Correct version count");
		$this->assertEquals($versionsArray[2]->VersionCounter, 3,"Correct version count");
		$this->assertEquals($versionsArray[3]->VersionCounter, 4,"Correct version count");

	}



}