# Download documents

## Get the download link

You can use `DMSDocument::getLink` to retrieve the secure route to download a DMS document:

```php
$dms = DMS::inst();
$docs = $dms->getByTag('priority', 'important')->First();
$link = $doc->getLink();
```

## Default download behaviour

The default download behaviour is "download" which will force the browser to download the document. You
can select "open" as an option in the document's settings in the CMS individually, or you can change the global
default value with configuration:

```php
Config::inst()->update('DMSDocument', 'default_download_behaviour', 'open');
```

Or in YAML:

```yaml
DMSDocument:
  default_download_behaviour: open
```
