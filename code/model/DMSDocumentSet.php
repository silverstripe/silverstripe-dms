<?php
/**
 * A document set is attached to Pages, and contains many DMSDocuments
 *
 * @property Varchar Title
 * @property  Text KeyValuePairs
 * @property  Enum SortBy
 * @property Enum SortByDirection
 */
class DMSDocumentSet extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'KeyValuePairs' => 'Text',
        'SortBy' => "Enum('LastEdited,Created,Title')')",
        'SortByDirection' => "Enum('DESC,ASC')')",
    );

    private static $has_one = array(
        'Page' => 'SiteTree',
    );

    private static $many_many = array(
        'Documents' => 'DMSDocument',
    );

    private static $many_many_extraFields = array(
        'Documents' => array(
            // Flag indicating if a document was added directly to a set - in which case it is set - or added
            // via the query-builder.
            'ManuallyAdded' => 'Boolean(1)',
            'DocumentSort' => 'Int'
        ),
    );

    private static $summary_fields = array(
        'Title' => 'Title',
        'Documents.Count' => 'No. Documents'
    );

    /**
     * Retrieve a list of the documents in this set. An extension hook is provided before the result is returned.
     *
     * You can attach an extension to this event:
     *
     * <code>
     * public function updateDocuments($document)
     * {
     *     // do something
     * }
     * </code>
     *
     * @return DataList|null
     */
    public function getDocuments()
    {
        $documents = $this->Documents();
        $this->extend('updateDocuments', $documents);
        return $documents;
    }

    /**
     * Put the "documents" list into the main tab instead of its own tab, and replace the default "Add Document" button
     * with a customised button for DMS documents
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // PHP 5.3 only
        $self = $this;
        $this->beforeUpdateCMSFields(function (FieldList $fields) use ($self) {
            $fields->removeFieldsFromTab(
                'Root.Main',
                array('KeyValuePairs', 'SortBy', 'SortByDirection')
            );
            // Don't put the GridField for documents in until the set has been created
            if (!$self->isInDB()) {
                $fields->addFieldToTab(
                    'Root.Main',
                    LiteralField::create(
                        'GridFieldNotice',
                        '<p class="message warning">' . _t(
                            'DMSDocumentSet.GRIDFIELD_NOTICE',
                            'Managing documents will be available once you have created this document set.'
                        ) . '</p>'
                    ),
                    'Title'
                );
            } else {
                $fields->removeByName('DocumentSetSort');
                // Document listing
                $gridFieldConfig = GridFieldConfig::create()
                    ->addComponents(
                        new GridFieldButtonRow('before'),
                        new GridFieldToolbarHeader(),
                        new GridFieldFilterHeader(),
                        new GridFieldSortableHeader(),
                        new GridFieldDataColumns(),
                        new DMSGridFieldEditButton(),
                        // Special delete dialog to handle custom behaviour of unlinking and deleting
                        new GridFieldDeleteAction(true),
                        new GridFieldDetailForm()
                    );

                if (class_exists('GridFieldPaginatorWithShowAll')) {
                    $paginatorComponent = new GridFieldPaginatorWithShowAll(15);
                } else {
                    $paginatorComponent = new GridFieldPaginator(15);
                }
                $gridFieldConfig->addComponent($paginatorComponent);

                if (class_exists('GridFieldSortableRows')) {
                    $gridFieldConfig->addComponent(new GridFieldSortableRows('DocumentSort'));
                } elseif (class_exists('GridFieldOrderableRows')) {
                    $gridFieldConfig->addComponent(new GridFieldOrderableRows('DocumentSort'));
                }

                // Don't show which page this is if we're already editing within a page context
                if (Controller::curr() instanceof CMSPageEditController) {
                    $fields->removeByName('PageID');
                } else {
                    $fields->fieldByName('Root.Main.PageID')->setTitle(_t('DMSDocumentSet.SHOWONPAGE', 'Show on page'));
                }

                // Don't show which page this is if we're already editing within a page context
                if (Controller::curr() instanceof CMSPageEditController) {
                    $fields->removeByName('PageID');
                } else {
                    $fields->fieldByName('Root.Main.PageID')->setTitle(_t('DMSDocumentSet.SHOWONPAGE', 'Show on page'));
                }

                $gridFieldConfig->getComponentByType('GridFieldDataColumns')
                    ->setDisplayFields($self->getDocumentDisplayFields())
                    ->setFieldCasting(array('LastEdited' => 'Datetime->Ago'))
                    ->setFieldFormatting(
                        array(
                            'FilenameWithoutID' => '<a target=\'_blank\' class=\'file-url\''
                                . ' href=\'$Link\'>$FilenameWithoutID</a>',
                            'ManuallyAdded' => function ($value) {
                                if ($value) {
                                    return _t('DMSDocumentSet.MANUAL', 'Manually');
                                }
                                return _t('DMSDocumentSet.QUERYBUILDER', 'Query Builder');
                            }
                        )
                    );

                // Override delete functionality with this class
                $gridFieldConfig->getComponentByType('GridFieldDetailForm')
                    ->setItemRequestClass('DMSGridFieldDetailForm_ItemRequest');
                $gridField = GridField::create(
                    'Documents',
                    false,
                    $self->Documents(),
                    $gridFieldConfig
                );
                $gridField->setModelClass('DMSDocument');
                $gridField->addExtraClass('documents');

                $gridFieldConfig->addComponent(
                    $addNewButton = new DMSGridFieldAddNewButton('buttons-before-left'),
                    'GridFieldExportButton'
                );
                $addNewButton->setDocumentSetId($self->ID);

                $fields->removeByName('Documents');
                $fields->addFieldsToTab(
                    'Root.Main',
                    array(
                        $gridField,
                        HiddenField::create('DMSShortcodeHandlerKey', false, DMS::inst()->getShortcodeHandlerKey())
                    )
                );
                $self->addQueryFields($fields);
            }
        });
        $this->addRequirements();
        return parent::getCMSFields();
    }

    /**
     * Add required CSS and Javascript requirements for managing documents
     *
     * @return $this
     */
    protected function addRequirements()
    {
        // Javascript to customize the grid field for the DMS document (overriding entwine
        // in FRAMEWORK_DIR.'/javascript/GridField.js'
        Requirements::javascript(DMS_DIR . '/javascript/DMSGridField.js');
        Requirements::css(DMS_DIR . '/dist/css/dmsbundle.css');

        // Javascript for the link editor pop-up in TinyMCE
        Requirements::javascript(DMS_DIR . '/javascript/DocumentHtmlEditorFieldToolbar.js');

        return $this;
    }

    /**
     * Adds the query fields to build the document logic to the DMSDocumentSet.
     *
     * @param FieldList $fields
     */
    public function addQueryFields($fields)
    {
        /** @var DMSDocument $doc */
        $doc = singleton('DMSDocument');
        /** @var FormField $field */
        $dmsDocFields = $doc->scaffoldSearchFields(array('fieldClasses' => true));
        $membersMap = Member::get()->map('ID', 'Name')->toArray();
        asort($membersMap);

        foreach ($dmsDocFields as $field) {
            if ($field instanceof ListboxField) {
                $map = ($field->getName() === 'Tags__ID') ? $doc->getAllTagsMap() : $membersMap;
                $field->setMultiple(true)->setSource($map);

                if ($field->getName() === 'Tags__ID') {
                    $field->setRightTitle(
                        _t(
                            'DMSDocumentSet.TAGS_RIGHT_TITLE',
                            'Tags can be set in the taxonomy area, and can be assigned when editing a document.'
                        )
                    );
                }
            }
        }
        $keyValPairs = DMSJsonField::create('KeyValuePairs', $dmsDocFields->toArray());

        // Now lastly add the sort fields
        $sortedBy = FieldGroup::create('SortedBy', array(
            DropdownField::create('SortBy', '', array(
                'LastEdited'  => 'Last changed',
                'Created'     => 'Created',
                'Title'       => 'Document title',
            ), 'LastEdited'),
            DropdownField::create(
                'SortByDirection',
                '',
                array(
                    'DESC' => _t('DMSDocumentSet.DIRECTION_DESCENDING', 'Descending'),
                    'ASC' => _t('DMSDocumentSet.DIRECTION_ASCENDING', 'Ascending')
                ),
                'DESC'
            ),
        ));

        $sortedBy->setTitle(_t('DMSDocumentSet.SORTED_BY', 'Sort the document set by:'));
        $fields->addFieldsToTab(
            'Root.QueryBuilder',
            array(
                LiteralField::create(
                    'GridFieldNotice',
                    '<p class="message warning">' . _t(
                        'DMSDocumentSet.QUERY_BUILDER_NOTICE',
                        'The query builder provides the ability to add documents to a document set based on the ' .
                        'filters below. Please note that the set will be built using this criteria when you save the ' .
                        'form. This set will not be dynamically updated (see the documentation for more information).'
                    ) . '</p>'
                ),
                $keyValPairs,
                $sortedBy
            )
        );
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->saveLinkedDocuments();
    }

    /**
     * Retrieve a list of the documents in this set. An extension hook is provided before the result is returned.
     */
    public function saveLinkedDocuments()
    {
        if (empty($this->KeyValuePairs) || !$this->isChanged('KeyValuePairs')) {
            return;
        }

        $keyValuesPair = Convert::json2array($this->KeyValuePairs);

        /** @var DMSDocument $dmsDoc */
        $dmsDoc = singleton('DMSDocument');
        $context = $dmsDoc->getDefaultSearchContext();

        $sortBy = $this->SortBy ? $this->SortBy : 'LastEdited';
        $sortByDirection = $this->SortByDirection ? $this->SortByDirection : 'DESC';
        $sortedBy = sprintf('%s %s', $sortBy, $sortByDirection);

        /** @var DataList $documents */
        $documents = $context->getResults($keyValuesPair, $sortedBy);
        $documents = $this->addEmbargoConditions($documents);
        $documents = $this->addQueryBuilderSearchResults($documents);
    }

    /**
     * Add embargo date conditions to a search query
     *
     * @param  DataList $documents
     * @return DataList
     */
    protected function addEmbargoConditions(DataList $documents)
    {
        $now = SS_Datetime::now()->Rfc2822();

        return $documents->where(
            "\"EmbargoedIndefinitely\" = 0 AND "
            . " \"EmbargoedUntilPublished\" = 0 AND "
            . "(\"EmbargoedUntilDate\" IS NULL OR "
            . "(\"EmbargoedUntilDate\" IS NOT NULL AND '{$now}' >= \"EmbargoedUntilDate\")) AND "
            . "\"ExpireAtDate\" IS NULL OR (\"ExpireAtDate\" IS NOT NULL AND '{$now}' < \"ExpireAtDate\")"
        );
    }

    /**
     * Remove all ManuallyAdded = 0 original results and add in the new documents returned by the search context
     *
     * @param  DataList $documents
     * @return DataList
     */
    protected function addQueryBuilderSearchResults(DataList $documents)
    {
        /** @var ManyManyList $originals Documents that belong to just this set. */
        $originals = $this->Documents();
        $originals->removeByFilter('"ManuallyAdded" = 0');

        foreach ($documents as $document) {
            $originals->add($document, array('ManuallyAdded' => 0));
        }

        return $originals;
    }

    /**
     * Customise the display fields for the documents GridField
     *
     * @return array
     */
    public function getDocumentDisplayFields()
    {
        return array_merge(
            (array) DMSDocument::create()->config()->get('display_fields'),
            array('ManuallyAdded' => _t('DMSDocumentSet.ADDEDMETHOD', 'Added'))
        );
    }

    protected function validate()
    {
        $result = parent::validate();

        if (!$this->getTitle()) {
            $result->error(_t('DMSDocumentSet.VALIDATION_NO_TITLE', '\'Title\' is required.'));
        }
        return $result;
    }

    public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return $this->getGlobalPermission($member);
    }

    public function canCreate($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return $this->getGlobalPermission($member);
    }

    public function canEdit($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return $this->getGlobalPermission($member);
    }

    public function canDelete($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }
        return $this->getGlobalPermission($member);
    }

    /**
     * Checks if a then given (or logged in) member is either an ADMIN, SITETREE_EDIT_ALL or has access
     * to the DMSDocumentAdmin module, in which case permissions is granted.
     *
     * @param Member $member
     * @return bool
     */
    public function getGlobalPermission(Member $member = null)
    {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUser();
        }

        $result = ($member &&
            Permission::checkMember(
                $member,
                array('ADMIN', 'SITETREE_EDIT_ALL', 'CMS_ACCESS_DMSDocumentAdmin')
            )
        );

        return (bool) $result;
    }
}
