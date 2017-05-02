<?php
/**
 * A document set is attached to Pages, and contains many DMSDocuments
 */
class DMSDocumentSet extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)'
    );

    private static $has_one = array(
        'Page' => 'SiteTree'
    );

    private static $many_many = array(
        'Documents' => 'DMSDocument'
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
     * @return DataList
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
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            // Javascript to customize the grid field for the DMS document (overriding entwine
            // in FRAMEWORK_DIR.'/javascript/GridField.js'
            Requirements::javascript(DMS_DIR . '/javascript/DMSGridField.js');
            Requirements::css(DMS_DIR . '/css/DMSMainCMS.css');

            // Javascript for the link editor pop-up in TinyMCE
            Requirements::javascript(DMS_DIR . '/javascript/DocumentHtmlEditorFieldToolbar.js');

            // Document listing
            $gridFieldConfig = GridFieldConfig::create()
                ->addComponents(
                    new GridFieldToolbarHeader(),
                    new GridFieldFilterHeader(),
                    new GridFieldSortableHeader(),
                    // new GridFieldOrderableRows('DocumentSort'),
                    new GridFieldDataColumns(),
                    new GridFieldEditButton(),
                    new DMSGridFieldDeleteAction(), //special delete dialog to handle custom behaviour of unlinking and deleting
                    new GridFieldDetailForm()
                    //GridFieldLevelup::create($folder->ID)->setLinkSpec('admin/assets/show/%d')
                );

            if (class_exists('GridFieldPaginatorWithShowAll')) {
                $paginatorComponent = new GridFieldPaginatorWithShowAll(15);
            } else {
                $paginatorComponent = new GridFieldPaginator(15);
            }
            $gridFieldConfig->addComponent($paginatorComponent);

            if (class_exists('GridFieldSortableRows')) {
                $sortableComponent = new GridFieldSortableRows('DocumentSort');
                //setUsePagenation method removed from newer version of SortableGridField.
                if (method_exists($sortableComponent, 'setUsePagination')) {
                    $sortableComponent->setUsePagination(false)->setForceRedraw(true);
                }
                $gridFieldConfig->addComponent($sortableComponent);
            }

            // HACK: Create a singleton of DMSDocument to ensure extensions are applied before we try to get display fields.
            singleton('DMSDocument');
            $gridFieldConfig->getComponentByType('GridFieldDataColumns')
                ->setDisplayFields(Config::inst()->get('DMSDocument', 'display_fields'))
                ->setFieldCasting(array('LastChanged'=>"Datetime->Ago"))
                ->setFieldFormatting(
                    array(
                        'FilenameWithoutID' => '<a target=\'_blank\' class=\'file-url\' href=\'$Link\'>$FilenameWithoutID</a>'
                    )
                );

            //override delete functionality with this class
            $gridFieldConfig->getComponentByType('GridFieldDetailForm')
                ->setItemRequestClass('DMSGridFieldDetailForm_ItemRequest');

            $gridField = GridField::create(
                'Documents',
                false,
                $this->Documents(),//->Sort('DocumentSort'),
                $gridFieldConfig
            );
            $gridField->addExtraClass('documents');

            $gridFieldConfig->addComponent(
                $addNewButton = new DMSGridFieldAddNewButton,
                'GridFieldExportButton'
            );
            $addNewButton->setDocumentSetId($this->ID);

            $fields->removeByName('Documents');
            $fields->addFieldToTab('Root.Main', $gridField);
        });
        return parent::getCMSFields();
    }
}
