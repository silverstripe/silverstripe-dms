# Creating documents

The following examples will allow you to create a DMS document in the system without associating it to a document set.

## Create by relative path

```php
$dms = DMS::inst();
$doc = $dms->storeDocument('assets/myfile.pdf');
```

## Create from an existing `File` record

```php
$dms = DMS::inst();
$file = File::get()->byID(99);
$doc = $dms->storeDocument($file);
```

Note: Both operations copy the existing file.

## Associate to a document set

If you need to associate a document to a set once it has already been created, you can use the ORM relationship from
SiteTree to access the document sets, or you can simply access the document set directly:

```php
// Add document to the first set in my page
$firstSetInPage = $myPage->DocumentSets()->first();
$firstSetInPage->add($doc);

// Add document to a specific document set
$docSet = DMSDocumentSet::get()->byId(123);
$docSet->add($doc);
```
