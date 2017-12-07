<?php
/**
 * Finds {@link DataObject} instances using certain shortcodes
 * by fulltext-querying only fields which are capable of parsing shortcodes.
 * Effectively the reverse of "link tracking",
 * which updates this relation on write rather than fetching it on demand.
 *
 * Doesn't scale to millions of pages due to triggering a potentially unindexed LIKE
 * search across dozens of columns and tables - but for a couple of hundred pages
 * and occasionally use its a feasible solution.
 */
class ShortCodeRelationFinder
{

    /**
     * @var String Regex matching a {@link DBField} class name which is shortcode capable.
     *
     * This should really look for implementors of a ShortCodeParseable interface,
     * but we can't extend the core Text and HTMLText class
     * on existing like SiteTree.Content for this.
     */
    protected $fieldSpecRegex = '/^(HTMLText)/';

    /**
     * @param String Shortcode index number to find
     * @return array IDs
     */
    public function findPageIDs($number)
    {
        $list = $this->getList($number);
        $found = $list->column();
        return $found;
    }

    public function findPageCount($number)
    {
        $list = $this->getList($number);
        return $list->count();
    }

    /**
     * @param int $number
     * @return DataList
     */
    public function getList($number)
    {
        $number = (int) $number;
        $list = DataList::create('SiteTree');
        $where = array();
        $fields = $this->getShortCodeFields('SiteTree');
        $shortcode = DMS::inst()->getShortcodeHandlerKey();
        foreach ($fields as $ancClass => $ancFields) {
            foreach ($ancFields as $ancFieldName => $ancFieldSpec) {
                if ($ancClass != "SiteTree") {
                    $list = $list->leftJoin($ancClass, '"'.$ancClass.'"."ID" = "SiteTree"."ID"');
                }
                $where[] = "\"$ancClass\".\"$ancFieldName\" LIKE '%[{$shortcode},id=$number]%'"; //."%s" LIKE ""',
            }
        }

        $list = $list->where(implode(' OR ', $where));
        return $list;
    }

    /**
     * Returns a filtered list of fields which could contain shortcodes.
     *
     * @param String
     * @return Array Map of class names to an array of field names on these classes.
     */
    public function getShortcodeFields($class)
    {
        $fields = array();
        $ancestry = array_values(ClassInfo::dataClassesFor($class));

        foreach ($ancestry as $ancestor) {
            if (ClassInfo::classImplements($ancestor, 'TestOnly')) {
                continue;
            }

            $ancFields = DataObject::custom_database_fields($ancestor);
            if ($ancFields) {
                foreach ($ancFields as $ancFieldName => $ancFieldSpec) {
                    if (preg_match($this->fieldSpecRegex, $ancFieldSpec)) {
                        if (!@$fields[$ancestor]) {
                            $fields[$ancestor] = array();
                        }
                        $fields[$ancestor][$ancFieldName] = $ancFieldSpec;
                    }
                }
            }
        }

        return $fields;
    }
}
