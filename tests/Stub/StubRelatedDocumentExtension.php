<?php

/**
 * Class StubRelatedDocumentExtension
 *
 * @package dms
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
