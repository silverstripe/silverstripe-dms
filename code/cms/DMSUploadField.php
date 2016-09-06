<?php

/**
 * Field for uploading files into a DMSDocument. Replacing the existing file.
 * Not ideally suited for the purpose, as the base implementation
 * assumes to operate on a {@link File} record. We only use this as
 * a temporary container, which gets deleted as soon as the actual
 * {@link DMSDocument} is created.
 *
 * <b>NOTE: this Field will call write() on the supplied record</b>
 *
 * @author Julian Seidenberg
 * @package dms
 */
class DMSUploadField extends UploadField
{
    private static $allowed_actions = array(
        "upload",
    );

    protected $folderName = 'DMSTemporaryUploads';

    public function __construct($name, $title = null, SS_List $items = null)
    {
        parent::__construct($name, $title, $items);

        //set default DMS replace template to false
        $this->setConfig('useDMSReplaceTemplate', 0);
    }


    /**
     * Override the default behaviour of the UploadField and take the uploaded file (uploaded to assets) and
     * add it into the DMS storage, deleting the old/uploaded file.
     * @param File
     */
    protected function attachFile($file)
    {
        $dms = DMS::inst();
        $record = $this->getRecord();

        if ($record instanceof DMSDocument) {
            // If the edited record is a document,
            // assume we're replacing an existing file
            $doc = $record;
            $doc->ingestFile($file);
        } else {
            // Otherwise create it
            $doc = $dms->storeDocument($file);
            $file->delete();
            // Relate to the underlying page being edited.
            // Not applicable when editing the document itself and replacing it.
            $doc->addPage($record);
        }

        return $doc;
    }

    public function validate($validator)
    {
        return true;
    }


    public function isDisabled()
    {
        return (parent::isDisabled() || !$this->isSaveable());
    }

    public function isSaveable()
    {
        return (!empty($this->getRecord()->ID));
    }

    /**
     * Action to handle upload of a single file
     * 
     * @param SS_HTTPRequest $request
     * @return string json
     */
    public function upload(SS_HTTPRequest $request)
    {
        if ($this->isDisabled() || $this->isReadonly()) {
            return $this->httpError(403);
        }

        // Protect against CSRF on destructive action
        $token = $this->getForm()->getSecurityToken();
        if (!$token->checkRequest($request)) {
            return $this->httpError(400);
        }

        $name = $this->getName();
        $tmpfile = $request->postVar($name);
        $record = $this->getRecord();
        
        // Check if the file has been uploaded into the temporary storage.
        if (!$tmpfile) {
            $return = array('error' => _t('UploadField.FIELDNOTSET', 'File information not found'));
        } else {
            $return = array(
                'name' => $tmpfile['name'],
                'size' => $tmpfile['size'],
                'type' => $tmpfile['type'],
                'error' => $tmpfile['error']
            );
        }

        // Check for constraints on the record to which the file will be attached.
        if (!$return['error'] && $this->relationAutoSetting && $record && $record->exists()) {
            $tooManyFiles = false;
            // Some relationships allow many files to be attached.
            if ($this->getConfig('allowedMaxFileNumber') && ($record->has_many($name) || $record->many_many($name))) {
                if (!$record->isInDB()) {
                    $record->write();
                }
                $tooManyFiles = $record->{$name}()->count() >= $this->getConfig('allowedMaxFileNumber');
            // has_one only allows one file at any given time.
            } elseif ($record->has_one($name)) {
                $tooManyFiles = $record->{$name}() && $record->{$name}()->exists();
            }

            // Report the constraint violation.
            if ($tooManyFiles) {
                if (!$this->getConfig('allowedMaxFileNumber')) {
                    $this->setConfig('allowedMaxFileNumber', 1);
                }
                $return['error'] = _t(
                    'UploadField.MAXNUMBEROFFILES',
                    'Max number of {count} file(s) exceeded.',
                    array('count' => $this->getConfig('allowedMaxFileNumber'))
                );
            }
        }

        // Process the uploaded file
        if (!$return['error']) {
            $fileObject = null;

            if ($this->relationAutoSetting) {
                // Search for relations that can hold the uploaded files.
                if ($relationClass = $this->getRelationAutosetClass()) {
                    // Create new object explicitly. Otherwise rely on Upload::load to choose the class.
                    $fileObject = Object::create($relationClass);
                }
            }

            // Get the uploaded file into a new file object.
            try {
                $this->upload->loadIntoFile($tmpfile, $fileObject, $this->folderName);
            } catch (Exception $e) {
                // we shouldn't get an error here, but just in case
                $return['error'] = $e->getMessage();
            }

            if (!$return['error']) {
                if ($this->upload->isError()) {
                    $return['error'] = implode(' '.PHP_EOL, $this->upload->getErrors());
                } else {
                    $file = $this->upload->getFile();

                    // CUSTOM Attach the file to the related record.
                    $document = $this->attachFile($file);
                    
                    // Collect all output data.
                    $return = array_merge($return, array(
                        'id' => $document->ID,
                        'name' => $document->getTitle(),
                        'thumbnail_url' => $document->Icon($document->getExtension()),
                        'edit_url' => $this->getItemHandler($document->ID)->EditLink(),
                        'size' => $document->getFileSizeFormatted(),
                        'buttons' => (string) $document->renderWith($this->getTemplateFileButtons()),
                        'showeditform' => true
                    ));

                    // CUSTOM END
                }
            }
        }
        $response = new SS_HTTPResponse(Convert::raw2json(array($return)));
        $response->addHeader('Content-Type', 'text/plain');
        return $response;
    }


