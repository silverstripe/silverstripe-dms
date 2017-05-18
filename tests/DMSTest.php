<?php
class DMSTest extends FunctionalTest
{
    protected static $fixture_file = 'dmstest.yml';

    /**
     * Stub PDF files for testing
     * @var string
     */
    public static $testFile = 'dms/tests/DMS-test-lorum-file.pdf';
    public static $testFile2 = 'dms/tests/DMS-test-document-2.pdf';

    /**
     * Store values to reset back to after this test runs
     */
    public static $dmsFolderOld;
    public static $dmsFolderSizeOld;

    /**
     * @var DMS
     */
    protected $dms;

    public function setUp()
    {
        parent::setUp();

        self::$dmsFolderOld = DMS::$dmsFolder;
        self::$dmsFolderSizeOld = DMS::$dmsFolderSize;

        //use a test DMS folder, so we don't overwrite the live one
        DMS::$dmsFolder = 'dms-assets-test-1234';

        //clear out the test folder (in case a broken test doesn't delete it)
        $this->delete(BASE_PATH . DIRECTORY_SEPARATOR . 'dms-assets-test-1234');

        $this->dms = DMS::inst();
    }

    public function tearDown()
    {
        parent::tearDown();

        self::$is_running_test = true;

        $d = DataObject::get("DMSDocument");
        foreach ($d as $d1) {
            $d1->delete();
        }
        $t = DataObject::get("DMSTag");
        foreach ($t as $t1) {
            $t1->delete();
        }

        //delete the test folder after the test runs
        $this->delete(BASE_PATH . DIRECTORY_SEPARATOR . 'dms-assets-test-1234');

        //set the old DMS folder back again
        DMS::$dmsFolder = self::$dmsFolderOld;
        DMS::$dmsFolderSize = self::$dmsFolderSizeOld;

        self::$is_running_test = $this->originalIsRunningTest;
    }

    /**
     * Delete a file that was created during a unit test
     *
     * @param string $path
     */
    public function delete($path)
    {
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

    public function testDMSStorage()
    {
        $file = self::$testFile;
        $document = $this->dms->storeDocument($file);

        $this->assertNotNull($document, "Document object created");
        $this->assertTrue(
            file_exists(
                DMS::get_dms_path() . DIRECTORY_SEPARATOR . $document->Folder
                . DIRECTORY_SEPARATOR . $document->Filename
            ),
            "Document file copied into DMS folder"
        );
    }

    public function testDMSFolderSpanning()
    {
        DMS::$dmsFolderSize = 5;
        $file = self::$testFile;

        $documents = array();
        for ($i = 0; $i <= 16; $i++) {
            $document = $this->dms->storeDocument($file);
            $this->assertNotNull($document, "Document object created on run number: $i");
            $this->assertTrue(file_exists($document->getFullPath()));
            $documents[] = $document;
        }

        // Test document objects have their folders set
        $folders = array();
        for ($i = 0; $i <= 16; $i++) {
            $folderName = $documents[$i]->Folder;
            $this->assertTrue(
                strpos($documents[$i]->getFullPath(), DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR) !== false,
                "Correct folder name for the documents. Document path contains reference to folder name '$folderName'"
            );
            $folders[] = $folderName;
        }

        // Test we created 4 folder to contain the 17 files
        foreach ($folders as $f) {
            $this->assertTrue(is_dir(DMS::get_dms_path() . DIRECTORY_SEPARATOR . $f), "Document folder '$f' exists");
        }
    }

    public function testReplaceDocument()
    {
        // Store the first document
        $document = $this->dms->storeDocument(self::$testFile);
        $document->Title = "My custom title";
        $document->Description = "My custom description";
        $document->write();

        // Then overwrite with a second document
        $document = $document->replaceDocument(self::$testFile2);

        $this->assertNotNull($document, "Document object created");
        $this->assertTrue(
            file_exists(
                DMS::get_dms_path() . DIRECTORY_SEPARATOR . $document->Folder
                . DIRECTORY_SEPARATOR . $document->Filename
            ),
            "Document file copied into DMS folder"
        );
        $this->assertContains(
            "DMS-test-document-2",
            $document->Filename,
            "Original document filename is contain in the new filename"
        );
        $this->assertEquals("My custom title", $document->Title, "Custom title not modified");
        $this->assertEquals("My custom description", $document->Description, "Custom description not modified");
    }

    /**
     * Test that documents can be returned by a given page
     */
    public function testGetByPageWithoutEmbargoes()
    {
        $pageWithEmbargoes = $this->objFromFixture('SiteTree', 's3');
        $documents = $this->dms->getByPage($pageWithEmbargoes);
        // Fixture: 6 documents in set, 1 is embargoed
        $this->assertCount(5, $documents, 'Embargoed documents are excluded by default');
        $this->assertContainsOnlyInstancesOf('DMSDocument', $documents);
    }

    /**
     * Test that embargoed documents are excluded from getByPage
     */
    public function testGetByPageWithEmbargoedDocuments()
    {
        $pageWithEmbargoes = $this->objFromFixture('SiteTree', 's3');
        $documents = $this->dms->getByPage($pageWithEmbargoes, true);
        // Fixture: 6 documents in set, 1 is embargoed
        $this->assertCount(6, $documents, 'Embargoed documents can be included');
        $this->assertContainsOnlyInstancesOf('DMSDocument', $documents);
    }

    /**
     * Ensure the shortcode handler key is configurable
     */
    public function testShortcodeHandlerKeyIsConfigurable()
    {
        Config::inst()->update('DMS', 'shortcode_handler_key', 'testing');
        $this->assertSame('testing', DMS::inst()->getShortcodeHandlerKey());
    }

    /**
     * Test that document sets can be retrieved for a given page
     */
    public function testGetDocumentSetsByPage()
    {
        $page = $this->objFromFixture('SiteTree', 's1');
        $sets = $this->dms->getDocumentSetsByPage($page);
        $this->assertCount(2, $sets);
        $this->assertContainsOnlyInstancesOf('DMSDocumentSet', $sets);
    }
}
