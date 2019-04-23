<?php

namespace Sunnysideup\DMS\Cms;

// todo: UPGRADE: we need to replace this ...
// use UploadField_ItemHandler;


use Sunnysideup\DMS\Model\DMSDocument;
use SilverStripe\Forms\Form;

class DMSUploadField_ItemHandler
{
    private static $allowed_actions = array(
        'delete',
        'edit',
        'EditForm',
    );

    /**
     * Gets a DMS document by its ID
     *
     * @return DMSDocument
     */
    public function getItem()
    {
        return DMSDocument::get()->byId($this->itemID);
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

        return $form;
    }
}
