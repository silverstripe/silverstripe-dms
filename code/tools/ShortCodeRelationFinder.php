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
class ShortCodeRelationFinder {

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
	function findPageIDs($number) {
		$list = $this->getList($number);
		$found = $list->column();
		return $found;
	}

	function findPageCount($number) {
		$list = $this->getList($number);
		return $list->count();
	}

	/**
	 * @return DataList
	 */
	function getList($number) {
		$list = DataList::create('SiteTree');
		$where = array();
		$fields = $this->getShortCodeFields('SiteTree');
		foreach($fields as $ancClass => $ancFields) {
			foreach($ancFields as $ancFieldName => $ancFieldSpec) {
				if ($ancClass != "SiteTree") $list = $list->leftJoin($ancClass,'"'.$ancClass.'"."ID" = "SiteTree"."ID"');
				$where[] = "\"$ancClass\".\"$ancFieldName\" LIKE '%[dms_document_link,id=$number]%'"; //."%s" LIKE ""',
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
	function getShortcodeFields($class) {
		$fields = array();
		$ancestry = array_values(ClassInfo::dataClassesFor($class));

		foreach($ancestry as $ancestor) {
			if(ClassInfo::classImplements($ancestor, 'TestOnly')) continue;

			$ancFields = DataObject::custom_database_fields($ancestor);
			if($ancFields) foreach($ancFields as $ancFieldName => $ancFieldSpec) {
				if(preg_match($this->fieldSpecRegex, $ancFieldSpec)) {
					if(!@$fields[$ancestor]) $fields[$ancestor] = array();
					$fields[$ancestor][$ancFieldName] = $ancFieldSpec;
				}
			}
		}

		return $fields;
	}

}
