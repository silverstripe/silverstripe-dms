<?php
/**
 * Creates default taxonomy type records if they don't exist already
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
