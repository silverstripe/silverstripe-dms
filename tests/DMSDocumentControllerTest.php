<?php

/**
 * Class DMSDocumentControllerTest
 */
class DMSDocumentControllerTest extends SapphireTest
{
    protected static $fixture_file = "dmstest.yml";

    /**
     * Test that the download behaviour is either "open" or "download"
     *
     * @param string $behaviour
     * @param string $expectedDisposition
     * @dataProvider behaviourProvider
     */
    public function testDownloadBehaviourOpen($behaviour, $expectedDisposition)
    {
        DMS::$dmsFolder = DMS_DIR;    //sneakily setting the DMS folder to the folder where the test file lives

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

        $openDoc = new DMSDocument();
        $openDoc->Filename = "DMS-test-lorum-file.pdf";
        $openDoc->Folder = "tests";
        $openDoc->DownloadBehavior = $behaviour;
        $openDoc->clearEmbargo(false);
        $openDoc->write();
        $request = new SS_HTTPRequest('GET', 'index/' . $openDoc->ID);
        $request->match('index/$ID');
        $controller->index($request);
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
