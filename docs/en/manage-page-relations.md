# Manage page relations

Documents are associated to pages via "document sets". You can retrieve document sets for a page, then retrieve
documents that belong to those sets. You can still retrieve all documents for a page if you want to.

## Get document sets for a page

```php
$dms = DMS::inst();
$sets = $dms->getDocumentSetsByPage($myPage);
```

You can also request sets directly from the SiteTree instance:

```php
$sets = $page->getDocumentSets();
```

## Get all related documents for a page

`DMS::getByPage` will exclude currently embargoed documents by default. To include embargoed documents as well
add `true` as the second argument.

```php
$dms = DMS::inst();

$documents = $dms->getByPage($myPage);
$documentsIncludingEmbargoed = $dms->getByPage($myPage, true);
```

You can also request this directly from the SiteTree instance:

```php
$documents = $myPage->getAllDocuments();
```
