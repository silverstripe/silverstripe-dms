<?php

/**
 * @package dms
 */
class DMSSiteTreeExtension extends DataExtension
{
    private static $has_many = array(
        'DocumentSets' => 'DMSDocumentSet'
    );

    public function updateCMSFields(FieldList $fields)
    {
        // Ability to disable document sets for a Page
        if (!$this->owner->config()->get('documents_enabled')) {
            return;
        }

        $gridField = GridField::create(
            'Document Sets',
            false,
            $this->owner->DocumentSets(), //->Sort('DocumentSort'),
            new GridFieldConfig_RelationEditor
        );
        $gridField->addExtraClass('documentsets');

        $fields->addFieldToTab(
            'Root.Document Sets (' . $this->owner->DocumentSets()->Count() . ')',
            $gridField
        );
    }

    /**
     * Get a list of document sets for the owner page
     *
     * @return ArrayList
     */
    public function getDocumentSets()
    {
        return $this->owner->DocumentSets();
    }

    /**
     * Get a list of all documents from all document sets for the owner page
     *
     * @return ArrayList
     */
    public function getAllDocuments()
    {
        $documents = ArrayList::create();

        foreach ($this->getDocumentSets() as $documentSet) {
            /** @var DocumentSet $documentSet */
            $documents->merge($documentSet->getDocuments());
        }

        return $documents;
    }

    public function onBeforeDelete()
    {
        if (Versioned::current_stage() == 'Live') {
            $existsOnOtherStage = !$this->owner->getIsDeletedFromStage();
        } else {
            $existsOnOtherStage = $this->owner->getExistsOnLive();
        }

        // Only remove if record doesn't still exist on live stage.
        if (!$existsOnOtherStage) {
            $dmsDocuments = $this->owner->Documents();
            foreach ($dmsDocuments as $document) {
                //if the document is only associated with one page, i.e. only associated with this page
                if ($document->Pages()->Count() <= 1) {
                    //delete the document before deleting this page
                    $document->delete();
                }
            }
        }
    }

    public function onBeforePublish()
    {
        $embargoedDocuments = $this->owner->getAllDocuments()->filter('EmbargoedUntilPublished', true);
        if ($embargoedDocuments->Count() > 0) {
            foreach ($embargoedDocuments as $doc) {
                $doc->EmbargoedUntilPublished = false;
                $doc->write();
            }
        }
    }

    /**
     * Returns the title of the page with the total number of documents it has associated with it across
     * all document sets
     *
     * @return string
     */
    public function getTitleWithNumberOfDocuments()
    {
        return $this->owner->Title . ' (' . $this->owner->getAllDocuments()->count() . ')';
    }
}
