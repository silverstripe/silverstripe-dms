<?php
class DMSVersioningTest extends SapphireTest
{
    protected $usesDatabase = true;

    /**
     * Stub PDF files for testing
     * @var string
     */
    public static $testFile = 'dms/tests/DMS-test-lorum-file.pdf';
    public static $testFile2 = 'dms/tests/DMS-test-document-2.pdf';

    /**
     * The test folder to write assets into
     *
     * @var string
     */
    protected $testDmsPath = 'assets/_dms-assets-test-versions';

    /**
     * Store values to reset back to after this test runs
     */
    public static $dmsEnableVersionsOld;

    /**
     * Use a test DMS folder, so we don't overwrite the live one, and clear out the test folder (in case a broken
     * test doesn't delete it)
     *
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        self::$dmsEnableVersionsOld = DMSDocument_versions::$enable_versions;
        DMSDocument_versions::$enable_versions = true;

        Config::inst()->update('DMS', 'folder_name', $this->testDmsPath);
        DMSFilesystemTestHelper::delete($this->testDmsPath);
    }

    /**
     * Delete the test folder after the tests run
     *
     * {@inheritDoc}
     */
    public function tearDown()
    {
        parent::tearDown();

        DMSFilesystemTestHelper::delete($this->testDmsPath);

        // Set the old DMS folder back again
        DMSDocument_versions::$enable_versions = self::$dmsEnableVersionsOld;
    }

    public function testDMSVersionStorage()
    {
        $this->markTestSkipped('Needs re-implementation, this test is not consistent.');

        $dms = DMS::inst();

        $document = $dms->storeDocument(self::$testFile);

        $this->assertNotNull($document, "Document object created");
        $this->assertTrue(
            file_exists(
                DMS::inst()->getStoragePath() . DIRECTORY_SEPARATOR . $document->Folder
                . DIRECTORY_SEPARATOR . $document->Filename
            ),
            "Document file copied into DMS folder"
        );

        $document->replaceDocument(self::$testFile2);
        $document->replaceDocument(self::$testFile);
        $document->replaceDocument(self::$testFile2);
        $document->replaceDocument(self::$testFile);

        $versionsList = $document->getVersions();

        $this->assertEquals(4, $versionsList->Count(), "4 Versions created");
        $versionsArray = $versionsList->toArray();

        $this->assertEquals($versionsArray[0]->VersionCounter, 1, "Correct version count");
        $this->assertEquals($versionsArray[1]->VersionCounter, 2, "Correct version count");
        $this->assertEquals($versionsArray[2]->VersionCounter, 3, "Correct version count");
        $this->assertEquals($versionsArray[3]->VersionCounter, 4, "Correct version count");
    }
}
