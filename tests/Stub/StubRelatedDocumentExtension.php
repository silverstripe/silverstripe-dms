<?php

class StubRelatedDocumentExtension extends DataExtension implements TestOnly
{
    /**
     * Push a fixed array entry into the datalist for extensibility testing
     *
     * @param  ArrayList $relatedDocuments
     * @return ArrayList
     */
    public function updateRelatedDocuments(ArrayList $relatedDocuments)
    {
        $fakeDocument = new DMSDocument;
        $fakeDocument->Filename = 'Extended';
        $relatedDocuments->push($fakeDocument);
        return $relatedDocuments;
    }
}
