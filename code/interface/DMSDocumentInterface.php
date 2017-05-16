<?php
/**
 * Interface for a DMSDocument used in the Document Management System. A DMSDocument is create by storing a File
 * object in an instance of the DMSInterface. All write operations on the DMSDocument create a new relation, so we
 * never need to explicitly call the write() method on the DMSDocument DataObject
 */
interface DMSDocumentInterface
{
    /**
     * Returns a link to download this DMSDocument from the DMS store
     * @return String
     */
    public function getLink();

    /**
     * Return the extension of the file associated with the document
     */
    public function getExtension();

    /**
     * Returns the size of the file type in an appropriate format.
     *
     * @return string
     */
    public function getSize();

    /**
     * Return the size of the file associated with the document, in bytes.
     *
     * @return int
     */
    public function getAbsoluteSize();


    /**
     * Takes a File object or a String (path to a file) and copies it into the DMS, replacing the original document file
     * but keeping the rest of the document unchanged.
     * @param $file File object, or String that is path to a file to store
     * @return DMSDocumentInstance Document object that we replaced the file in
     */
    public function replaceDocument($file);

    /**
     * Hides the DMSDocument, so it does not show up when getByPage($myPage) is called
     * (without specifying the $showEmbargoed = true parameter). This is similar to expire, except that this method
     * should be used to hide DMSDocuments that have not yet gone live.
     * @return null
     */
    public function embargoIndefinitely();

    /**
     * Returns if this is DMSDocument is embargoed or expired.
     * @return bool True or False depending on whether this DMSDocument is embargoed or expired
     */
    public function isHidden();


    /**
     * Returns if this is DMSDocument is embargoed.
     * @return bool True or False depending on whether this DMSDocument is embargoed
     */
    public function isEmbargoed();

    /**
     * Hides the DMSDocument, so it does not show up when getByPage($myPage) is called. Automatically un-hides the
     * DMSDocument at a specific date.
     * @param $datetime String date time value when this DMSDocument should expire
     * @return null
     */
    public function embargoUntilDate($datetime);

    /**
     * Hides the document until any page it is linked to is published
     * @return null
     */
    public function embargoUntilPublished();

    /**
     * Clears any previously set embargos, so the DMSDocument always shows up in all queries.
     * @return null
     */
    public function clearEmbargo();

    /**
     * Returns if this is DMSDocument is expired.
     * @return bool True or False depending on whether this DMSDocument is expired
     */
    public function isExpired();

    /**
     * Hides the DMSDocument at a specific date, so it does not show up when getByPage($myPage) is called.
     * @param $datetime String date time value when this DMSDocument should expire
     * @return null
     */
    public function expireAtDate($datetime);

    /**
     * Clears any previously set expiry.
     * @return null
     */
    public function clearExpiry();

    /*---- FROM HERE ON: optional API features ----*/

    /**
     * Returns a DataList of all previous Versions of this DMSDocument (check the LastEdited date of each
     * object to find the correct one)
     * @return DataList List of DMSDocument objects
     */
    public function getVersions();
}
