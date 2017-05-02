# Manage page relations

To find documents by a Page:

```php
$dms = DMS::getDMSInstance();
$page = SiteTree::get()->filter('URLSegment', 'home')->first();
/** @var DataList $docs */
$docs = $dms->getByPage($page);
```
