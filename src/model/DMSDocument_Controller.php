<?php

class DMSDocument_Controller extends Controller
{
    /**
     * Mode to switch for testing. Does not return document download, just document URL.
     *
     * @var boolean
     */
    public static $testMode = false;

    private static $allowed_actions = array(
        'index'
    );

    public function init()
    {
        Versioned::choose_site_stage();
        parent::init();
    }

    /**
     * Returns the document object from the request object's ID parameter.
     * Returns null, if no document found
     *
     * @param  SS_HTTPRequest $request
     * @return DMSDocument|null
     */
    protected function getDocumentFromID($request)
    {
        $doc = null;

        $id = Convert::raw2sql($request->param('ID'));
        if (strpos($id, 'version') === 0) {
            // Versioned document
            $id = $this->getDocumentIdFromSlug(str_replace('version', '', $id));
            $doc = DataObject::get_by_id('DMSDocument_versions', $id);
            $this->extend('updateVersionFromID', $doc, $request);
        } else {
            // Normal document
            $doc = DataObject::get_by_id('DMSDocument', $this->getDocumentIdFromSlug($id));
            $this->extend('updateDocumentFromID', $doc, $request);
        }

        return $doc;
    }

    /**
     * Get a document's ID from a "friendly" URL slug containing a numeric ID and slugged title
     *
     * @param  string $slug
     * @return int
     * @throws InvalidArgumentException if an invalid format is provided
     */
    protected function getDocumentIdFromSlug($slug)
    {
        $parts = (array) sscanf($slug, '%d-%s');
        $id = array_shift($parts);
        if (is_numeric($id)) {
            return (int) $id;
        }
        throw new InvalidArgumentException($slug . ' is not a valid DMSDocument URL');
    }

    /**
     * Access the file download without redirecting user, so we can block direct
     * access to documents.
     */
    public function index(SS_HTTPRequest $request)
    {
        $doc = $this->getDocumentFromID($request);

        if (!empty($doc)) {
            $canView = $doc->canView();

            if ($canView) {
                $path = $doc->getFullPath();
                if (is_file($path)) {
                    $fileBin = trim(`whereis file`);
                    if (function_exists('finfo_file')) {
                        // discover the mime type properly
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $path);
                    } elseif (is_executable($fileBin)) {
                        $path = escapeshellarg($path);
                        // try to use the system tool
                        $mime = `$fileBin -i -b $path`;
                        $mime = explode(';', $mime);
                        $mime = trim($mime[0]);
                    } else {
                        // make do with what we have
                        $ext = $doc->getExtension();
                        if ($ext == 'pdf') {
                            $mime = 'application/pdf';
                        } elseif ($ext == 'html' || $ext =='htm') {
                            $mime = 'text/html';
                        } else {
                            $mime = 'application/octet-stream';
                        }
                    }

                    if (self::$testMode) {
                        return $path;
                    }

                    // set fallback if no config nor file-specific value
                    $disposition = 'attachment';

                    // file-specific setting
                    if ($doc->DownloadBehavior == 'open') {
                        $disposition = 'inline';
                    }

                    //if a DMSDocument can be downloaded and all the permissions/privileges has passed,
                    //its ViewCount should be increased by 1 just before the browser sending the file to front.
                    $doc->trackView();

                    return $this->sendFile($path, $mime, $doc->getFilenameWithoutID(), $disposition);
                }
            }
        }

        if (self::$testMode) {
            return 'This asset does not exist.';
        }
        $this->httpError(404, 'This asset does not exist.');
    }

    /**
     * @param string $path File path
     * @param string $mime File mime type
     * @param string $name File name
     * @param string $disposition Content dispositon
     */
    protected function sendFile($path, $mime, $name, $disposition)
    {
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path), null);
        if (!empty($mime) && $mime != "text/html") {
            header('Content-Disposition: '.$disposition.'; filename="'.addslashes($name).'"');
        }
        header('Content-transfer-encoding: 8bit');
        header('Expires: 0');
        header('Pragma: cache');
        header('Cache-Control: private');
        flush();
        readfile($path);
        exit;
    }
}
