<?php

class DMSGridFieldAddNewButton extends GridFieldAddNewButton implements GridField_HTMLProvider
{
    /**
     * The document set ID that the document should be attached to
     *
     * @var int
     */
    protected $documentSetId;

    /**
     * Overriding the parent method to change the template that the DMS add button will be rendered with
     *
     * @param  GridField $gridField
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        $singleton = singleton($gridField->getModelClass());

        if (!$singleton->canCreate()) {
            return array();
        }

        if (!$this->buttonName) {
            // provide a default button name, can be changed by calling {@link setButtonName()} on this component
            $objectName = $singleton->i18n_singular_name();
            $this->buttonName = _t('GridField.Add', 'Add {name}', array('name' => $objectName));
        }

        $link = singleton('DMSDocumentAddController')->Link();
        if ($this->getDocumentSetId()) {
            $link = Controller::join_links($link, '?dsid=' . $this->getDocumentSetId());
        }

        $data = new ArrayData(array(
            'NewLink' => $link,
            'ButtonName' => $this->buttonName,
        ));

        return array(
            $this->targetFragment => $data->renderWith('DMSGridFieldAddNewButton'),
        );
    }

    /**
     * Set the document set ID that this document should be attached to
     *
     * @param  int $id
     * @return $this
     */
    public function setDocumentSetId($id)
    {
        $this->documentSetId = $id;
        return $this;
    }

    /**
     * Get the document set ID that this document should be attached to
     *
     * @return int
     */
    public function getDocumentSetId()
    {
        return $this->documentSetId;
    }
}
