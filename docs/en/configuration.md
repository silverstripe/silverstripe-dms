# Configuration

The file location is set via the `DMS::$dmsFolder` static, and points to a location in the webroot.

## Enable/disable documents/sets for a specific page type

If you don't need documents/document sets for a specific page type you can disable this with YAML configuration:

```yaml
MyPageType:
  documents_enabled: false
```

Likewise, you could override a previously set configuration value by setting this back to `true` in a configuration
file with a higher precedence.

## Allowed extensions for DMS documents

By default the allowed extensions for DMS documents will come from the UploadField's allowed extesions list, and will
have a customised list of extensions for DMS merged in. The base `allowed_extensions` is a site-wide configuration
setting. [See here for information](https://docs.silverstripe.org/en/3/developer_guides/forms/field_types/uploadfield/#limit-the-allowed-filetypes) on changing this.

To add extra allowed file extensions purely for DMS documents, you can update the YAML configuration property:

```yaml
DMSDocumentAddController:
  allowed_extensions:
    - php
    - php5
```

## Adding fields to the Query Builder
Query builder fields are read from the DMSDocument::searchable_fields property set in [querybuilder.yml](../../_config/querybuilder.yml). Some default fields are provided and can be customised
by modifying the field and/or filter properties of a field or adding a new field entirely.

[See here for information](https://docs.silverstripe.org/en/developer_guides/model/searchfilters/) on how to modify search filters and [see here for more information](https://docs.silverstripe.org/en/developer_guides/forms/field_types/common_subclasses/)
on the field types available.

The default searchable filters available to query builder is as follows:

```yaml
DMSDocument:
  searchable_fields:
    Title:
      title: "Document title matches ..."
    Description:
      title: "Document summary matches ..."
    CreatedByID:
      title: 'Document created by ...'
      field: 'ListboxField'
      filter: 'ExactMatchFilter'
    LastEditedByID:
      title: 'Document last changed by ...'
      field: 'ListboxField'
      filter: 'ExactMatchFilter'
    Filename:
      title: 'File name'
```

## Change the shortcode handler

If you need to change the `dms_document_link` shortcode handler for some reason, you can do so with YAML configuration
and some PHP:

```yaml
DMS:
  shortcode_handler_key: your_shortcode
```

And for example in `_config.php`:

```php
ShortcodeParser::get('default')->register(
    Config::inst()->get('DMS', 'shortcode_handler_key'),
    array('DMSShortcodeHandler', 'handle')
);
```
