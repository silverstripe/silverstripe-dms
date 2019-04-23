<?php

use SilverStripe\Core\Config\Config;
use Sunnysideup\DMS\DMS;
use Sunnysideup\DMS\Model\DMSDocument_Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;

/**
 * Class DMSDocumentControllerTest
 */
class DMSDocumentControllerTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

    /**
     * @var DMSDocument_Controller
     */
    protected $controller;

    public function setUp()
    {
        parent::setUp();

        Config::modify()->update(DMS::class, 'folder_name', 'assets/_unit-test-123');
        $this->logInWithPermission('ADMIN');

        $this->controller = $this->getMockBuilder(DMSDocument_Controller::class)
            ->setMethods(array('sendFile'))
            ->getMock();
    }

    public function tearDown()
    {
        DMSFilesystemTestHelper::delete('assets/_unit-test-123');
        parent::tearDown();
    }

    /**
     * Test that the download behaviour is either "open" or "download"
     *
     * @param string $behaviour
     * @param string $expectedDisposition
     * @dataProvider behaviourProvider
     */
    public function testDownloadBehaviourOpen($behaviour, $expectedDisposition)
    {
        $self = $this;
        $this->controller->expects($this->once())
            ->method('sendFile')
            ->will(
                $this->returnCallback(function ($path, $mime, $name, $disposition) use ($self, $expectedDisposition) {
                    $self->assertEquals($expectedDisposition, $disposition);
                })
            );

        $openDoc = DMS::inst()->storeDocument('dms/tests/DMS-test-lorum-file.pdf');
        $openDoc->DownloadBehavior = $behaviour;
        $openDoc->clearEmbargo(false);
        $openDoc->write();

        $request = new HTTPRequest('GET', $openDoc->Link());
        $request->match('dmsdocument/$ID');
        $this->controller->index($request);
    }

    /**
     * @return array[]
     */
    public function behaviourProvider()
    {
        return array(
            array('open', 'inline'),
            array('download', 'attachment')
        );
    }
}
