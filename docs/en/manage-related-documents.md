# Manage related documents

You can relate documents to each other using the GridField under "Related Documents" in the CMS.

## Add related documents

You can use the model relationship `DMSDocument::RelatedDocuments` to modify the DataList and save as required:

```php
$parentDocument = DMSDocument::get()->byId(123);

$relatedDocument = DMSDocument::get()->byId(234);

$parentDocument->RelatedDocuments()->add($relatedDocument);
```

Using the relationship method directly will skip the extension hook available in `getRelatedDocuments` (see below).

## Modifying the related documents list

If you need to modify the related documents DataList returned by the ORM, use the `updateRelatedDocuments` extension
hook provided by `DMSDocument::getRelatedDocuments`:

```php
# MyExtension is an extension applied to DMSDocument
class MyExtension extends DataExtension
{
    public function updateRelatedDocuments($relatedDocuments)
    {
        foreach ($relatedDocuments as $document) {
            // Add square brackets around the description
            $document->Description = '[' . $document->Description . ']';
        }
        return $relatedDocuments;
    }
}
```

## Retrieving related documents

To retrieve a DataList of related documents you can either use `getRelatedDocuments` or the ORM relationship method
`RelatedDocuments` directly. The former will allow extensions to modify the list, whereas the latter will not.

```php
$relatedDocuments = $document->getRelatedDocuments();

foreach ($relatedDocuments as $relatedDocument) {
    // ...
}
```
