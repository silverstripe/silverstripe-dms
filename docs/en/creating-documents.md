# Creating documents

Create by relative path:

```php
$dms = DMS::getDMSInstance();
$doc = $dms->storeDocument('assets/myfile.pdf');
```

Create from an existing `File` record:

```php
$dms = DMS::getDMSInstance();
$file = File::get()->byID(99);
$doc = $dms->storeDocument($file);
```

Note: Both operations copy the existing file.
