<?php
/**
 * This class is a {@link GridField} component that adds a delete a DMS document.
 *
 * This component also supports unlinking a relation instead of deleting the object. By default it unlinks, but if
 * this is the last reference to a specific document, it warns the user that continuing with the operation will
 * delete the document completely.
 *
 * <code>
 * $action = new GridFieldDeleteAction(); // delete objects permanently
  * </code>
 *
 * @package dms
 * @subpackage cms
 */
class DMSGridFieldDeleteAction extends GridFieldDeleteAction implements
    GridField_ColumnProvider,
    GridField_ActionProvider
{

    /**
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return string - the HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        if ($this->removeRelation) {
            $field = GridField_FormAction::create(
                $gridField,
                'UnlinkRelation' . $record->ID,
                false,
                "unlinkrelation",
                array('RecordID' => $record->ID)
            )
                ->addExtraClass('gridfield-button-unlink')
                ->setAttribute('title', _t('GridAction.UnlinkRelation', "Unlink"))
                ->setAttribute('data-icon', 'chain--minus');
        } else {
            if (!$record->canDelete()) {
                return '';
            }

            $field = GridField_FormAction::create(
                $gridField,
                'DeleteRecord' . $record->ID,
                false,
                "deleterecord",
                array('RecordID' => $record->ID)
            )
                ->addExtraClass('gridfield-button-delete')
                ->setAttribute('title', _t('GridAction.Delete', "Delete"))
                ->setAttribute('data-icon', 'cross-circle')
                ->setDescription(_t('GridAction.DELETE_DESCRIPTION', 'Delete'));
        }

        // Add a class to the field to if it is the last gridfield in the list
        $numberOfRelations = $record->getRelatedPages()->count();
        $field
            // Add a new class for custom JS to handle the delete action
            ->addExtraClass('dms-delete')
            // Add the number of pages attached to this field as a data-attribute
            ->setAttribute('data-pages-count', $numberOfRelations)
            // Remove the base gridfield behaviour
            ->removeExtraClass('gridfield-button-delete');

        // Set a class telling JS what kind of warning to display when clicking the delete button
        if ($numberOfRelations > 1) {
            $field->addExtraClass('dms-delete-link-only');
        } else {
            $field->addExtraClass('dms-delete-last-warning');
        }

        // Set a class to show if the document is hidden
        if ($record->isHidden()) {
            $field->addExtraClass('dms-document-hidden');
        }

        return $field->Field();
    }

    /**
     * Handle the actions and apply any changes to the GridField
     *
     * @param GridField $gridField
     * @param string $actionName
     * @param mixed $arguments
     * @param array $data - form data
     * @return void
     * @throws ValidationException If the current user doesn't have permission to delete the record
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'deleterecord' || $actionName == 'unlinkrelation') {
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }
            if ($actionName == 'deleterecord' && !$item->canDelete()) {
                throw new ValidationException(
                    _t('GridFieldAction_Delete.DeletePermissionsFailure', "No delete permissions"),
                    0
                );
            }

            $delete = false;
            if ($item->Pages()->Count() <= 1) {
                $delete = true;
            }

            // Remove the relation
            $gridField->getList()->remove($item);
            if ($delete) {
                // Delete the document
                $item->delete();
            }
        }
    }
}
