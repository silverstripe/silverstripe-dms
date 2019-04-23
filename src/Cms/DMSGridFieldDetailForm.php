<?php

/**
 * Custom ItemRequest class the provides custom delete behaviour for the CMSFields of DMSDocument
 */
class DMSGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = array('ItemEditForm');

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();

        //add a data attribute specifying how many pages this document is referenced on
        if ($record = $this->record) {
            $numberOfPageRelations = $record->getRelatedPages()->count();
            $relations = new ShortCodeRelationFinder();
            $numberOfInlineRelations = $relations->findPageCount($record->ID);

            //add the number of pages attached to this field as a data-attribute
            $form->setAttribute('data-pages-count', $numberOfPageRelations);
            $form->setAttribute('data-relation-count', $numberOfInlineRelations);
        }
        return $form;
    }
}
