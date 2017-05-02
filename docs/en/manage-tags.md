# Manage Tags

## Find documents by tag

```php
$dms = DMS::getDMSInstance();
$docs = $dms->getByTag('priority', 'important');
```

## Add tag to existing document

```php
$doc = DMSDocument::get()->byID(99);
$doc->addTag('priority', 'low');
```

## Supports multiple values for tags

```php
$doc->addTag('category', 'keyboard');
$doc->addTag('category', 'input device');
```

## Removing tags

Removing tags is abstracted as well.

```php
$doc->removeTag('category', 'keyboard');
$doc->removeTag('category', 'input device');
$doc->removeAllTags();
```
