<?php

class DMSDocumentAddExistingField extends CompositeField
{
    public $useFieldContext = true;

    public function __construct($name, $title = null)
    {
        $this->name = $name;
        $this->title = ($title === null) ? $name : $title;

        parent::__construct(
            new TreeDropdownField(
                'PageSelector',
                'Add from another page',
                'SiteTree',
                'ID',
                'TitleWithNumberOfDocuments'
            )
        );
    }

    /**
     * Force a record to be used as "Parent" for uploaded Files (eg a Page with a has_one to File)
     * @param DataObject $record
     */
    public function setRecord($record)
    {
        $this->record = $record;
        return $this;
    }
    /**
     * Get the record to use as "Parent" for uploaded Files (eg a Page with a has_one to File) If none is set, it
     * will use Form->getRecord() or Form->Controller()->data()
     * @return DataObject
     */
    public function getRecord()
    {
        if (!$this->record && $this->form) {
            if ($this->form->getRecord() && is_a($this->form->getRecord(), 'DataObject')) {
                $this->record = $this->form->getRecord();
            } elseif ($this->form->Controller() && $this->form->Controller()->hasMethod('data')
                    && $this->form->Controller()->data() && is_a($this->form->Controller()->data(), 'DataObject')) {
                $this->record = $this->form->Controller()->data();
            }
        }
        return $this->record;
    }

    public function FieldHolder($properties = array())
    {
        return $this->Field($properties);
    }

    public function Field($properties = array())
    {
        Requirements::javascript(DMS_DIR . '/javascript/DMSDocumentAddExistingField.js');
        Requirements::javascript(DMS_DIR . '/javascript/DocumentHtmlEditorFieldToolbar.js');
        Requirements::css(DMS_DIR . '/dist/css/cmsbundle.css');

        return $this->renderWith('DMSDocumentAddExistingField');
    }

    /**
     * Sets or unsets the use of the "field" class in the template. The "field" class adds Javascript behaviour
     * that causes unwelcome hiding side-effects when this Field is used within the link editor pop-up
     *
     * @return $this
     */
    public function setUseFieldClass($use = false)
    {
        $this->useFieldContext = $use;
        return $this;
    }
}
