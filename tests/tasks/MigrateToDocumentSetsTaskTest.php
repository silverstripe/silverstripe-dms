<?php

class MigrateToDocumentSetsTaskTest extends SapphireTest
{
    protected static $fixture_file = 'MigrateToDocumentSetsTaskTest.yml';

    /**
     * Ensure that output is formatted either for the CLI or browser
     *
     * @param bool   $isCli
     * @param string $expected
     * @dataProvider outputProvider
     */
    public function testCanOutputToCliOrBrowser($isCli, $expected)
    {
        $lines = array('Test', 'Test line 2');

        $mock = $this->getMockBuilder('MigrateToDocumentSetsTask')
            ->setMethods(array('isCli'))
            ->getMock();

        $mock->expects($this->exactly(2))
            ->method('isCli')
            ->will($this->returnValue($isCli));

        ob_start();
        foreach ($lines as $line) {
            $mock->output($line);
        }
        $result = ob_get_clean();
        $this->assertSame($expected, $result);
    }

    /**
     * @return array[]
     */
    public function outputProvider()
    {
        return array(
            array(true, 'Test' . PHP_EOL . 'Test line 2' . PHP_EOL),
            array(false, 'Test<br />Test line 2<br />')
        );
    }

    /**
     * Ensure that providing an invalid action returns an error
     */
    public function testShowErrorOnInvalidAction()
    {
        $result = $this->runTask(array('action' => 'coffeetime'));
        $this->assertContains('Error! Specified action is not valid.', $result);
    }

    /**
     * Test that default document sets can be created for those pages that don't have them already
     */
    public function testCreateDefaultDocumentSets()
    {
        $this->fixtureOldRelations();

        $result = $this->runTask(array('action' => 'create-default-document-set'));
        $this->assertContains('Finished', $result);
        // There are four pages in the fixture, but one of them already has a document set, so should be unchanged
        $this->assertContains('Default document set added: 3', $result);
        $this->assertContains('Skipped: already has a set: 1', $result);

        // Test that some of the relationship records were written correctly
        $this->assertCount(1, $firstPageSets = $this->objFromFixture('SiteTree', 'one')->DocumentSets());
        $this->assertSame('Default', $firstPageSets->first()->Title);
        $this->assertCount(1, $this->objFromFixture('SiteTree', 'two')->DocumentSets());

        // With dryrun enabled and being run the second time, nothing should be done
        $result = $this->runTask(array('action' => 'create-default-document-set', 'dryrun' => '1'));
        $this->assertContains('Skipped: already has a set: 4', $result);
        $this->assertContains('NOTE: Dryrun mode enabled', $result);
    }

    /**
     * Test that legacy ORM relationship maps are migrated to the new page -> document set -> document relationship
     */
    public function testReassignDocumentsToFirstSet()
    {
        $this->fixtureOldRelations();

        // Ensure default sets are created
        $this->runTask(array('action' => 'create-default-document-set'));

        // Dryrun check
        $result = $this->runTask(array('action' => 'reassign-documents', 'dryrun' => '1'));
        $this->assertContains('NOTE: Dryrun mode enabled', $result);
        $this->assertContains('Reassigned to document set: 3', $result);

        // Actual run
        $result = $this->runTask(array('action' => 'reassign-documents'));
        $this->assertNotContains('NOTE: Dryrun mode enabled', $result);
        $this->assertContains('Reassigned to document set: 3', $result);

        // Smoke ORM checks
        $this->assertCount(1, $this->objFromFixture('SiteTree', 'one')->getAllDocuments());
        $this->assertCount(1, $this->objFromFixture('SiteTree', 'two')->getAllDocuments());
        $this->assertCount(0, $this->objFromFixture('SiteTree', 'four')->getAllDocuments());
    }

    /**
     * Centralises (slightly) logic for capturing direct output from the task
     *
     * @param  array $getVars
     * @return string Task output
     */
    protected function runTask(array $getVars)
    {
        $task = new MigrateToDocumentSetsTask;
        $request = new SS_HTTPRequest('GET', '/', $getVars);

        ob_start();
        $task->run($request);
        return ob_get_clean();
    }

    /**
     * Set up the old many many relationship table from documents to pages
     */
    protected function fixtureOldRelations()
    {
        if (!DB::get_schema()->hasTable('DMSDocument_Pages')) {
            DB::create_table('DMSDocument_Pages', array(
                'DMSDocumentID' => 'int(11) null',
                'SiteTreeID' => 'int(11) null'
            ));
        }

        $documentIds = $this->getFixtureFactory()->getIds('DMSDocument');
        $pageIds = $this->getFixtureFactory()->getIds('SiteTree');
        foreach (array('one', 'two', 'three') as $fixtureName) {
            $this->getFixtureFactory()->createRaw(
                'DMSDocument_Pages',
                'rln_' . $fixtureName,
                array('DMSDocumentID' => $documentIds[$fixtureName], 'SiteTreeID' => $pageIds[$fixtureName])
            );
        }
    }
}
