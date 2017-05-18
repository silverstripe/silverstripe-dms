<?php

class DMSDocumentTaxonomyExtension extends DataExtension
{
    private static $many_many = array(
        'Tags' => 'TaxonomyTerm'
    );

    /**
     * Push an autocomplete dropdown for the available tags in documents
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $tags = $this->getAllTagsMap();
        $tagField = ListboxField::create('Tags', _t('DMSDocumentTaxonomyExtension.TAGS', 'Tags'))
            ->setMultiple(true)
            ->setSource($tags);

        if (empty($tags)) {
            $tagField->setAttribute('data-placeholder', _t('DMSDocumentTaxonomyExtension.NOTAGS', 'No tags found'));
        }

        $fields->insertAfter('Description', $tagField);
    }

    /**
     * Return an array of all the available tags that a document can use. Will return a list containing a taxonomy
     * term's entire hierarchy, e.g. "Photo > Attribute > Density > High"
     *
     * @return array
     */
    public function getAllTagsMap()
    {
        $tags = TaxonomyTerm::get()->filter(
            'Type.Name:ExactMatch',
            Config::inst()->get('DMSTaxonomyTypeExtension', 'default_record_name')
        );

        $map = array();
        foreach ($tags as $tag) {
            $nameParts = array($tag->Name);
            $currentTag = $tag;

            while ($currentTag->Parent() && $currentTag->Parent()->exists()) {
                array_unshift($nameParts, $currentTag->Parent()->Name);
                $currentTag = $currentTag->Parent();
            }

            $map[$tag->ID] = implode(' > ', $nameParts);
        }
        return $map;
    }
}
