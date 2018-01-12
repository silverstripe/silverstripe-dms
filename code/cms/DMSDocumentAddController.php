<?php

/**
 * @package dms
 */
class DMSDocumentAddController extends LeftAndMain
{
    private static $url_segment = 'pages/adddocument';
    private static $url_priority = 60;
    private static $required_permission_codes = 'CMS_ACCESS_AssetAdmin';
    private static $menu_title = 'Edit Page';
    private static $tree_class = 'SiteTree';
    private static $session_namespace = 'CMSMain';

    /**
     * Allowed file upload extensions, will be merged with `$allowed_extensions` from {@link File}
     *
     * @config
     * @var array
     */
    private static $allowed_extensions = array();

    private static $allowed_actions = array(
        'getEditForm',
        'documentautocomplete',
        'linkdocument',
        'documentlist'
    );

    /**
     * Custom currentPage() method to handle opening the 'root' folder
     *
     * @return SiteTree
     */
    public function currentPage()
    {
        $id = $this->currentPageID();
        if ($id === 0) {
            return SiteTree::singleton();
        }
        return parent::currentPage();
    }

    /**
     * Return fake-ID "root" if no ID is found (needed to upload files into the root-folder). Otherwise the page ID
     * is passed in from the {@link DMSGridFieldAddNewButton}.
     *
     * @return int
     */
    public function currentPageID()
    {
        return (int) $this->getRequest()->getVar('page_id');
    }

    /**
     * Get the current document set, if a document set ID was provided
     *
     * @return DMSDocumentSet
     */
    public function getCurrentDocumentSet()
    {
        if ($id = $this->getRequest()->getVar('dsid')) {
            return DMSDocumentSet::get()->byId($id);
        }
        return singleton('DMSDocumentSet');
    }

    /**
     * @return Form
     * @todo what template is used here? AssetAdmin_UploadContent.ss doesn't seem to be used anymore
     */
    public function getEditForm($id = null, $fields = null)
    {
        Requirements::javascript(FRAMEWORK_DIR . '/javascript/AssetUploadField.js');
        Requirements::css(FRAMEWORK_DIR . '/css/AssetUploadField.css');
        Requirements::css(DMS_DIR . '/dist/css/cmsbundle.css');

        /** @var SiteTree $page */
        $page = $this->currentPage();
        /** @var DMSDocumentSet $documentSet */
        $documentSet = $this->getCurrentDocumentSet();

        $uploadField = DMSUploadField::create('AssetUploadField', '');
        $uploadField->setConfig('previewMaxWidth', 40);
        $uploadField->setConfig('previewMaxHeight', 30);
        // Required to avoid Solr reindexing (often used alongside DMS) to
        // return 503s because of too many concurrent reindex requests
        $uploadField->setConfig('sequentialUploads', 1);
        $uploadField->addExtraClass('ss-assetuploadfield');
        $uploadField->removeExtraClass('ss-uploadfield');
        $uploadField->setTemplate('AssetUploadField');
        $uploadField->setRecord($documentSet);

        $uploadField->getValidator()->setAllowedExtensions($this->getAllowedExtensions());
        $exts = $uploadField->getValidator()->getAllowedExtensions();

        asort($exts);
        $backlink = $this->Backlink();
        $done = "
		<a class=\"ss-ui-button ss-ui-action-constructive cms-panel-link ui-corner-all\" href=\"" . $backlink . "\">
			" . _t('UploadField.DONE', 'DONE') . "
		</a>";

        $addExistingField = new DMSDocumentAddExistingField(
            'AddExisting',
            _t('DMSDocumentAddExistingField.ADDEXISTING', 'Add Existing')
        );
        $addExistingField->setRecord($documentSet);

        $form = new Form(
            $this,
            'getEditForm',
            new FieldList(
                new TabSet(
                    _t('DMSDocumentAddController.MAINTAB', 'Main'),
                    new Tab(
                        _t('UploadField.FROMCOMPUTER', 'From your computer'),
                        $uploadField,
                        new LiteralField(
                            'AllowedExtensions',
                            sprintf(
                                '<p>%s: %s</p>',
                                _t('AssetAdmin.ALLOWEDEXTS', 'Allowed extensions'),
                                implode('<em>, </em>', $exts)
                            )
                        )
                    ),
                    new Tab(
                        _t('UploadField.FROMCMS', 'From the CMS'),
                        $addExistingField
                    )
                )
            ),
            new FieldList(
                new LiteralField('doneButton', $done)
            )
        );
        $form->addExtraClass('center cms-edit-form ' . $this->BaseCSSClasses());
        $form->Backlink = $backlink;
        // Don't use AssetAdmin_EditForm, as it assumes a different panel structure
        $form->setTemplate($this->getTemplatesWithSuffix('_EditForm'));
        $form->Fields()->push(HiddenField::create('ID', false, $documentSet->ID));
        $form->Fields()->push(HiddenField::create('DSID', false, $documentSet->ID));

        return $form;
    }

    /**
     * @return ArrayList
     */
    public function Breadcrumbs($unlinked = false)
    {
        $items = parent::Breadcrumbs($unlinked);

        // The root element should explicitly point to the root node.
        $items[0]->Link = Controller::join_links(singleton('CMSPageEditController')->Link('show'), 0);

        // Enforce linkage of hierarchy to AssetAdmin
        foreach ($items as $item) {
            $baselink = $this->Link('show');
            if (strpos($item->Link, $baselink) !== false) {
                $item->Link = str_replace($baselink, singleton('CMSPageEditController')->Link('show'), $item->Link);
            }
        }

        $items->push(new ArrayData(array(
            'Title' => _t('DMSDocumentSet.ADDDOCUMENTBUTTON', 'Add Document'),
            'Link' => $this->Link()
        )));

        return $items;
    }