    /**
     * Never directly display items uploaded
     * @return SS_List
     */
    public function getItems()
    {
        return new ArrayList();
    }

    public function Field($properties = array())
    {
        $fields = parent::Field($properties);

        // Replace the download template with a new one only when access the upload field through a GridField.
        // Needs to be enabled through setConfig('downloadTemplateName', 'ss-dmsuploadfield-downloadtemplate');
        Requirements::javascript('dms/javascript/DMSUploadField_downloadtemplate.js');
            
        // In the add dialog, add the addtemplate into the set of file that load.
        Requirements::javascript('dms/javascript/DMSUploadField_addtemplate.js');

        return $fields;
    }

    /**
     * @param int $itemID
     * @return UploadField_ItemHandler
     */
    public function getItemHandler($itemID)
    {
        return DMSUploadField_ItemHandler::create($this, $itemID);
    }
    

    /**
     * FieldList $fields for the EditForm
     * @example 'getCMSFields'
     *
     * @param File $file File context to generate fields for
     * @return FieldList List of form fields
     */
    public function getDMSFileEditFields($file) 
	{

        // Empty actions, generate default
        if(empty($this->fileEditFields)) {
            $fields = $file->getCMSFields();
            // Only display main tab, to avoid overly complex interface
            if($fields->hasTabSet() && ($mainTab = $fields->findOrMakeTab('Root.Main'))) {
                $fields = $mainTab->Fields();
            }
            return $fields;
        }

        // Fields instance
        if ($this->fileEditFields instanceof FieldList) return $this->fileEditFields;

        // Method to call on the given file
        if($file->hasMethod($this->fileEditFields)) {
            return $file->{$this->fileEditFields}();
        }

        user_error("Invalid value for UploadField::fileEditFields", E_USER_ERROR);
    }

    /**
     * FieldList $actions or string $name (of a method on File to provide a actions) for the EditForm
     * @example 'getCMSActions'
     *
     * @param File $file File context to generate form actions for
     * @return FieldList Field list containing FormAction
     */
    public function getDMSFileEditActions($file) 
	{

        // Empty actions, generate default
        if(empty($this->fileEditActions)) {
            $actions = new FieldList($saveAction = new FormAction('doEdit', _t('UploadField.DOEDIT', 'Save')));
            $saveAction->addExtraClass('ss-ui-action-constructive icon-accept');
            return $actions;
        }

        // Actions instance
        if ($this->fileEditActions instanceof FieldList) return $this->fileEditActions;

        // Method to call on the given file
        if($file->hasMethod($this->fileEditActions)) {
            return $file->{$this->fileEditActions}();
        }

        user_error("Invalid value for UploadField::fileEditActions", E_USER_ERROR);
    }

    /**
     * Determines the validator to use for the edit form
     * @example 'getCMSValidator'
     *
     * @param File $file File context to generate validator from
     * @return Validator Validator object
     */
    public function getDMSFileEditValidator($file) 
	{
        // Empty validator
        if(empty($this->fileEditValidator)) return null;

        // Validator instance
        if($this->fileEditValidator instanceof Validator) return $this->fileEditValidator;

        // Method to call on the given file
        if($file->hasMethod($this->fileEditValidator)) {
            return $file->{$this->fileEditValidator}();
        }

        user_error("Invalid value for UploadField::fileEditValidator", E_USER_ERROR);
    }    
}

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
    public function EditForm() {
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
