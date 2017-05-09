<?php
class DMS implements DMSInterface
{
    /**
     * Folder to store the documents in
     *
     * @var string
     */
    public static $dmsFolder = 'dms-assets';

    /**
     * How many documents to store in a single folder. The square of this number is the maximum number of documents.
     *
     * The number should be a multiple of 10
     *
     * @var int
     */
    public static $dmsFolderSize = 1000;


    /**
     * Factory method that returns an instance of the DMS. This could be any class that implements the DMSInterface.
     * @static
     * @return DMSInterface An instance of the Document Management System
     */
    public static function inst()
    {
        $dmsPath = self::get_dms_path();

        $dms = new DMS();
        if (!is_dir($dmsPath)) {
            self::create_storage_folder($dmsPath);
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
        return $dms;
    }

    /**
     * @return string
     */
    public static function get_dms_path()
    {
        return BASE_PATH . DIRECTORY_SEPARATOR . self::$dmsFolder;
    }

    public static function transform_file_to_file_path($file)
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
     * @param $file File object, or String that is path to a file to store,
     *              e.g. "assets/documents/industry/supplied-v1-0.pdf"
     * @return DMSDocument
     */
    public function storeDocument($file)
    {
        $filePath = self::transform_file_to_file_path($file);

        //create a new document and get its ID
        $doc = new DMSDocument();
        $doc->write();
        $doc->storeDocument($filePath);

        return $doc;
    }

    /**
     *
     * Returns a number of Document objects based on the a search by tags. You can search by category alone,
     * by tag value alone, or by both. I.e:
     *
     * <code>
     * getByTag("fruits", null);
     * getByTag(null, "banana");
     * getByTag("fruits", "banana");
     * </code>
     *
     * @param null $category The metadata category to search for
     * @param null $value The metadata value to search for
     * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
     * @return DocumentInterface
     */
    public function getByTag($category = null, $value = null, $showEmbargoed = false)
    {
        // TODO: Implement getByTag() method.
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
        return $page->getDocumentSets();
    }

    /**
     * Creates a storage folder for the given path
     * @param $path Path to create a folder for
     */
    public static function create_storage_folder($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
    }

    /**
     * Calculates the storage path from a database DMSDocument ID
     */
    public static function get_storage_folder($id)
    {
        $folderName = intval($id / self::$dmsFolderSize);
        return $folderName;
    }
}
