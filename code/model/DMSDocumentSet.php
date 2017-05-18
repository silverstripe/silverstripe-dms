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
            'BelongsToSet' => 'Boolean(1)', // Flag indicating if a document was added directly to a set - in which case it is set - or added via the query-builder.
        ),
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
                // Document listing
                $gridFieldConfig = GridFieldConfig::create()
                    ->addComponents(
                        new GridFieldToolbarHeader(),
                        new GridFieldFilterHeader(),
                        new GridFieldSortableHeader(),
                        new GridFieldDataColumns(),
                        new GridFieldEditButton(),
                        // Special delete dialog to handle custom behaviour of unlinking and deleting
                        new DMSGridFieldDeleteAction(),
                        new GridFieldDetailForm()
                    );

                if (class_exists('GridFieldPaginatorWithShowAll')) {
                    $paginatorComponent = new GridFieldPaginatorWithShowAll(15);
                } else {
                    $paginatorComponent = new GridFieldPaginator(15);
                }
                $gridFieldConfig->addComponent($paginatorComponent);

                if (class_exists('GridFieldSortableRows')) {
                    $sortableComponent = new GridFieldSortableRows('DocumentSort');
                    // setUsePagination method removed from newer version of SortableGridField.
                    if (method_exists($sortableComponent, 'setUsePagination')) {
                        $sortableComponent->setUsePagination(false)->setForceRedraw(true);
                    }
                    $gridFieldConfig->addComponent($sortableComponent);
                }

                $gridFieldConfig->getComponentByType('GridFieldDataColumns')
                    ->setDisplayFields($self->getDocumentDisplayFields())
                    ->setFieldCasting(array('LastEdited' => 'Datetime->Ago'))
                    ->setFieldFormatting(
                        array(
                            'FilenameWithoutID' => '<a target=\'_blank\' class=\'file-url\' href=\'$Link\'>$FilenameWithoutID</a>',
                            'BelongsToSet' => function ($value) {
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
                    $addNewButton = new DMSGridFieldAddNewButton,
                    'GridFieldExportButton'
                );
                $addNewButton->setDocumentSetId($self->ID);

                $fields->removeByName('Documents');
                $fields->addFieldToTab('Root.Main', $gridField);
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
     * To extend use the following from within an Extension subclass:
     *
     * <code>
     * public function updateQueryFields($result)
     * {
     *     // Do something here
     * }
     * </code>
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
            // Apply field customisations where necessary
            if (in_array($field->getName(), array('CreatedByID', 'LastEditedByID', 'LastEditedByID'))) {
                /** @var ListboxField $field */
                $field->setMultiple(true)->setSource($membersMap);
            }
        }
        $keyValPairs = JsonField::create('KeyValuePairs', $dmsDocFields->toArray());

        // Now lastly add the sort fields
        $sortedBy = FieldGroup::create('SortedBy', array(
                DropdownField::create('SortBy', '', array(
                    'LastEdited'  => 'Last changed',
                    'Created'     => 'Created',
                    'Title'       => 'Document title',
                ), 'LastEdited'),
                DropdownField::create('SortByDirection', '', $this->dbObject('SortByDirection')->enumValues(), 'DESC'),
            ));

        $sortedBy->setTitle(_t('DMSDocumentSet.SORTED_BY', 'Sort the document set by:'));
        $fields->addFieldsToTab('Root.QueryBuilder', array($keyValPairs, $sortedBy));
        $this->extend('updateQueryFields', $fields);
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->saveLinkedDocuments();
    }

    /**
     * Retrieve a list of the documents in this set. An extension hook is provided before the result is returned.
     *
     * @return ArrayList|null
     */
    public function saveLinkedDocuments()
    {
        // Documents that belong to just this set.
        /** @var ManyManyList $originals */
        $originals = $this->Documents();
        if (!(empty($this->KeyValuePairs)) && $this->isChanged('KeyValuePairs')) {
            $keyValuesPair = Convert::json2array($this->KeyValuePairs);
            /** @var DMSDocument $dmsDoc */
            $dmsDoc = singleton('DMSDocument');
            $context = $dmsDoc->getDefaultSearchContext();

            $sortBy = $this->SortBy ? $this->SortBy : 'LastEdited';
            $sortByDirection = $this->SortByDirection ? $this->SortByDirection : 'DESC';
            $sortedBy = sprintf('%s %s', $sortBy, $sortByDirection);
            /** @var DataList $documents */
            $documents = $context->getResults($keyValuesPair, $sortedBy);
            $now = SS_Datetime::now()->Rfc2822();
            $documents = $documents->where(
                "\"EmbargoedIndefinitely\" = 0 AND ".
                " \"EmbargoedUntilPublished\" = 0 AND ".
                "(\"EmbargoedUntilDate\" IS NULL OR " .
                "(\"EmbargoedUntilDate\" IS NOT NULL AND '{$now}' >= \"EmbargoedUntilDate\")) AND " .
                "\"ExpireAtDate\" IS NULL OR (\"ExpireAtDate\" IS NOT NULL AND '{$now}' < \"ExpireAtDate\")"
            );

            // Remove all BelongsToSet as the rules have changed
            $originals->removeByFilter('"BelongsToSet" = 0');
            foreach ($documents as $document) {
                $originals->add($document, array('BelongsToSet' => 0));
            }
        }
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
            array('BelongsToSet' => _t('DMSDocumentSet.ADDEDMETHOD', 'Added'))
        );
    }
}
