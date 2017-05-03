<?php

class DMSGridFieldAddNewButton extends GridFieldAddNewButton implements GridField_HTMLProvider
{
    /**
     * The page ID that the document should be attached to. Used in the GridField for Documents in a Page.
     *
     * @var int
     */
    protected $pageId;

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
        if ($this->getPageId()) {
            $link = Controller::join_links($link, '?ID=' . $this->getPageId());
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
     * Set the page ID that this document should be attached to
     *
     * @param  int $id
     * @return $this
     */
    public function setPageId($id)
    {
        $this->pageId = $id;
        return $this;
    }

    /**
     * Get the page ID that this document should be attached to
     *
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }
}
