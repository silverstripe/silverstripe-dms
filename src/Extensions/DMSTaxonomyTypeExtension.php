<?php

namespace Sunnysideup\DMS\Extensions;

use TaxonomyType;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;

/**
 * Creates default taxonomy type records if they don't exist already
 */

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: upgrade to SS4
  * OLD:  extends DataExtension (ignore case)
  * NEW:  extends DataExtension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
class DMSTaxonomyTypeExtension extends DataExtension
{
    /**
     * Create default taxonomy type records. Add records via YAML configuration (see taxonomy.yml):
     *
     * <code>
     * DMSTaxonomyTypeExtension:
     *   default_records:
     *     - Document
     *     - PrivateDocument
     * </code>
     */
    public function requireDefaultRecords()
    {
        $records = (array) Config::inst()->get(get_class($this), 'default_records');
        foreach ($records as $name) {
            $type = TaxonomyType::get()->filter('Name', $name)->first();
            if (!$type) {
                $type = TaxonomyType::create(array('Name' => $name));
                $type->write();
            }
        }
    }
}
