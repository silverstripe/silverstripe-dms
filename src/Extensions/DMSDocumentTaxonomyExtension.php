<?php

namespace Sunnysideup\DMS\Extensions;

use TaxonomyTerm;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Core\Config\Config;
use Sunnysideup\DMS\Extensions\DMSTaxonomyTypeExtension;
use SilverStripe\ORM\DataExtension;

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: upgrade to SS4
  * OLD:  extends DataExtension (ignore case)
  * NEW:  extends DataExtension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
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
            Config::inst()->get(DMSTaxonomyTypeExtension::class, 'default_record_name')
        );

        $map = [];
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
