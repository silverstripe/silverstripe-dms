<?php

class DMSUploadField_ItemHandler extends UploadField_ItemHandler
{
    private static $allowed_actions = array(
        'delete',
        'edit',
        'EditForm',
    );

    public function getItem()
    {
        return DataObject::get_by_id('DMSDocument', $this->itemID);
    }

    /**
     * @return Form
     */
    public function EditForm()
    {
        $file = $this->getItem();

        // Get form components
        $fields = $this->parent->getDMSFileEditFields($file);
        $actions = $this->parent->getDMSFileEditActions($file);
        $validator = $this->parent->getDMSFileEditValidator($file);
        $form = new Form(
            $this,
            __FUNCTION__,
            $fields,
            $actions,
            $validator
        );
        $form->loadDataFrom($file);
        $form->addExtraClass('small');

        return $form;
    }
}
