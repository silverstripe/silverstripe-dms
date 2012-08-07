<?php

class DMSDocumentAddExistingField extends FormField {
	/**
	 * Force a record to be used as "Parent" for uploaded Files (eg a Page with a has_one to File)
	 * @param DataObject $record
	 */
	public function setRecord($record) {
		$this->record = $record;
		return $this;
	}
	/**
	 * Get the record to use as "Parent" for uploaded Files (eg a Page with a has_one to File) If none is set, it will use Form->getRecord() or Form->Controller()->data()
	 * @return DataObject
	 */
	public function getRecord() {
		if (!$this->record && $this->form) {
			if ($this->form->getRecord() && is_a($this->form->getRecord(), 'DataObject')) {
				$this->record = $this->form->getRecord();
			} elseif ($this->form->Controller() && $this->form->Controller()->hasMethod('data') 
					&& $this->form->Controller()->data() && is_a($this->form->Controller()->data(), 'DataObject')) {
				$this->record = $this->form->Controller()->data();
			}
		}
		return $this->record;
	}

	public function FieldHolder($properties = array()) {
		return $this->Field($properties);
	}

	public function Field($properties = array()) {
		Requirements::javascript('dms/javascript/DMSDocumentAddExistingField.js');

		return $this->renderWith('DMSDocumentAddExistingField');
	}
}

?>