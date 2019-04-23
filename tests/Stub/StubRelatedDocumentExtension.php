<?php

use Sunnysideup\DMS\Model\DMSDocument;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Dev\TestOnly;

/**
 * Class StubRelatedDocumentExtension
 *
 * @package dms
 */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: upgrade to SS4
  * OLD:  extends DataExtension (ignore case)
  * NEW:  extends DataExtension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
class StubRelatedDocumentExtension extends DataExtension implements TestOnly
{
    /**
     * For method {@link DMSDocument::getRelatedDocuments}
     *
     * @param  ArrayList $relatedDocuments
     * @return ArrayList
     */
    public function updateRelatedDocuments($relatedDocuments)
    {
        $relatedDocuments->push($this->getFakeDocument());
        return $relatedDocuments;
    }

    /**
     * For method {@link DMSDocumentSet::getDocuments}
     *
     * @param  ArrayList $relatedDocuments
     * @return ArrayList
     */
    public function updateDocuments($documents)
    {
        $documents->push($this->getFakeDocument());
        return $documents;
    }

    /**
     * Return a dummy document for testing purposes
     *
     * @return DMSDocument
     */
    protected function getFakeDocument()
    {
        $fakeDocument = new DMSDocument;
        $fakeDocument->Filename = 'Extended';
        return $fakeDocument;
    }
}
