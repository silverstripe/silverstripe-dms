<?php

/**
 * Custom ItemRequest class the provides custom delete behaviour for the CMSFields of DMSDocument
 */
class DMSGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {


	function ItemEditForm() {
		$form = parent::ItemEditForm();

		//add a data attribute specifying how many pages this document is referenced on
		if ($record = $this->record) {
			$numberOfRelations = $record->Pages()->Count();

			//add the number of pages attached to this field as a data-attribute
			$form->setAttribute('data-pages-count', $numberOfRelations);
		}
		return $form;
	}

}