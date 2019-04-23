<?php

use SilverStripe\Core\Config\Config;
use Sunnysideup\DMS\DMS;
use SilverStripe\CMS\Model\SiteTree;
use Sunnysideup\DMS\Model\DMSDocument;
use Sunnysideup\DMS\Model\DMSDocumentSet;
use SilverStripe\Assets\Filesystem;
use SilverStripe\Dev\FunctionalTest;
class DMSTest extends FunctionalTest
{
    protected static $fixture_file = 'dmstest.yml';

    /**
     * Stub PDF files for testing
     *
     * @var string
     */
    public static $testFile = 'dms/tests/DMS-test-lorum-file.pdf';
    public static $testFile2 = 'dms/tests/DMS-test-document-2.pdf';

    /**
     * The test folder to write assets into
     *
     * @var string
     */
    protected $testDmsPath = 'assets/_dms-assets-test-1234';

    /**
     * @var DMSInterace
     */
    protected $dms;

    /**
     * Use a test DMS folder, so we don't overwrite the live one, and clear it out in case of previous broken tests
     *
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();
        Config::modify()->update(DMS::class, 'folder_name', $this->testDmsPath);
        DMSFilesystemTestHelper::delete($this->testDmsPath);
        $this->dms = DMS::inst();
    }

    /**
     * Delete the test folder after the test runs
     *
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        DMSFilesystemTestHelper::delete($this->testDmsPath);
    }

    public function testDMSStorage()
    {
        $file = self::$testFile;
        $document = $this->dms->storeDocument($file);

        $this->assertNotNull($document, "Document object created");
        $this->assertTrue(
            file_exists(
                DMS::inst()->getStoragePath() . DIRECTORY_SEPARATOR . $document->Folder
                . DIRECTORY_SEPARATOR . $document->Filename
            ),
            "Document file copied into DMS folder"
        );
    }

    public function testDMSFolderSpanning()
    {
        Config::modify()->update(DMS::class, 'folder_size', 5);
        $file = self::$testFile;

        $documents = [];
        for ($i = 0; $i <= 16; $i++) {
            $document = $this->dms->storeDocument($file);
            $this->assertNotNull($document, "Document object created on run number: $i");

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: upgrade to SS4
  * OLD: ->getFullPath() (case sensitive)
  * NEW: ->getFilename() (COMPLEX)
  * EXP: You may need to add ASSETS_PATH."/" in front of this ...
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
            $this->assertTrue(file_exists($document->getFilename()));
            $documents[] = $document;
        }

        // Test document objects have their folders set
        $folders = [];
        for ($i = 0; $i <= 16; $i++) {
            $folderName = $documents[$i]->Folder;
            $this->assertTrue(

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: upgrade to SS4
  * OLD: ->getFullPath() (case sensitive)
  * NEW: ->getFilename() (COMPLEX)
  * EXP: You may need to add ASSETS_PATH."/" in front of this ...
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
                strpos($documents[$i]->getFilename(), DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR) !== false,
                "Correct folder name for the documents. Document path contains reference to folder name '$folderName'"
            );
            $folders[] = $folderName;
        }

        // Test we created 4 folder to contain the 17 files
        foreach ($folders as $f) {
            $this->assertTrue(
                is_dir(DMS::inst()->getStoragePath() . DIRECTORY_SEPARATOR . $f),
                "Document folder '$f' exists"
            );
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
                DMS::inst()->getStoragePath() . DIRECTORY_SEPARATOR . $document->Folder
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
        $pageWithEmbargoes = $this->objFromFixture(SiteTree::class, 's3');
        $documents = $this->dms->getByPage($pageWithEmbargoes);
        // Fixture: 6 documents in set, 1 is embargoed
        $this->assertCount(5, $documents, 'Embargoed documents are excluded by default');
        $this->assertContainsOnlyInstancesOf(DMSDocument::class, $documents);
    }

    /**
     * Test that embargoed documents are excluded from getByPage
     */
    public function testGetByPageWithEmbargoedDocuments()
    {
        $pageWithEmbargoes = $this->objFromFixture(SiteTree::class, 's3');
        $documents = $this->dms->getByPage($pageWithEmbargoes, true);
        // Fixture: 6 documents in set, 1 is embargoed
        $this->assertCount(6, $documents, 'Embargoed documents can be included');
        $this->assertContainsOnlyInstancesOf(DMSDocument::class, $documents);
    }

    /**
     * Ensure the shortcode handler key is configurable
     */
    public function testShortcodeHandlerKeyIsConfigurable()
    {
        Config::modify()->update(DMS::class, 'shortcode_handler_key', 'testing');
        $this->assertSame('testing', DMS::inst()->getShortcodeHandlerKey());
    }

    /**
     * Test that document sets can be retrieved for a given page
     */
    public function testGetDocumentSetsByPage()
    {
        $page = $this->objFromFixture(SiteTree::class, 's1');
        $sets = $this->dms->getDocumentSetsByPage($page);
        $this->assertCount(2, $sets);
        $this->assertContainsOnlyInstancesOf(DMSDocumentSet::class, $sets);
    }

    /**
     * Ensure that assets/* folders are not included in filesystem sync operations
     */
    public function testFolderExcludedFromFilesystemSync()
    {
        // Undo setup config changes
        Config::unnest();
        Config::nest();

        $result = Filesystem::config()->get('sync_blacklisted_patterns');
        $folderName = substr(DMS::config()->get('folder_name'), 7);
        $this->assertContains('/^' . $folderName . '$/i', $result);
    }
}
