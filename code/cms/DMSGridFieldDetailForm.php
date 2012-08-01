<?php

/**
 * Custom ItemRequest class the provides custom delete behaviour for the CMSFields of DMSDocument
 */
class DMSGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {


	function ItemEditForm() {
		$form = parent::ItemEditForm();
		//could try and remove the delete button here (or just hide it in css)

		return $form;
	}


	/**
	 * Overriding delete functionality with our own
	 * @param $data
	 * @param $form
	 * @return mixed
	 * @throws ValidationException
	 */
	function doDelete($data, $form) {
		try {
			$toDelete = $this->record;
			if (!$toDelete->canDelete()) {
				throw new ValidationException(_t('GridFieldDetailForm.DeletePermissionsFailure',"No delete permissions"),0);
			}

			$toDelete->delete();
		} catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad');
			return Controller::curr()->redirectBack();
		}

		$message = sprintf(
			_t('GridFieldDetailForm.Deleted', 'Deleted %s %s'),
			$this->record->singular_name(),
			'<a href="' . $this->Link('edit') . '">"' . htmlspecialchars($this->record->Title, ENT_QUOTES) . '"</a>'
		);

		$form->sessionMessage($message, 'good');

		//when an item is deleted, redirect to the revelant admin section without the action parameter
		$controller = Controller::curr();
		$noActionURL = $controller->removeAction($data['url']);
		$controller->getRequest()->addHeader('X-Pjax', 'Content'); // Force a content refresh

		return $controller->redirect($noActionURL, 302); //redirect back to admin section
	}

}