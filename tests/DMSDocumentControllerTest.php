<?php

/**
 * Class DMSDocumentControllerTest
 */
class DMSDocumentControllerTest extends SapphireTest {

    protected static $fixture_file = "dmstest.yml";

    public function testDownloadBehaviourOpen() {
        DMS::$dmsFolder = DMS_DIR;    //sneakily setting the DMS folder to the folder where the test file lives

        $this->logInWithPermission('ADMIN');
        /** @var DMSDocument_Controller $controller */
        $controller = $this->getMockBuilder('DMSDocument_Controller')
            ->setMethods(array('sendFile'))->getMock();
        $self = $this;
        $controller->expects($this->once())->method('sendFile')->will($this->returnCallback(function($path, $mime, $name, $disposition) use($self) {
            $self->assertEquals('inline', $disposition);
        }));
        $openDoc = new DMSDocument();
        $openDoc->Filename = "DMS-test-lorum-file.pdf";
        $openDoc->Folder = "tests";
        $openDoc->DownloadBehavior = 'open';
        $openDoc->clearEmbargo(false);
        $openDoc->write();
        $request = new SS_HTTPRequest('GET', 'index/' . $openDoc->ID);
        $request->match('index/$ID');
        $controller->index($request);
    }

    public function testDownloadBehaviourDownload() {
        DMS::$dmsFolder = DMS_DIR;    //sneakily setting the DMS folder to the folder where the test file lives

        $this->logInWithPermission('ADMIN');
        /** @var DMSDocument_Controller $controller */
        $controller = $this->getMockBuilder('DMSDocument_Controller')
            ->setMethods(array('sendFile'))->getMock();
        $self = $this;
        $controller->expects($this->once())->method('sendFile')->will($this->returnCallback(function($path, $mime, $name, $disposition) use($self) {
            $self->assertEquals('attachment', $disposition);
        }));
        $openDoc = new DMSDocument();
        $openDoc->Filename = "DMS-test-lorum-file.pdf";
        $openDoc->Folder = "tests";
        $openDoc->DownloadBehavior = 'download';
        $openDoc->clearEmbargo(false);
        $openDoc->write();
        $request = new SS_HTTPRequest('GET', 'index/' . $openDoc->ID);
        $request->match('index/$ID');
        $controller->index($request);
    }

}