    /**
     * Returns the link to be used to return the user after uploading a document. Scenarios:
     *
     * 1) Page context: page ID and document set ID provided, redirect back to the page and document set
     * 2) Document set context: no page ID, document set ID provided, redirect back to document set in ModelAdmin
     * 3) Document context: no page ID and no document set ID provided, redirect back to documents in ModelAdmin
     *
     * @return string
     */
    public function Backlink()
    {
        if (!$this->getRequest()->getVar('dsid') || !$this->currentPageID()) {
            $modelAdmin = new DMSDocumentAdmin;
            $modelAdmin->init();

            if ($this->getRequest()->getVar('dsid')) {
                return Controller::join_links(
                    $modelAdmin->Link('DMSDocumentSet'),
                    'EditForm/field/DMSDocumentSet/item',
                    (int) $this->getRequest()->getVar('dsid'),
                    'edit'
                );
            }
            return $modelAdmin->Link();
        }

        return $this->getPageEditLink($this->currentPageID(), (int) $this->getRequest()->getVar('dsid'));
    }

    /**
     * Return a link to edit a page, deep linking into the document set given
     *
     * @param  int $pageId
     * @param  int $documentSetId
     * @return string
     */
    protected function getPageEditLink($pageId, $documentSetId)
    {
        return Controller::join_links(
            CMSPageEditController::singleton()->getEditForm($pageId)->FormAction(),
            'field/DocumentSets/item',
            (int) $documentSetId
        );
    }

    public function documentautocomplete()
    {
        $term = (string) $this->getRequest()->getVar('term');
        $termSql = Convert::raw2sql($term);
        $data = DMSDocument::get()
            ->where(
                '("ID" LIKE \'%' . $termSql . '%\' OR "Filename" LIKE \'%' . $termSql . '%\''
                . ' OR "Title" LIKE \'%' . $termSql . '%\')'
            )
            ->sort('ID ASC')
            ->limit(20);

        $return = array();
        foreach ($data as $doc) {
            $return[] = array(
                'label' => $doc->ID . ' - ' . $doc->Title,
                'value' => $doc->ID
            );
        }

        return Convert::raw2json($return);
    }

    /**
     * Link an existing document to the given document set ID
     * @return string JSON
     */
    public function linkdocument()
    {
        $return = array('error' => _t('UploadField.FIELDNOTSET', 'Could not add document to page'));
        $documentSet = $this->getCurrentDocumentSet();
        if (!empty($documentSet)) {
            $document = DMSDocument::get()->byId($this->getRequest()->getVar('documentID'));
            $documentSet->Documents()->add($document);

            $buttonText = '<button class="ss-uploadfield-item-edit ss-ui-button ui-corner-all"'
                . ' title="' . _t('DMSDocument.EDITDOCUMENT', 'Edit this document') . '" data-icon="pencil">'
                . _t('DMSDocument.EDIT', 'Edit') . '<span class="toggle-details">'
                . '<span class="toggle-details-icon"></span></span></button>';

            // Collect all output data.
            $return = array(
                'id' => $document->ID,
                'name' => $document->getTitle(),
                'thumbnail_url' => $document->Icon($document->getExtension()),
                'edit_url' => $this->getEditForm()->Fields()->fieldByName('Main.From your computer.AssetUploadField')
                    ->getItemHandler($document->ID)->EditLink(),
                'size' => $document->getFileSizeFormatted(),
                'buttons' => $buttonText,
                'showeditform' => true
            );
        }

        return Convert::raw2json($return);
    }

    /**
     * Returns HTML representing a list of documents that are associated with the given page ID, across all document
     * sets.
     *
     * @return string HTML
     */
    public function documentlist()
    {
        if (!$this->getRequest()->getVar('pageID')) {
            return $this->httpError(400);
        }

        $page = SiteTree::get()->byId($this->getRequest()->getVar('pageID'));

        if ($page && $page->getAllDocuments()->count() > 0) {
            $list = '<ul>';

            foreach ($page->getAllDocuments() as $document) {
                $list .= sprintf(
                    '<li><a class="add-document" data-document-id="%s">%s</a></li>',
                    $document->ID,
                    $document->ID . ' - '. Convert::raw2xml($document->Title)
                );
            }

            $list .= '</ul>';

            return $list;
        }

        return sprintf(
            '<p>%s</p>',
            _t('DMSDocumentAddController.NODOCUMENTS', 'There are no documents attached to the selected page.')
        );
    }

    /**
     * Get an array of allowed file upload extensions, merged with {@link File} and extra configuration from this
     * class
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return array_filter(
            array_merge(
                (array) Config::inst()->get('File', 'allowed_extensions'),
                (array) $this->config()->get('allowed_extensions')
            )
        );
    }

    /**
     * Overrides the parent method to allow users with access to DMS admin to access this controller
     *
     * @param Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUser();
        }

        if ($member &&
            Permission::checkMember(
                $member,
                array(
                    'CMS_ACCESS_DMSDocumentAdmin',
                )
            )
        ) {
            return true;
        }
        return parent::canView($member);
    }
}
