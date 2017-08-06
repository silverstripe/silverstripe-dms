<?php
/**
 * Extends the original toolbar with document picking capability - modified lines are commented.
 */
class DocumentHtmlEditorFieldToolbar extends Extension
{
    public function updateLinkForm(Form $form)
    {
        $linkType = null;
        $fieldList = null;
        $fields = $form->Fields();//->fieldByName('Heading');
        foreach ($fields as $field) {
            $linkType = ($field->fieldByName('LinkType'));
            $fieldList = $field;
            if ($linkType) {
                break;
            }   //break once we have the object
        }

        $source = $linkType->getSource();
        $source['document'] = 'Download a document';
        $linkType->setSource($source);

        $addExistingField = new DMSDocumentAddExistingField('AddExisting', 'Add Existing');
        $addExistingField->setForm($form);
        $addExistingField->setUseFieldClass(false);
        $fieldList->insertAfter($addExistingField, 'Description');

        $fieldList->push(HiddenField::create('DMSShortcodeHandlerKey', false, DMS::inst()->getShortcodeHandlerKey()));

//		Requirements::javascript(SAPPHIRE_DIR . "/thirdparty/behaviour/behaviour.js");
//		Requirements::javascript(SAPPHIRE_DIR . "/javascript/tiny_mce_improvements.js");
//
//		// create additional field, rebase to 'documents' directory
//		$documents = new TreeDropdownField('document', 'Document', 'File', 'ID', 'DocumentDropdownTitle', true);
//		$documents->setSearchFunction(array($this, 'documentSearchCallback'));
//		$baseFolder = Folder::find_or_make(Document::$directory);
//		$documents->setTreeBaseID($baseFolder->ID);


        //return $form;
    }
}
