<?php

class DMSDocumentAdmin extends ModelAdmin
{
    private static $managed_models = array(
        'DMSDocument'
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

        // See parent class
        $gridFieldName = $this->sanitiseClassName($this->modelClass);

        $gridFieldConfig = $form->Fields()->fieldByName($gridFieldName)->getConfig();
        $gridFieldConfig->removeComponentsByType('GridFieldAddNewButton');
        $gridFieldConfig->addComponent(new DMSGridFieldAddNewButton('buttons-before-left'), 'GridFieldExportButton');

        return $form;
    }
}
