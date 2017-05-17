<?php

/**
 * Class DMSDocumentControllerTest
 */
class DMSDocumentControllerTest extends SapphireTest
{
    protected static $fixture_file = 'dmstest.yml';

    /**
     * Test that the download behaviour is either "open" or "download"
     *
     * @param string $behaviour
     * @param string $expectedDisposition
     * @dataProvider behaviourProvider
     */
    public function testDownloadBehaviourOpen($behaviour, $expectedDisposition)
    {
        Config::inst()->update('DMS', 'folder_name', 'assets/_unit-test-123');

        $this->logInWithPermission('ADMIN');

        /** @var DMSDocument_Controller $controller */
        $controller = $this->getMockBuilder('DMSDocument_Controller')
            ->setMethods(array('sendFile'))->getMock();

        $self = $this;
        $controller->expects($this->once())
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

        $request = new SS_HTTPRequest('GET', 'index/' . $openDoc->ID);
        $request->match('index/$ID');
        $controller->index($request);

        DMSFilesystemTestHelper::delete('assets/_unit-test-123');
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
