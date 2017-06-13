<?php

class DMSGridFieldEditButton extends GridFieldEditButton implements GridField_ColumnProvider
{

    /**
     * Overriding the parent method to change the template that the DMS edit button will be rendered with based on
     * whether or not the user has edit permissions.
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     *
     * @return string - the HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        $data = new ArrayData(array(
            'Link' => Controller::join_links($gridField->Link('item'), $record->ID, 'edit')
        ));

        $template = $record->canEdit() ? 'GridFieldEditButton' : 'GridFieldViewButton';

        return $data->renderWith($template);
    }
}
