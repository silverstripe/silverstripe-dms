<?php
class DMS extends SS_Object implements DMSInterface
{
    /**
     * Folder to store the documents in
     *
     * @config
     * @var string
     */
    private static $folder_name = 'assets/_dmsassets';

    /**
     * How many documents to store in a single folder. The square of this number is the maximum number of documents.
     *
     * The number should be a multiple of 10
     *
     * @config
     * @var int
     */
    private static $folder_size = 1000;

    /**
     * Singleton instance of a DMSInterface
     *
     * @var DMSInterface
     */
    private static $instance;

    /**
     * The shortcode handler key. Can be changed by user code.
     *
     * @config
     * @var string
     */
    private static $shortcode_handler_key = 'dms_document_link';

    /**
     * Factory method that returns an instance of the DMS. This could be any class that implements the DMSInterface.
     *
     * @return DMSInterface An instance of the Document Management System
     */
    public static function inst()
    {
        if (!self::$instance) {
            self::$instance = new static();

            $dmsPath = self::$instance->getStoragePath();

            if (!is_dir($dmsPath)) {
                self::$instance->createStorageFolder($dmsPath);
            }

            if (!file_exists($dmsPath . DIRECTORY_SEPARATOR . '.htaccess')) {
                // Restrict access to the storage folder
                copy(
                    BASE_PATH . DIRECTORY_SEPARATOR . DMS_DIR . DIRECTORY_SEPARATOR
                    . 'resources' . DIRECTORY_SEPARATOR . '.htaccess',
                    $dmsPath . DIRECTORY_SEPARATOR . '.htaccess'
                );

                copy(
                    BASE_PATH . DIRECTORY_SEPARATOR . DMS_DIR . DIRECTORY_SEPARATOR
                    . 'resources' . DIRECTORY_SEPARATOR . 'web.config',
                    $dmsPath . DIRECTORY_SEPARATOR . 'web.config'
                );
            }
        }
        return self::$instance;
    }

    /**
     * Get the storage path for DMS documents
     *
     * @return string
     */
    public function getStoragePath()
    {
        return BASE_PATH . DIRECTORY_SEPARATOR . $this->config()->get('folder_name');
    }

    /**
     * Gets a file path from either a File or a string
     *
     * @param  string|File $file
     * @return string
     * @throws FileNotFoundException If an unexpected value was provided, or the filename was null
     */
    public function transformFileToFilePath($file)
    {
        //confirm we have a file
        $filePath = null;
        if (is_string($file)) {
            $filePath = $file;
        } elseif (is_object($file) && $file->is_a("File")) {
            $filePath = $file->Filename;
        }

        if (!$filePath) {
            throw new FileNotFoundException();
        }

        return $filePath;
    }

    /**
     * Takes a File object or a String (path to a file) and copies it into the DMS. The original file remains unchanged.
     * When storing a document, sets the fields on the File has "tag" metadata.
     * @param  File|string $file File object, or String that is path to a file to store,
     *              e.g. "assets/documents/industry/supplied-v1-0.pdf"
     * @return DMSDocument
     */
    public function storeDocument($file)
    {
        $filePath = $this->transformFileToFilePath($file);

        // Create a new document and get its ID
        $doc = DMSDocument::create();
        $doc->write();
        $doc->storeDocument($filePath);

        return $doc;
    }

    /**
     * Returns a number of Document objects that match a full-text search of the Documents and their contents
     * (if contents is searchable and compatible search module is installed - e.g. FullTextSearch module)
     * @param $searchText String to search for
     * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
     * @return DocumentInterface
     */
    public function getByFullTextSearch($searchText, $showEmbargoed = false)
    {
        // TODO: Implement getByFullTextSearch() method.
    }

    public function getByPage(SiteTree $page, $showEmbargoed = false)
    {
        /** @var ArrayList $documents */
        $documents = $page->getAllDocuments();

        if (!$showEmbargoed) {
            foreach ($documents as $document) {
                if ($document->isEmbargoed()) {
                    $documents->remove($document);
                }
            }
        }

        return $documents;
    }

    public function getDocumentSetsByPage(SiteTree $page)
    {
        return $page->DocumentSets();
    }

    /**
     * Creates a storage folder for the given path
     *
     * @param  string $path Path to create a folder for
     * @return $this
     */
    public function createStorageFolder($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $this;
    }

    /**
     * Calculates the storage path from a database DMSDocument ID
     *
     * @return int
     */
    public function getStorageFolder($id)
    {
        return intval($id / self::config()->get('folder_size'));
    }

    /**
     * Get the shortcode handler key
     *
     * @return string
     */
    public function getShortcodeHandlerKey()
    {
        return (string) Config::inst()->get('DMS', 'shortcode_handler_key');
    }
}
