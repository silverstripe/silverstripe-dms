<?php

class DMSDocumentAdmin extends ModelAdmin
{
    private static $managed_models = array(
        'DMSDocument',
        'DMSDocumentSet'
    );

    private static $url_segment = 'documents';

    private static $menu_title = 'Documents';

    private static $menu_icon = 'dms/images/app_icons/drawer.png';

    /**
     * Remove the default "add" button and replace it with a customised version for DMS
     *
     * @return CMSForm
     */
    public function getEditForm($id = null, $fields = null)
    {
        /** @var CMSForm $form */
        $form = parent::getEditForm($id, $fields);
        $gridField = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
        return $this->modifyGridField($form, $gridField);
    }

    /**
     * If the GridField is for DMSDocument then add a custom "add" button. If it's for DMSDocumentSet then
     * update the display fields to include some extra columns that are only for this ModelAdmin, so cannot
     * be added directly to the model's display fields.
     *
     * @param  CMSForm   $form
     * @param  GridField $gridField
     * @return CMSForm
     */
    protected function modifyGridField(CMSForm $form, GridField $gridField)
    {
        $gridFieldConfig = $gridField->getConfig();

        if ($this->modelClass === 'DMSDocument') {
            $gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
            $gridFieldConfig->addComponent(new DMSGridFieldAddNewButton('buttons-before-left'), 'GridFieldExportButton');
        } elseif ($this->modelClass === 'DMSDocumentSet') {
            $gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');

            $dataColumns = $gridFieldConfig->getComponentByType('GridFieldDataColumns');
            $fields = $dataColumns->getDisplayFields($gridField);
            $fields = array('Title' => 'Title', 'Page.Title' => 'Page') + $fields;
            $dataColumns->setDisplayFields($fields);
        }

        return $form;
    }
}
