<?php
class DMSEmbargoTest extends SapphireTest {

	static $fixture_file = "dms/tests/dmsembargotest.yml";

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

	function createFakeHTTPRequest($id) {
		$r = new SS_HTTPRequest('GET','index/'.$id);
		$r->match('index/$ID');
		return $r;
	}

	function testBasicEmbargo() {
		$oldDMSFolder = DMS::$dmsFolder;
		DMS::$dmsFolder = DMS_DIR;    //sneakily setting the DMS folder to the folder where the test file lives

		$doc = new DMSDocument();
		$doc->Filename = "DMS-test-lorum-file.pdf";
		$doc->Folder = "tests";
		$docID = $doc->write();

		//fake a request for a document
		$controller = new DMSDocument_Controller();
		DMSDocument_Controller::$testMode = true;
		$result = $controller->index($this->createFakeHTTPRequest($docID));
		$this->assertEquals($doc->getFullPath(),$result,"Correct underlying file returned (in test mode)");

		$doc->embargoIndefinitely();

		$this->logInWithPermission('ADMIN');
		$result = $controller->index($this->createFakeHTTPRequest($docID));
		$this->assertEquals($doc->getFullPath(),$result,"Admins can still download embargoed files");

		$this->logInWithPermission('random-user-group');
		$result = $controller->index($this->createFakeHTTPRequest($docID));
		$this->assertNotEquals($doc->getFullPath(),$result,"File no longer returned (in test mode) when switching to other user group");

		DMS::$dmsFolder = $oldDMSFolder;
	}

	function testEmbargoIndefinitely() {
		$doc = new DMSDocument();
		$doc->Filename = "DMS-test-lorum-file.pdf";
		$doc->Folder = "tests";
		$doc->write();

		$doc->embargoIndefinitely();
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertTrue($doc->isEmbargoed(),"Document is embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$doc->clearEmbargo();
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

	}

	function testExpireAtDate() {
		$doc = new DMSDocument();
		$doc->Filename = "DMS-test-lorum-file.pdf";
		$doc->Folder = "tests";
		$doc->write();

		$doc->expireAtDate(strtotime('-1 second'));
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertTrue($doc->isExpired(),"Document is expired");

		$expireTime = "2019-04-05 11:43:13";
		$doc->expireAtDate($expireTime);
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		SS_Datetime::set_mock_now($expireTime);
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertTrue($doc->isExpired(),"Document is expired");
		SS_Datetime::clear_mock_now();

		$doc->expireAtDate(strtotime('-1 second'));
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertTrue($doc->isExpired(),"Document is expired");

		$doc->clearExpiry();
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");
	}

	function testEmbargoUntilDate() {
		$doc = new DMSDocument();
		$doc->Filename = "DMS-test-lorum-file.pdf";
		$doc->Folder = "tests";
		$doc->write();

		$doc->embargoUntilDate(strtotime('+1 minute'));
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertTrue($doc->isEmbargoed(),"Document is embargoed");

		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$doc->embargoUntilDate(strtotime('-1 second'));
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$embargoTime = "2019-04-05 11:43:13";
		$doc->embargoUntilDate($embargoTime);
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertTrue($doc->isEmbargoed(),"Document is embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		SS_Datetime::set_mock_now($embargoTime);
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		SS_Datetime::clear_mock_now();

		$doc->clearEmbargo();
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");
	}

	function testEmbargoUntilPublished() {
		$s1 = $this->objFromFixture('SiteTree','s1');

		$doc = new DMSDocument();
		$doc->Filename = "test file";
		$doc->Folder = "0";
		$dID = $doc->write();

		$doc->addPage($s1);

		$s1->publish('Stage','Live');
		$s1->doPublish();
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$doc->embargoUntilPublished();
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertTrue($doc->isEmbargoed(),"Document is embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$s1->publish('Stage','Live');
		$s1->doPublish();
		$doc = DataObject::get_by_id("DMSDocument",$dID);
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$doc->embargoUntilPublished();
		$doc = DataObject::get_by_id("DMSDocument",$dID);
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertTrue($doc->isEmbargoed(),"Document is embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$doc->embargoIndefinitely();
		$doc = DataObject::get_by_id("DMSDocument",$dID);
		$this->assertTrue($doc->isHidden(),"Document is hidden");
		$this->assertTrue($doc->isEmbargoed(),"Document is embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$s1->publish('Stage','Live');
		$s1->doPublish();
		$doc = DataObject::get_by_id("DMSDocument",$dID);
		$this->assertTrue($doc->isHidden(),"Document is still hidden because although the untilPublish flag is cleared, the indefinitely flag is still there");
		$this->assertTrue($doc->isEmbargoed(),"Document is embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");

		$doc->clearEmbargo();
		$doc = DataObject::get_by_id("DMSDocument",$dID);
		$this->assertFalse($doc->isHidden(),"Document is not hidden");
		$this->assertFalse($doc->isEmbargoed(),"Document is not embargoed");
		$this->assertFalse($doc->isExpired(),"Document is not expired");
	}
}