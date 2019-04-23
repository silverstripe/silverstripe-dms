<?php

use SilverStripe\Core\Config\Config;
use Sunnysideup\DMS\DMS;
use Sunnysideup\DMS\Model\DMSDocument;
use SilverStripe\CMS\Model\SiteTree;
use Sunnysideup\DMS\Tools\ShortCodeRelationFinder;
use SilverStripe\Dev\SapphireTest;
class ShortCodeRelationFinderTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

    public function testFindInRate()
    {
        Config::modify()->update(DMS::class, 'shortcode_handler_key', 'dms_document_link');

        $d1 = $this->objFromFixture(DMSDocument::class, 'd1');
        $d2 = $this->objFromFixture(DMSDocument::class, 'd2');

        $page1 = new SiteTree();
        $page1->Content = 'Condition:  <a title="document test 1" href="[dms_document_link,id=' . $d1->ID . ']">';
        $page1ID = $page1->write();

        $page2 = new SiteTree();
        $page2->Content = 'Condition:  <a title="document test 2" href="[dms_document_link,id=' . $d2->ID . ']">';
        $page2ID = $page2->write();

        $page3 = new SiteTree();
        $page3->Content = 'Condition:  <a title="document test 1" href="[dms_document_link,id=' . $d1->ID . ']">';
        $page3ID = $page3->write();

        $finder = new ShortCodeRelationFinder();

        $ids = $finder->findPageIDs('UnknownShortcode');
        $this->assertEquals(0, count($ids));

        $ids = $finder->findPageIDs($d1->ID);
        $this->assertNotContains($page2ID, $ids);
        $this->assertContains($page1ID, $ids);
        $this->assertContains($page3ID, $ids);
    }
}
