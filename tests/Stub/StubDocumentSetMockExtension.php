<?php

/**
 * Class StubDocumentSetMockExtension
 *
 * @package dms
 */
class StubDocumentSetMockExtension extends DataExtension implements TestOnly
{
    /**
     *
     * For method {@link DMSDocumentSet::addQueryFields}
     *
     * @param FieldList $fields
     *
     * @return FieldList
     */
    public function updateQueryFields($fields)
    {
        $fields->addFieldToTab('Root.QueryBuilder', new TextField('ExtendedField'));

        return $fields;
    }
}
