<?php
class DMSTest extends SapphireTest {

	static $testFile = 'dms/tests/DMS-test-lorum-file';

	//store values to reset back to after this test runs
	static $dmsFolderOld;
	static $dmsFolderSizeOld;

	function setUp() {
		self::$dmsFolderOld = DMS::$dmsFolder;
		self::$dmsFolderSizeOld = DMS::$dmsFolderSize;

		//use a test DMS folder, so we don't overwrite the live one
		DMS::$dmsFolder = 'dms-assets-test-1234';

		//clear out the test folder (in case a broken test doesn't delete it)
		$this->delete(BASE_PATH . DIRECTORY_SEPARATOR . DMS::$dmsFolder);
	}

	function tearDown() {
		//delete the test folder after the test runs
		$this->delete(BASE_PATH . DIRECTORY_SEPARATOR . DMS::$dmsFolder);

		//set the old DMS folder back again
		DMS::$dmsFolder = self::$dmsFolderOld;
		DMS::$dmsFolderSize = self::$dmsFolderSizeOld;
	}

	public function delete($path) {
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


	function testDMSStorage() {
		$dms = DMS::getDMSInstance();

		$file = BASE_PATH . DIRECTORY_SEPARATOR . self::$testFile;
		$document = $dms->storeDocument($file);

		$this->assertNotNull($document, "Document object created");
		$this->assertTrue(file_exists(DMS::$dmsPath . DIRECTORY_SEPARATOR . $document->Filename),"Document file copied into DMS folder");

		//$title = $document->getTag('title');
	}

	function testDMSFolderSpanning() {
		DMS::$dmsFolderSize = 5;
		$dms = DMS::getDMSInstance();

		$file = BASE_PATH . DIRECTORY_SEPARATOR . self::$testFile;

		for($i = 0; $i <= 16; $i++) {
			$document = $dms->storeDocument($file);
			$this->assertNotNull($document, "Document object created on run number: $i");
		}

		//test we created 4 folder to contain the 17 files
		$this->assertTrue(is_dir(DMS::$dmsPath . DIRECTORY_SEPARATOR . '1'));
		$this->assertTrue(is_dir(DMS::$dmsPath . DIRECTORY_SEPARATOR . '2'));
		$this->assertTrue(is_dir(DMS::$dmsPath . DIRECTORY_SEPARATOR . '3'));
		$this->assertTrue(is_dir(DMS::$dmsPath . DIRECTORY_SEPARATOR . '4'));
		$this->assertFalse(is_dir(DMS::$dmsPath . DIRECTORY_SEPARATOR . '5'));
	}


}