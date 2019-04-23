<?php

namespace Sunnysideup\DMS\Interfaces;

use SilverStripe\CMS\Model\SiteTree;

/**
 *
 */
interface DMSInterface
{
    /**
     * Factory method that returns an instance of the DMS. This could be any class that implements the DMSInterface.
     * @static
     * @abstract
     * @return DMSInterface An instance of the Document Management System
     */
    public static function inst();

    /**
     * Takes a File object or a String (path to a file) and copies it into the DMS. The original file remains unchanged.
     * When storing a document, sets the fields on the File has "tag" metadata.
     * @abstract
     * @param $file File object, or String that is path to a file to store
     * @return DMSDocumentInstance Document object that we just created
     */
    public function storeDocument($file);

    /**
     * Returns a number of Document objects that match a full-text search of the Documents and their contents
     * (if contents is searchable and compatible search module is installed - e.g. FullTextSearch module)
     * @abstract
     * @param $searchText String to search for
     * @param bool $showEmbargoed Boolean that specifies if embargoed documents should be included in results
     * @return DMSDocumentInterface
     */
    public function getByFullTextSearch($searchText, $showEmbargoed = false);


    /**
     * Returns a list of Document objects associated with a Page via intermediary document sets
     *
     * @param  SiteTree $page          SiteTree to fetch the associated Documents from
     * @param  bool     $showEmbargoed Boolean that specifies if embargoed documents should be included in results
     * @return ArrayList               Document list associated with the Page
     */
    public function getByPage(SiteTree $page, $showEmbargoed = false);

    /**
     * Returns a list of Document Set objects associated with a Page
     *
     * @param  SiteTree $page SiteTree to fetch the associated Document Sets from
     * @return ArrayList      Document list associated with the Page
     */
    public function getDocumentSetsByPage(SiteTree $page);
}
